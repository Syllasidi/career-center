<?php
/**
 * ================================
 * Modèle : Etudiant
 * Projet : IDMC Career Center (CSI)
 * ================================
 */

require_once __DIR__ . '/../config/database.php';

class Etudiant
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* =========================
       STATS DASHBOARD
       ========================= */
    public function getStats(int $idEtudiant): array
{
    // Offres disponibles
    $offres = (int) $this->db->query("
        SELECT COUNT(*)
        FROM offre
        WHERE statut_offre = 'PUBLIEE'
          AND (date_expiration IS NULL OR date_expiration >= CURRENT_DATE)
    ")->fetchColumn();

    // Candidatures en cours
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM candidature
        WHERE id_etudiant = :id
          AND statut_candidature IN ('EN_ATTENTE','EN_VALIDATION_ENSEIGNANT')
    ");
    $stmt->execute(['id' => $idEtudiant]);
    $enCours = (int) $stmt->fetchColumn();

    // Réponses reçues
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM candidature
        WHERE id_etudiant = :id
          AND statut_candidature IN ('AFFECTEE','REJETEE')
    ");
    $stmt->execute(['id' => $idEtudiant]);
    $reponses = (int) $stmt->fetchColumn();

    return [
        'offres_disponibles'     => $offres,
        'candidatures_en_cours' => $enCours,
        'reponses'              => $reponses
    ];
}

    /* =========================
       OFFRES ACTIVES
       ========================= */
    public function getOffresActives(int $limit = 6): array
    {
        $sql = "
            SELECT
                o.idoffre,
                o.titre,
                o.type_contrat,
                o.ville,
                o.pays,
                o.date_expiration,
                o.date_debut,
                o.date_fin,
                e.raison_sociale
            FROM offre o
            JOIN entreprise e ON e.idutilisateur = o.id_entreprise
            WHERE o.statut_offre = 'PUBLIEE'
              AND (o.date_expiration IS NULL OR o.date_expiration >= CURRENT_DATE)
            ORDER BY o.date_mise_en_validation DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       CANDIDATURES ÉTUDIANT
       ========================= */
    public function getCandidatures(int $idEtudiant): array
    {
        $sql = "
            SELECT
                o.titre,
                ent.raison_sociale,
                o.type_contrat,
                c.date_candidature,
                c.statut_candidature
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            JOIN entreprise ent ON ent.idutilisateur = o.id_entreprise
            WHERE c.id_etudiant = :id
            ORDER BY c.date_candidature DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idEtudiant]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       ATTESTATION RC
       ========================= */
    public function getAttestationRC(int $idEtudiant): ?array
    {
        $stmt = $this->db->prepare("
            SELECT statut_attestation, date_depot, date_fin_validite
            FROM attestation_rc
            WHERE id_etudiant = :id
        ");
        $stmt->execute(['id' => $idEtudiant]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* =========================
       INFOS COMPTE (LECTURE)
       ========================= */
    public function getInfosCompte(int $idEtudiant): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.nom,
                u.prenom,
                u.email,
                u.statut,
                e.formation,
                e.competence,
                e.adresse,
                e.profil_visible,
                e.en_recherche_active,
                e.date_naissance
            FROM utilisateur u
            JOIN etudiant e ON e.idutilisateur = u.idutilisateur
            WHERE u.idutilisateur = :id
        ");
        $stmt->execute(['id' => $idEtudiant]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       MODIFIER PROFIL 
       ========================= */
    public function modifierProfil(
        int $idEtudiant,
        ?string $adresse,
        ?string $competence
    ): void {
        $stmt = $this->db->prepare("
            UPDATE etudiant
            SET
                adresse = :adresse,
                competence = :competence
            WHERE idutilisateur = :id
        ");
        $stmt->execute([
            'adresse'   => $adresse,
            'competence'=> $competence,
            'id'        => $idEtudiant
        ]);
    }

    /* =========================
       PROFIL VISIBLE
       ========================= */
    public function activerProfil(int $idEtudiant): void
    {
        $this->db->prepare("
            UPDATE etudiant
            SET profil_visible = TRUE
            WHERE idutilisateur = :id
        ")->execute(['id' => $idEtudiant]);
    }

    public function desactiverProfil(int $idEtudiant): void
    {
        $this->db->prepare("
            UPDATE etudiant
            SET profil_visible = FALSE
            WHERE idutilisateur = :id
        ")->execute(['id' => $idEtudiant]);
    }

    /* =========================
       RECHERCHE ACTIVE
       ========================= */
    public function activerRecherche(int $idEtudiant): void
    {
        $this->db->prepare("
            UPDATE etudiant
            SET en_recherche_active = TRUE
            WHERE idutilisateur = :id
        ")->execute(['id' => $idEtudiant]);
    }

    public function desactiverRecherche(int $idEtudiant): void
    {
        $this->db->prepare("
            UPDATE etudiant
            SET en_recherche_active = FALSE
            WHERE idutilisateur = :id
        ")->execute(['id' => $idEtudiant]);
    }

    /* =========================
       MOT DE PASSE
       ========================= */
    public function changerMotDePasse(int $idUtilisateur, string $mdp): void
    {
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
    }

/* =========================
       OFFRES DISPONIBLES (FILTRES)
       ========================= */
    public function getOffresDisponibles(
        ?string $type,
        ?string $pays
    ): array {
        $sql = "
            SELECT
                o.idoffre,
                o.titre,
                o.type_contrat,
                o.pays,
                o.ville,
                o.date_fin,
                e.raison_sociale
            FROM offre o
            JOIN entreprise e ON e.idutilisateur = o.id_entreprise
            WHERE o.statut_offre = 'PUBLIEE'
              AND o.date_fin >= CURRENT_DATE
        ";

        $params = [];

        if (!empty($type)) {
            $sql .= " AND o.type_contrat = :type ";
            $params['type'] = $type;
        }

        if (!empty($pays)) {
            $sql .= " AND o.pays = :pays ";
            $params['pays'] = $pays;
        }

        $sql .= " ORDER BY o.date_mise_en_validation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       PEUT POSTULER ?
       ========================= */
    public function peutPostuler(int $idEtudiant, int $idOffre): array
    {
        // 1️⃣ Attestation RC validée
        $stmt = $this->db->prepare("
            SELECT 1
            FROM attestation_rc
            WHERE id_etudiant = :id
              AND statut_attestation = 'VALIDEE'
        ");
        $stmt->execute(['id' => $idEtudiant]);
        if (!$stmt->fetch()) {
            return ['ok' => false, 'msg' =>
                "Vous devez disposer d’une attestation RC valide."
            ];
        }

       /* // 2️⃣ Profil visible + recherche active
        $stmt = $this->db->prepare("
            SELECT profil_visible, en_recherche_active
            FROM etudiant
            WHERE idutilisateur = :id
        ");
        $stmt->execute(['id' => $idEtudiant]);
        $e = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$e['profil_visible'] || !$e['en_recherche_active']) {
            return ['ok' => false, 'msg' =>
                "Votre profil doit être visible et en recherche active."
            ];
        }*/

        // 3️⃣ Déjà candidat à cette offre
        $stmt = $this->db->prepare("
            SELECT 1
            FROM candidature
            WHERE id_etudiant = :id
              AND idoffre = :idoffre
        ");
        $stmt->execute([
            'id' => $idEtudiant,
            'idoffre' => $idOffre
        ]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'msg' =>
                "Vous avez déjà postulé à cette offre."
            ];
        }

        // 4️⃣ Affectation ACTIVE (pas terminée)
        $stmt = $this->db->prepare("
            SELECT 1
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.id_etudiant = :id
              AND c.statut_candidature = 'AFFECTEE'
              AND o.date_fin >= CURRENT_DATE
        ");
        $stmt->execute(['id' => $idEtudiant]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'msg' =>
                "Vous êtes actuellement affecté à une offre."
            ];
        }

        return ['ok' => true];
    }

    /* =========================
       DÉPOSER CANDIDATURE
       ========================= */
    public function deposerCandidature(
        int $idEtudiant,
        int $idOffre
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO candidature
            (date_candidature, statut_candidature, origine_candidature, idoffre, id_etudiant)
            VALUES
            (CURRENT_DATE, 'EN_ATTENTE', 'ETUDIANT', :idoffre, :id)
        ");
        $stmt->execute([
            'idoffre' => $idOffre,
            'id'      => $idEtudiant
        ]);
    }



    public function getCandidaturesDetaillees(int $idEtudiant): array
{
    $sql = "
        SELECT
            c.idcandidature,
            c.date_candidature,
            c.statut_candidature,
            c.date_renoncement,
            c.justification_renoncement,

            o.titre AS offre,
            o.type_contrat,
            ent.raison_sociale AS entreprise
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN entreprise ent ON ent.idutilisateur = o.id_entreprise
        WHERE c.id_etudiant = :id
        ORDER BY c.date_candidature DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function renoncerCandidature(
    int $idCandidature,
    int $idEtudiant,
    ?string $justification
): void {
    $this->db->beginTransaction();

    try {
        // 🔒 verrou candidature
        $stmt = $this->db->prepare("
            SELECT
                c.statut_candidature,
                c.id_etudiant,
                o.id_entreprise,
                o.id_enseignant,
                o.titre,
                o.idoffre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $idCandidature]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c || $c['id_etudiant'] != $idEtudiant) {
            throw new Exception("Candidature invalide.");
        }

        // justification obligatoire si affectée
        if ($c['statut_candidature'] === 'AFFECTEE' && empty(trim($justification))) {
            throw new Exception(
                "Une justification est obligatoire pour une candidature déjà affectée."
            );
        }

        // mise à jour candidature
        $stmt = $this->db->prepare("
            UPDATE candidature
            SET
                statut_candidature = 'RENONCEE',
                date_renoncement = CURRENT_DATE,
                justification_renoncement = :justif,
                origine_candidature = 'ETUDIANT'
            WHERE idcandidature = :id
        ");
        $stmt->execute([
            'justif' => $justification,
            'id'     => $idCandidature
        ]);

        // notifications uniquement si affectée
        if ($c['statut_candidature'] === 'AFFECTEE') {

            $message = "Renoncement de l’étudiant pour l’offre « {$c['titre']} »."
                     . " Justification : " . $justification;

            // entreprise
            $this->db->prepare("
                INSERT INTO notification (message, type, idutilisateur, idoffre, idcandidature)
                VALUES (:msg, 'RENONCEMENT_CANDIDATURE', :idUser, :idoffre, :idCand)
            ")->execute([
                'msg'     => $message,
                'idUser'  => $c['id_entreprise'],
                'idoffre' => $c['idoffre'],
                'idCand'  => $idCandidature
            ]);

            // enseignant
            if (!empty($c['id_enseignant'])) {
                $this->db->prepare("
                    INSERT INTO notification (message, type, idutilisateur, idoffre, idcandidature)
                    VALUES (:msg, 'RENONCEMENT_CANDIDATURE', :idUser, :idoffre, :idCand)
                ")->execute([
                    'msg'     => $message,
                    'idUser'  => $c['id_enseignant'],
                    'idoffre' => $c['idoffre'],
                    'idCand'  => $idCandidature
                ]);
            }
        }

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}


}
