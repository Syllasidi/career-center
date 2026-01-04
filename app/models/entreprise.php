<?php

require_once __DIR__ . '/../config/database.php';

class Entreprise
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Inscription entreprise
     * Diagramme de séquence respecté
     */
    public function inscrireEntreprise(array $data): bool
    {
        $this->db->beginTransaction();

        try {
            /**
             * 1️⃣ UTILISATEUR
             */
            $stmt = $this->db->prepare("
                INSERT INTO utilisateur (nom, prenom, email, role, statut)
                VALUES (:nom, '', :email, 'ENTREPRISE', 'ACTIF')
                RETURNING idutilisateur
            ");
            $stmt->execute([
                'nom'   => $data['raison_sociale'],
                'email' => $data['email']
            ]);

            $idUtilisateur = (int)$stmt->fetchColumn();

            /**
             * 2️⃣ ENTREPRISE
             */
            $stmt = $this->db->prepare("
                INSERT INTO entreprise
                (idutilisateur, raison_sociale, siret, adresse, secteur_activite)
                VALUES (:id, :raison, :siret, :adresse, :secteur)
            ");
            $stmt->execute([
                'id'      => $idUtilisateur,
                'raison'  => $data['raison_sociale'],
                'siret'   => $data['siret'],
                'adresse' => $data['adresse'],
                'secteur' => $data['secteur_activite']
            ]);

            /**
             * 3️⃣ COMPTE
             */
            $stmt = $this->db->prepare("
                INSERT INTO compte (identifiant, mdp, idutilisateur)
                VALUES (:identifiant, :mdp, :id)
            ");
            $stmt->execute([
                'identifiant' => $data['email'],
                'mdp'         => password_hash($data['mdp'], PASSWORD_DEFAULT),
                'id'          => $idUtilisateur
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }



    /**
 * ======================================
 * STATISTIQUES ACCUEIL ENTREPRISE
 * ======================================
 */
public function getStatsEntreprise(int $idEntreprise): array
{
    $stats = [
        'offres_actives'  => 0,
        'offres_attente'  => 0,
        'candidatures'    => 0,
        'offres_pourvues' => 0
    ];

    // Offres actives (PUBLIÉES)
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM offre
        WHERE id_entreprise = :id
          AND statut_offre = 'PUBLIEE'
    ");
    $stmt->execute(['id' => $idEntreprise]);
    $stats['offres_actives'] = (int) $stmt->fetchColumn();

    // Offres en attente de validation
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM offre
        WHERE id_entreprise = :id
          AND statut_offre = 'EN_ATTENTE_VALIDATION'
    ");
    $stmt->execute(['id' => $idEntreprise]);
    $stats['offres_attente'] = (int) $stmt->fetchColumn();

    // Candidatures reçues sur les offres de l’entreprise
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        WHERE o.id_entreprise = :id
    ");
    $stmt->execute(['id' => $idEntreprise]);
    $stats['candidatures'] = (int) $stmt->fetchColumn();

    // Offres pourvues (au moins une candidature AFFECTEE)
    $stmt = $this->db->prepare("
        SELECT COUNT(DISTINCT o.idoffre)
        FROM offre o
        JOIN candidature c ON c.idoffre = o.idoffre
        WHERE o.id_entreprise = :id
          AND c.statut_candidature = 'AFFECTEE'
    ");
    $stmt->execute(['id' => $idEntreprise]);
    $stats['offres_pourvues'] = (int) $stmt->fetchColumn();

    return $stats;
}
/**
 * ======================================
 * LISTE DES OFFRES DE L’ENTREPRISE
 * ======================================
 */
public function getOffresEntreprise(int $idEntreprise): array
{
    $sql = "
        SELECT
            o.*,
            COUNT(c.idcandidature) AS nb_candidatures
        FROM offre o
        LEFT JOIN candidature c ON c.idoffre = o.idoffre
        WHERE o.id_entreprise = :id
        GROUP BY o.idoffre
        ORDER BY o.date_validation DESC NULLS LAST, o.idoffre DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $idEntreprise]);

    $result = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        // =====================
        // Mapping statut → UI
        // =====================
        switch ($row['statut_offre']) {

            case 'BROUILLON':
                $label = 'Brouillon';
                $class = 'status-draft';
                break;

            case 'EN_ATTENTE_VALIDATION':
                $label = 'En attente validation';
                $class = 'status-pending';
                break;

            case 'REJETTEE':
                $label = 'Rejetée';
                $class = 'status-rejected';
                break;

            case 'VALIDEE':
                $label = 'Validée';
                $class = 'status-validated';
                break;

            case 'PUBLIEE':
                $label = 'Publiée';
                $class = 'status-active';
                break;

            case 'DESACTIVEE':
                $label = 'Désactivée';
                $class = 'status-inactive';
                break;

            default:
                $label = 'Inconnu';
                $class = 'status-pending';
        }

        // =====================
        // Construction résultat
        // =====================
        $row['nb_candidatures'] = (int)$row['nb_candidatures'];
        $row['statut_label']    = $label;
        $row['status_class']    = $class;

        // date d’affichage (fallback propre)
        $row['date_affichage']  = $row['date_validation'] ?? '—';

        $result[] = $row;
    }

    return $result;
}


/**
 * ======================================
 * CANDIDATURES REÇUES PAR L’ENTREPRISE
 * ======================================
 */
public function getCandidaturesEntreprise(int $idEntreprise): array
{
    $sql = "
        SELECT
            c.idcandidature      AS idcandidature,
            c.statut_candidature AS statut_candidature,
            c.date_candidature,

            u.nom,
            u.prenom,
            u.email,

            e.formation,

            o.titre              AS offre
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN etudiant e ON e.idutilisateur = c.id_etudiant
        JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
        WHERE o.id_entreprise = :id
        ORDER BY c.date_candidature DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $idEntreprise]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ======================================
 * RÉCUPÉRER UNE OFFRE DE L’ENTREPRISE
 * (pour modification)
 * ======================================
 */
public function getOffreEntrepriseById(int $idOffre, int $idEntreprise): ?array
{
    $stmt = $this->db->prepare("
        SELECT *
        FROM offre
        WHERE idoffre = :idoffre
          AND id_entreprise = :idEntreprise
    ");

    $stmt->execute([
        'idoffre'      => $idOffre,
        'idEntreprise' => $idEntreprise
    ]);

    $offre = $stmt->fetch(PDO::FETCH_ASSOC);

    return $offre ?: null;
}
public function getEtudiantsVisibles(): array
{
    $sql = "
        SELECT
            u.idutilisateur,
            u.nom,
            u.prenom,
            u.email,
            e.formation,
            e.competence,
            e.en_recherche_active
        FROM etudiant e
        JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
        WHERE e.profil_visible = TRUE
          AND u.statut = 'ACTIF'
          AND u.role = 'ETUDIANT'
        ORDER BY u.nom, u.prenom
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}
