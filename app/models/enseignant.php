<?php
/**
 * ================================
 * Modèle : Enseignant
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Logique métier uniquement
 * - AUCUN HTML
 * - AUCUNE redirection
 */

require_once __DIR__ . '/../config/database.php';

class Enseignant
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * ======================================
     * STATS ACCUEIL ENSEIGNANT
     * ======================================
     * Utilisé dans : views/enseignant/index.php
     */
    public function getStats(int $idEnseignant): array
    {
        $stats = [
            'offres_attente'       => 0,
            'affectations_attente' => 0,
            'offres_validees'      => 0,
            'affectations_ok'      => 0
        ];

        // 1) Offres en attente (à valider par n'importe quel enseignant)
        $stats['offres_attente'] = (int) $this->db
            ->query("
                SELECT COUNT(*)
                FROM offre
                WHERE statut_offre = 'EN_ATTENTE_VALIDATION'
                  AND id_enseignant IS NULL
            ")
            ->fetchColumn();

        // 2) Offres validées par CET enseignant (il est responsable)
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM offre
            WHERE id_enseignant = :id
              AND statut_offre IN ('VALIDEE','PUBLIEE')
        ");
        $stmt->execute(['id' => $idEnseignant]);
        $stats['offres_validees'] = (int) $stmt->fetchColumn();

        /**
         * Affectations : on branche plus tard.
         * Pour l’instant on garde 0 pour rester cohérent.
         */
        $stats['affectations_attente'] = 0;
        $stats['affectations_ok']      = 0;

        return $stats;
    }

    /**
     * ======================================
     * OFFRES À VALIDER (NON PRISES)
     * ======================================
     * Règle : on affiche seulement :
     * - EN_ATTENTE_VALIDATION
     * - id_enseignant IS NULL (pas encore responsable)
     */
   public function getDernieresOffresAValider(int $limit = 3): array
{
    $sql = "
        SELECT
            o.idoffre,
            o.titre,
            o.description,
            o.type_contrat,
            o.pays,
            o.ville,
            o.date_debut,
            o.date_fin,
            o.remuneration,
            o.date_mise_en_validation,
            e.raison_sociale
        FROM offre o
        JOIN entreprise e ON e.idutilisateur = o.id_entreprise
        WHERE o.statut_offre = 'EN_ATTENTE_VALIDATION'
          AND o.id_enseignant IS NULL
        ORDER BY o.date_mise_en_validation DESC
        LIMIT :limit
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $this->mapperOffres($stmt->fetchAll(PDO::FETCH_ASSOC));
}

public function getToutesOffresAValider(): array
{
    $sql = "
        SELECT
            o.idoffre,
            o.titre,
            o.description,
            o.type_contrat,
            o.pays,
            o.ville,
            o.date_debut,
            o.date_fin,
            o.remuneration,
            o.date_mise_en_validation,
            e.raison_sociale
        FROM offre o
        JOIN entreprise e ON e.idutilisateur = o.id_entreprise
        WHERE o.statut_offre = 'EN_ATTENTE_VALIDATION'
          AND o.id_enseignant IS NULL
        ORDER BY o.date_mise_en_validation DESC
    ";

    return $this->mapperOffres(
        $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC)
    );
}

