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
    public function getOffresAValider(): array
    {
        $sql = "
            SELECT
                o.idoffre,
                o.titre,
                o.type_contrat,
                o.pays,
                o.ville,
                o.date_debut,
                o.date_fin,
                o.remuneration,
                o.date_validation ,
                u.nom AS nom_entreprise,
                u.prenom AS prenom_entreprise,
                e.raison_sociale
            FROM offre o
            JOIN entreprise e ON e.idutilisateur = o.id_entreprise
            JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
            WHERE o.statut_offre = 'EN_ATTENTE_VALIDATION'
              AND o.id_enseignant IS NULL
            ORDER BY o.idoffre DESC
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            // badge type contrat
            $typeBadge = $r['type_contrat'];

            // durée (simple, en mois)
            $dureeLabel = '';
            if (!empty($r['date_debut']) && !empty($r['date_fin'])) {
                try {
                    $d1 = new DateTime($r['date_debut']);
                    $d2 = new DateTime($r['date_fin']);
                    $int = $d1->diff($d2);
                    $mois = ($int->y * 12) + $int->m;
                    $dureeLabel = $mois . " mois";
                } catch (Exception $e) {
                    $dureeLabel = "—";
                }
            } else {
                $dureeLabel = "—";
            }

            $result[] = [
                'idoffre'          => (int)$r['idoffre'],
                'titre'            => $r['titre'],
                'type_contrat'     => $typeBadge,
                'raison_sociale'   => $r['raison_sociale'] ?: (($r['nom_entreprise'] ?? '') . ' ' . ($r['prenom_entreprise'] ?? '')),
                'localisation'     => trim(($r['ville'] ?? '') . ' ' . (($r['pays'] ?? '') === 'ETRANGER' ? '(Étranger)' : '')),
                'duree'            => $dureeLabel,
                'remuneration'     => $r['remuneration'],
                'date_depot'       => $r['date_mise_en_validation'] ?? null,
                'description'      => '' // on branchera si tu veux afficher description complète
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

}
