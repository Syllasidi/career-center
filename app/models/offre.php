<?php
/**
 * ================================
 * Modèle : Offre
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Logique métier uniquement
 * - Vérification via table reglementation
 * - Aucun HTML
 */

require_once __DIR__ . '/../config/database.php';

class Offre
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Vérification automatique de conformité
     */
   public function verifierConformiteOffre(array $data): void
{
    // 1) Dates obligatoires et cohérentes
    $debut = new DateTime($data['date_debut']);
    $fin   = new DateTime($data['date_fin']);

    if ($fin <= $debut) {
        throw new Exception("Dates invalides : la date de fin doit être après la date de début.");
    }

    // 2) Date début pas dans le passé (simple, cohérent CSI)
    $today = new DateTime('today');
    if ($debut < $today) {
        throw new Exception("Date de début invalide : elle ne peut pas être dans le passé.");
    }

    // 3) Règle spécifique alternance → France uniquement
    if ($data['type_contrat'] === 'ALTERNANCE' && $data['pays'] !== 'FRANCE') {
        throw new Exception("Alternance impossible : ce type de contrat est autorisé uniquement en France.");
    }

    // 4) Calcul durée (en mois)
    $interval = $debut->diff($fin);
    $duree = ($interval->y * 12) + $interval->m;
    if ($duree <= 0) {
        throw new Exception("Durée invalide : la durée calculée est incorrecte.");
    }

    // 5) Chercher la règle de reglementation (type+pays)
    $stmt = $this->db->prepare("
        SELECT duree_min, duree_max, remuneration_min
        FROM reglementation
        WHERE type_contrat = :type
          AND pays = :pays
        LIMIT 1
    ");
    $stmt->execute([
        'type' => $data['type_contrat'],
        'pays' => $data['pays']
    ]);

    $regle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$regle) {
        throw new Exception("Aucune règle de réglementation trouvée pour ce type de contrat et ce pays.");
    }

    // 6) Vérifier durée min/max
    $min = (int)$regle['duree_min'];
    $max = (int)$regle['duree_max'];

    if ($duree < $min || $duree > $max) {
        throw new Exception("Durée non conforme : la durée doit être entre {$min} et {$max} mois.");
    }

    // 7) Vérifier rémunération minimale
    $remu = (float)$data['remuneration'];
    $remuMin = (float)$regle['remuneration_min'];

    if ($remu < $remuMin) {
        throw new Exception("Rémunération non conforme : minimum attendu {$remuMin} €.");
    }
}

    /**
     * Calcul de la durée en mois
     */
    private function calculerDuree(array $data): int
    {
        $debut = new DateTime($data['date_debut']);
        $fin   = new DateTime($data['date_fin']);

        if ($fin <= $debut) {
            throw new Exception("Dates de début et de fin incohérentes.");
        }

        $interval = $debut->diff($fin);
        return ($interval->y * 12) + $interval->m;
    }

    /**
     * Enregistrement de l’offre
     * Statut initial : EN_ATTENTE_VALIDATION
     */
   public function enregistrerOffre(array $data): void
{
    $this->db->beginTransaction();

    try {
        // 1️⃣ Insertion de l’offre
        $stmt = $this->db->prepare("
            INSERT INTO offre (
                id_entreprise,
                type_contrat,
                titre,
                description,
                pays,
                ville,
                date_debut,
                date_fin,
                remuneration,
                statut_offre
            ) VALUES (
                :idEntreprise,
                :type_contrat,
                :titre,
                :description,
                :pays,
                :ville,
                :date_debut,
                :date_fin,
                :remuneration,
                'EN_ATTENTE_VALIDATION'
            )
            RETURNING idoffre
        ");

        $stmt->execute([
            'idEntreprise' => $data['idEntreprise'],
            'type_contrat' => $data['type_contrat'],
            'titre'        => $data['titre'],
            'description'  => $data['description'],
            'pays'         => $data['pays'],
            'ville'        => $data['ville'],
            'date_debut'   => $data['date_debut'],
            'date_fin'     => $data['date_fin'],
            'remuneration' => $data['remuneration']
        ]);

        $idOffre = (int) $stmt->fetchColumn();

        // 2️⃣ Notification enseignants
        $this->creerNotificationValidationOffre($idOffre);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}


private function creerNotificationValidationOffre(int $idOffre): void
{
    $stmt = $this->db->prepare("
        INSERT INTO notification (message, type, idoffre)
        SELECT
            'Nouvelle offre à valider',
            'VALIDATION_OFFRE',
            :idOffre
        FROM enseignant
    ");

    $stmt->execute(['idOffre' => $idOffre]);
}

}