public function getAffectationsApercu(int $idEnseignant): array
{
    $sql = "
        SELECT
            c.idcandidature,
            u.nom,
            u.prenom,
            e.formation,
            o.titre AS offre,
            c.date_mise_en_validation
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN etudiant e ON e.idutilisateur = c.id_etudiant
        JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
        WHERE c.statut_candidature = 'EN_VALIDATION_ENSEIGNANT'
          AND o.id_enseignant = :idEns
        ORDER BY c.date_mise_en_validation ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['idEns' => $idEnseignant]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getAffectationsAValider(int $idEnseignant): array
{
    $sql = "
        SELECT
            c.idcandidature,
            c.date_mise_en_validation,

            u.nom,
            u.prenom,
            u.email,
            e.formation,

            o.titre AS offre,
            o.type_contrat,

            ent.raison_sociale AS entreprise

        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN entreprise ent ON ent.idutilisateur = o.id_entreprise
        JOIN etudiant e ON e.idutilisateur = c.id_etudiant
        JOIN utilisateur u ON u.idutilisateur = e.idutilisateur

        WHERE c.statut_candidature = 'EN_VALIDATION_ENSEIGNANT'
          AND o.id_enseignant = :idEns 
          AND c.date_mise_en_validation > CURRENT_DATE - INTERVAL '3 days'


        ORDER BY c.date_mise_en_validation ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['idEns' => $idEnseignant]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

private function mapperOffres(array $rows): array
{
    $result = [];

    foreach ($rows as $r) {
        // durée en mois
        $duree = '—';
        if (!empty($r['date_debut']) && !empty($r['date_fin'])) {
            try {
                $d1 = new DateTime($r['date_debut']);
                $d2 = new DateTime($r['date_fin']);
                $i  = $d1->diff($d2);
                $duree = (($i->y * 12) + $i->m) . ' mois';
            } catch (Exception $e) {}
        }

        $result[] = [
            'idoffre'        => (int)$r['idoffre'],
            'titre'          => $r['titre'],
            'description'    => $r['description'],
            'type_contrat'   => $r['type_contrat'],
            'raison_sociale' => $r['raison_sociale'],
            'localisation'   => trim($r['ville'] . ' ' . ($r['pays'] === 'ETRANGER' ? '(Étranger)' : '')),
            'duree'          => $duree,
            'remuneration'   => $r['remuneration'],
            'date_depot'     => $r['date_mise_en_validation']
        ];
    }

    return $result;
}



    /**
     * ======================================
     * VALIDER UNE OFFRE
     * ======================================
     * Effets :
     * - statut_offre → VALIDEE (ou PUBLIEE selon ton cycle)
     * - id_enseignant ← enseignant connecté (devient responsable)
     * - date_validation ← CURRENT_DATE
     * - notification entreprise
     */
    public function validerOffre(int $idOffre, int $idEnseignant): void
    {
        $this->db->beginTransaction();

        try {
            // 1) Lock + vérifier que l’offre est toujours disponible
            $stmt = $this->db->prepare("
                SELECT idoffre, id_entreprise, statut_offre, id_enseignant
                FROM offre
                WHERE idoffre = :id
                FOR UPDATE
            ");
            $stmt->execute(['id' => $idOffre]);
            $offre = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$offre) {
                throw new Exception("Offre introuvable.");
            }

            if ($offre['statut_offre'] !== 'EN_ATTENTE_VALIDATION') {
                throw new Exception("Cette offre n’est plus en attente de validation.");
            }

            if (!empty($offre['id_enseignant'])) {
                throw new Exception("Cette offre a déjà été prise en charge par un enseignant.");
            }

            // 2) Update offre : responsable + statut
            $stmt = $this->db->prepare("
                UPDATE offre
                SET
                    statut_offre = 'VALIDEE',
                    id_enseignant = :ens,
                    date_validation = CURRENT_DATE
                WHERE idoffre = :id
            ");
            $stmt->execute([
                'ens' => $idEnseignant,
                'id'  => $idOffre
            ]);

            // 3) Notification entreprise
            $stmt = $this->db->prepare("
                INSERT INTO notification (message, type, idutilisateur, idoffre)
                VALUES (:msg, 'OFFRE_VALIDEE', :idEnt, :idOffre)
            ");
            $stmt->execute([
                'msg'    => "Votre offre a été validée par l’enseignant responsable.",
                'idEnt'  => (int)$offre['id_entreprise'],
                'idOffre'=> $idOffre
            ]);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ======================================
     * REJETER UNE OFFRE
     * ======================================
     * Effets :
     * - statut_offre → REJETTEE
     * - id_enseignant ← enseignant connecté (traçabilité)
     * - date_validation ← CURRENT_DATE
     * - notification entreprise
     *
     * $motif est optionnel (simple)
     */
    public function rejeterOffre(int $idOffre, int $idEnseignant, string $motif = ''): void
    {
        $this->db->beginTransaction();

        try {
            // 1) Lock + vérifier disponibilité
            $stmt = $this->db->prepare("
                SELECT idoffre, id_entreprise, statut_offre, id_enseignant
                FROM offre
                WHERE idoffre = :id
                FOR UPDATE
            ");
            $stmt->execute(['id' => $idOffre]);
            $offre = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$offre) {
                throw new Exception("Offre introuvable.");
            }

            if ($offre['statut_offre'] !== 'EN_ATTENTE_VALIDATION') {
                throw new Exception("Cette offre n’est plus en attente de validation.");
            }

            if (!empty($offre['id_enseignant'])) {
                throw new Exception("Cette offre a déjà été prise en charge par un enseignant.");
            }

            // 2) Update offre
            $stmt = $this->db->prepare("
                UPDATE offre
                SET
                    statut_offre = 'REJETTEE',
                    id_enseignant = :ens,
                    date_validation = CURRENT_DATE
                WHERE idoffre = :id
            ");
            $stmt->execute([
                'ens' => $idEnseignant,
                'id'  => $idOffre
            ]);

            // 3) Notification entreprise
            $message = "Votre offre a été rejetée par l’enseignant responsable.";
            if (trim($motif) !== '') {
                $message .= " Motif : " . trim($motif);
            }

            $stmt = $this->db->prepare("
                INSERT INTO notification (message, type, idutilisateur, idoffre)
                VALUES (:msg, 'OFFRE_REJETEE', :idEnt, :idOffre)
            ");
            $stmt->execute([
                'msg'    => $message,
                'idEnt'  => (int)$offre['id_entreprise'],
                'idOffre'=> $idOffre
            ]);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ======================================
     * NOTIFICATIONS ENSEIGNANT (simple)
     * ======================================
     * Pour l’instant : on récupère les dernières notifications
     * liées à l’enseignant connecté (idutilisateur)
     */
    public function getNotifications(int $idEnseignant): array
    {
        $stmt = $this->db->prepare("
            SELECT idnotification, message, type, date_notification, idoffre, idcandidature
            FROM notification
            WHERE idutilisateur = :id
            ORDER BY date_notification DESC
            LIMIT 20
        ");
        $stmt->execute(['id' => $idEnseignant]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compteur notifications (navbar)
     */
    public function countNotifications(int $idEnseignant): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM notification
            WHERE idutilisateur = :id
        ");
        $stmt->execute(['id' => $idEnseignant]);

        return (int)$stmt->fetchColumn();
    }

    public function toutesSecretairesEnConge(): bool
{
    $sql = "
        SELECT COUNT(*) 
        FROM secretaire
        WHERE en_conge = FALSE
    ";

    $stmt = $this->db->query($sql);
    $nbEnService = (int) $stmt->fetchColumn();

    // S'il n'y a AUCUNE secrétaire en service
    return $nbEnService === 0;
}

public function getAttestationsRCEnAttente(): array
{
    $sql = "
        SELECT
            a.idattestation_rc,
            u.nom || ' ' || u.prenom AS etudiant,
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


public function getHistoriqueValidations(int $idEnseignant, int $limit = 10): array
{
    $historique = [];

    /**
     * ============================
     * 1) HISTORIQUE DES OFFRES
     * ============================
     */
    $sqlOffres = "
        SELECT
            o.date_validation        AS date_action,
            'Offre'                  AS type,
            o.titre                  AS objet,
            e.raison_sociale         AS acteur,
            o.statut_offre           AS statut
        FROM offre o
        JOIN entreprise e ON e.idutilisateur = o.id_entreprise
        WHERE o.id_enseignant = :idEns
          AND o.statut_offre IN ('VALIDEE', 'REJETTEE')
          AND o.date_validation IS NOT NULL
    ";

    $stmt = $this->db->prepare($sqlOffres);
    $stmt->execute(['idEns' => $idEnseignant]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $historique[] = [
            'date'         => $r['date_action'],
            'type'         => $r['type'],
            'objet'        => $r['objet'],
            'acteur'       => $r['acteur'],
            'action'       => $r['statut'] === 'VALIDEE' ? 'Validation' : 'Refus',
            'status_label' => $r['statut'],
            'status_class' => $r['statut'] === 'VALIDEE'
                ? 'status-validated'
                : 'status-rejected'
        ];
    }

    /**
     * ================================
     * 2) HISTORIQUE DES AFFECTATIONS
     * ================================
     * Base : statut_candidature
     * Date utilisée : date_mise_en_validation
     */
    $sqlAffectations = "
        SELECT
            c.date_mise_en_validation AS date_action,
            'Affectation'             AS type,
            o.titre                   AS objet,
            u.nom || ' ' || u.prenom  AS acteur,
            c.statut_candidature      AS statut
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN etudiant et ON et.idutilisateur = c.id_etudiant
        JOIN utilisateur u ON u.idutilisateur = et.idutilisateur
        WHERE o.id_enseignant = :idEns
          AND c.statut_candidature IN ('AFFECTEE', 'REJETEE')
          AND c.date_mise_en_validation IS NOT NULL
    ";

    $stmt = $this->db->prepare($sqlAffectations);
    $stmt->execute(['idEns' => $idEnseignant]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $historique[] = [
            'date'         => $r['date_action'],
            'type'         => $r['type'],
            'objet'        => $r['objet'],
            'acteur'       => $r['acteur'],
            'action'       => $r['statut'] === 'AFFECTEE' ? 'Validation' : 'Refus',
            'status_label' => $r['statut'],
            'status_class' => $r['statut'] === 'AFFECTEE'
                ? 'status-validated'
                : 'status-rejected'
        ];
    }

    /**
     * ============================
     * 3) TRI + LIMITE
     * ============================
     */
    usort($historique, function ($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });

    return array_slice($historique, 0, $limit);
}


public function validerAffectation(int $idCandidature, int $idEnseignant): void
{
    $this->db->beginTransaction();

    try {
        $stmt = $this->db->prepare("
            SELECT
                c.idcandidature,
                c.statut_candidature,
                c.id_etudiant,
                o.id_entreprise,
                o.titre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $idCandidature]);
        $cand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cand) {
            throw new Exception("Affectation introuvable.");
        }

        if ($cand['statut_candidature'] !== 'EN_VALIDATION_ENSEIGNANT') {
            throw new Exception("Cette affectation n’est plus en attente.");
        }

        // 1️⃣ Validation
        $stmt = $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = 'AFFECTEE'
            WHERE idcandidature = :id
        ");
        $stmt->execute(['id' => $idCandidature]);

        // 2️⃣ Notification ÉTUDIANT
        $stmt = $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur, idcandidature)
            VALUES (:msg, 'AFFECTATION_VALIDEE', :idUser, :idCand)
        ");
        $stmt->execute([
            'msg'    => "Votre candidature pour l’offre « {$cand['titre']} » a été validée.",
            'idUser' => $cand['id_etudiant'],
            'idCand' => $idCandidature
        ]);

        // 3️⃣ Notification ENTREPRISE
        $stmt->execute([
            'msg'    => "L’affectation pour l’offre « {$cand['titre']} » a été validée par l’enseignant.",
            'idUser' => $cand['id_entreprise'],
            'idCand' => $idCandidature
        ]);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
public function rejeterAffectation(int $idCandidature, int $idEnseignant): void
{
    $this->db->beginTransaction();

    try {
        $stmt = $this->db->prepare("
            SELECT
                c.idcandidature,
                c.statut_candidature,
                c.id_etudiant,
                o.id_entreprise,
                o.titre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $idCandidature]);
        $cand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cand) {
            throw new Exception("Affectation introuvable.");
        }

        if ($cand['statut_candidature'] !== 'EN_VALIDATION_ENSEIGNANT') {
            throw new Exception("Cette affectation n’est plus en attente.");
        }

        // 1️⃣ Rejet
        $stmt = $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = 'REJETEE'
            WHERE idcandidature = :id
        ");
        $stmt->execute(['id' => $idCandidature]);

        // 2️⃣ Notification ÉTUDIANT
        $stmt = $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur, idcandidature)
            VALUES (:msg, 'AFFECTATION_REJETEE', :idUser, :idCand)
        ");
        $stmt->execute([
            'msg'    => "Votre candidature pour l’offre « {$cand['titre']} » a été rejetée.",
            'idUser' => $cand['id_etudiant'],
            'idCand' => $idCandidature
        ]);

        // 3️⃣ Notification ENTREPRISE
        $stmt->execute([
            'msg'    => "L’affectation pour l’offre « {$cand['titre']} » a été rejetée par l’enseignant.",
            'idUser' => $cand['id_entreprise'],
            'idCand' => $idCandidature
        ]);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}


private int $delaiUrgence = 7; // jours
public function getAffectationsUrgentes(): array
{
    $sql = "
        SELECT
            c.idcandidature,
            u.nom || ' ' || u.prenom AS etudiant,
            u.email AS email_etudiant,
            e.formation,
            o.titre AS offre,
            ent.raison_sociale AS entreprise,
            o.type_contrat,
            c.date_mise_en_validation
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        JOIN etudiant e ON e.idutilisateur = c.id_etudiant
        JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
        JOIN entreprise ent ON ent.idutilisateur = o.id_entreprise
        WHERE c.statut_candidature = 'EN_VALIDATION_ENSEIGNANT'
          AND c.date_mise_en_validation <= CURRENT_DATE - INTERVAL '3 days'
        ORDER BY c.date_mise_en_validation ASC
    ";

    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
public function validerAffectationUrgente(int $idCandidature, int $idNouvelEnseignant): void
{
    $this->db->beginTransaction();

    try {
        $stmt = $this->db->prepare("
            SELECT
                c.idcandidature,
                c.statut_candidature,
                c.id_etudiant,
                o.idoffre,
                o.id_entreprise,
                o.titre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $idCandidature]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c || $c['statut_candidature'] !== 'EN_VALIDATION_ENSEIGNANT') {
            throw new Exception("Affectation non valide.");
        }

        // 1️⃣ enseignant reprend l’offre
        $this->db->prepare("
            UPDATE offre
            SET id_enseignant = :ens
            WHERE idoffre = :idoffre
        ")->execute([
            'ens'     => $idNouvelEnseignant,
            'idoffre'=> $c['idoffre']
        ]);

        // 2️⃣ affectation validée
        $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = 'AFFECTEE'
            WHERE idcandidature = :id
        ")->execute(['id' => $idCandidature]);

        // 3️⃣ notifications
        $this->notifierAffectation($c, true);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
private function notifierAffectation(array $c, bool $urgent = false): void
{
    $label = $urgent ? " (URGENT)" : "";

    // étudiant
    $this->db->prepare("
        INSERT INTO notification (message, type, idutilisateur, idcandidature)
        VALUES (:msg, 'AFFECTATION_VALIDEE', :idUser, :idCand)
    ")->execute([
        'msg'    => "Votre affectation{$label} pour l’offre « {$c['titre']} » a été validée.",
        'idUser' => $c['id_etudiant'],
        'idCand' => $c['idcandidature']
    ]);

    // entreprise
    $this->db->prepare("
        INSERT INTO notification (message, type, idutilisateur, idcandidature)
        VALUES (:msg, 'AFFECTATION_VALIDEE', :idUser, :idCand)
    ")->execute([
        'msg'    => "Affectation{$label} validée pour l’offre « {$c['titre']} ».",
        'idUser' => $c['id_entreprise'],
        'idCand' => $c['idcandidature']
    ]);
}



public function envoyerRelancesAffectations(): void
{
    $sql = "
        INSERT INTO notification (message, type, idutilisateur, idcandidature)
        SELECT
            'Rappel : une affectation est en attente de validation depuis 48h.',
            'RELANCE_AFFECTATION',
            o.id_enseignant,
            c.idcandidature
        FROM candidature c
        JOIN offre o ON o.idoffre = c.idoffre
        WHERE c.statut_candidature = 'EN_VALIDATION_ENSEIGNANT'
          AND c.date_mise_en_validation = CURRENT_DATE - INTERVAL '2 days'
          AND o.id_enseignant IS NOT NULL
          AND NOT EXISTS (
              SELECT 1 FROM notification n
              WHERE n.type = 'RELANCE_AFFECTATION'
                AND n.idcandidature = c.idcandidature
          )
    ";

    $this->db->exec($sql);
}


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

}
