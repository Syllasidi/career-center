<?php
/**
 * ================================
 * Modèle : Secretaire
 
 * ================================
 *
 * Rôle du modèle :
 * - Fournir les données nécessaires
 *   à l’espace secrétaire
 * 
 */

require_once __DIR__ . '/../config/database.php';

class Secretaire
{
    /**
     * Connexion PDO
     */
    private PDO $db;

    /**
     * Constructeur
     * → récupération de la connexion PostgreSQL
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }





   /**
     * Création en masse des étudiants
     * Diagramme de séquence respecté
     */
    public function creerEtudiants(array $etudiants): void
{
    $this->db->beginTransaction();

    try {
        foreach ($etudiants as $data) {

            // 1️⃣ UTILISATEUR
            $stmt = $this->db->prepare("
                INSERT INTO utilisateur (nom, prenom, email, role, statut)
                VALUES (:nom, :prenom, :email, 'ETUDIANT', 'ACTIF')
                RETURNING idutilisateur
            ");
            $stmt->execute([
                'nom'    => $data['nom'],
                'prenom' => $data['prenom'],
                'email'  => $data['email']
            ]);

            $idUtilisateur = (int)$stmt->fetchColumn();

            // 2️⃣ ÉTUDIANT
            $this->db->prepare("
                INSERT INTO etudiant (idutilisateur, formation, date_naissance)
                VALUES (:id, :formation, :date_naissance)
            ")->execute([
                'id'             => $idUtilisateur,
                'formation'      => $data['formation'],
                'date_naissance' => $data['date_naissance']
            ]);

            // 3️⃣ COMPTE
            $mdp = strtolower($data['prenom'] . '.' . $data['nom']);

            $this->db->prepare("
                INSERT INTO compte (identifiant, mdp, idutilisateur)
                VALUES (:identifiant, :mdp, :id)
            ")->execute([
                'identifiant' => $data['email'],
                'mdp'         => password_hash($mdp, PASSWORD_DEFAULT),
                'id'          => $idUtilisateur
            ]);
        }

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}


    /**
     * ======================================
     * STATISTIQUES DASHBOARD SECRÉTAIRE
     * ======================================
     *
     * Utilisé dans :
     * views/secretaire/index.php
     *
     * Retourne :
     * - nombre total d’étudiants
     * - nombre d’attestations RC en attente
     * - nombre d’attestations RC validées
     */
    public function getStats(): array
    {
        // Valeurs par défaut (sécurité)
        $stats = [
            'total_etudiants' => 0,
            'rc_en_attente'   => 0,
            'rc_validees'     => 0
        ];

        /**
         * TOTAL ÉTUDIANTS
         */
        $stats['total_etudiants'] = (int) $this->db
            ->query("SELECT COUNT(*) FROM etudiant")
            ->fetchColumn();

        /**
         * ATTESTATIONS RC EN ATTENTE
         */
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM attestation_rc
            WHERE statut_attestation = 'EN_ATTENTE_VALIDATION'
        ");
        $stmt->execute();
        $stats['rc_en_attente'] = (int) $stmt->fetchColumn();

        /**
         * ATTESTATIONS RC VALIDÉES
         */
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM attestation_rc
            WHERE statut_attestation = 'VALIDEE'
        ");
        $stmt->execute();
        $stats['rc_validees'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    /**
     * ======================================
     * ATTESTATIONS RC EN ATTENTE
     * ======================================
     *
     * Utilisé dans :
     * - dashboard secrétaire
     *
     * Retourne une liste prête à afficher
     */
    public function getAttestationsEnAttente(): array
    {
        $sql = "
            SELECT
                u.nom || ' ' || u.prenom AS nom_prenom,
                u.email,
                e.formation,
                a.date_depot,
                a.date_debut_validite,
                a.date_fin_validite
            FROM attestation_rc a
            JOIN etudiant e ON e.idutilisateur = a.id_etudiant
            JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
            WHERE a.statut_attestation = 'EN_ATTENTE_VALIDATION'
            ORDER BY a.date_depot ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * ======================================
     * LISTE DES ÉTUDIANTS
     * ======================================
     *
     * Utilisé dans :
     * - tableau "Gestion des étudiants"
     *
     * On prépare aussi le statut RC
     * sous forme directement exploitable
     * par la vue.
     */
    public function getEtudiants(): array
    {
        $sql = "
            SELECT
                u.idutilisateur,
                u.nom,
                u.prenom,
                u.email,
                e.formation,
                a.statut_attestation
            FROM etudiant e
            JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
            LEFT JOIN attestation_rc a ON a.id_etudiant = e.idutilisateur
            ORDER BY u.nom, u.prenom
        ";

        $result = [];

        foreach ($this->db->query($sql)->fetchAll() as $row) {

            // Gestion du statut RC (logique métier simple)
            if ($row['statut_attestation'] === 'VALIDEE') {
                $statusLabel = 'Validée';
                $statusClass = 'status-validated';
            } elseif ($row['statut_attestation'] === 'EN_ATTENTE_VALIDATION') {
                $statusLabel = 'En attente';
                $statusClass = 'status-pending';
            } else {
                $statusLabel = 'Non déposée';
                $statusClass = 'status-missing';
            }

            $result[] = [
                'id'               => $row['idutilisateur'],
                'nom_prenom'       => $row['nom'] . ' ' . $row['prenom'],
                'email'            => $row['email'],
                'formation'        => $row['formation'],
                'rc_status_label'  => $statusLabel,
                'rc_status_class'  => $statusClass
            ];
        }

        return $result;
    }




    /**
 * Infos personnelles du secrétaire
 */
public function getInfosCompte(int $idUtilisateur): array
{
    $stmt = $this->db->prepare("
        SELECT 
            u.nom,
            u.prenom,
            u.email,
            u.role,
            s.en_conge
        FROM utilisateur u
        JOIN secretaire s ON s.idutilisateur = u.idutilisateur
        WHERE u.idutilisateur = :id
    ");
    $stmt->execute(['id' => $idUtilisateur]);

    return $stmt->fetch() ?: [];
}


/**
 * Changement de mot de passe
 */
public function changerMotDePasse(int $idUtilisateur, string $mdp): bool
{
    try {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            UPDATE compte
            SET mdp = :mdp
            WHERE idutilisateur = :id
        ");

        $stmt->execute([
            'mdp' => $hash,
            'id'  => $idUtilisateur
        ]);

        return true;

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Mise à jour du statut congé
 */
public function setEnConge(int $idUtilisateur, bool $enConge): bool
{
    try {
        $stmt = $this->db->prepare("
            UPDATE secretaire
            SET en_conge = :conge::boolean
            WHERE idutilisateur = :id
        ");

        $stmt->execute([
            'conge' => $enConge ? 'true' : 'false',
            'id'    => $idUtilisateur
        ]);

        return true;

    } catch (Exception $e) {
        return false;
    }
}



}

