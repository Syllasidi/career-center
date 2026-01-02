<?php

require_once __DIR__ . '/../config/database.php';

class Entreprise
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* =====================================================
       INSCRIPTION ENTREPRISE
       ===================================================== */
    public function inscrireEntreprise(array $data): bool
    {
        $this->db->beginTransaction();

        try {
            // UTILISATEUR
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

            // ENTREPRISE
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

            // COMPTE
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

    /* =====================================================
       STATISTIQUES ACCUEIL ENTREPRISE
       ===================================================== */
    public function getStatsEntreprise(int $idEntreprise): array
    {
        return [
            'offres_actives'  => $this->count("
                SELECT COUNT(*) FROM offre
                WHERE id_entreprise = :id AND statut_offre = 'PUBLIEE'
            ", $idEntreprise),

            'offres_attente'  => $this->count("
                SELECT COUNT(*) FROM offre
                WHERE id_entreprise = :id AND statut_offre = 'EN_ATTENTE_VALIDATION'
            ", $idEntreprise),

            'candidatures'    => $this->count("
                SELECT COUNT(*) FROM candidature c
                JOIN offre o ON o.idoffre = c.idoffre
                WHERE o.id_entreprise = :id
            ", $idEntreprise),

            'offres_pourvues' => $this->count("
                SELECT COUNT(DISTINCT o.idoffre)
                FROM offre o
                JOIN candidature c ON c.idoffre = o.idoffre
                WHERE o.id_entreprise = :id
                  AND c.statut_candidature = 'AFFECTEE'
            ", $idEntreprise)
        ];
    }

    private function count(string $sql, int $id): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    /* =====================================================
       LISTE DES OFFRES ENTREPRISE
       ===================================================== */
    public function getOffresEntreprise(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT
                o.idoffre,
                o.titre,
                o.type_contrat,
                o.statut_offre,
                o.date_mise_en_validation,
                COUNT(c.idcandidature) AS nb_candidatures
            FROM offre o
            LEFT JOIN candidature c ON c.idoffre = o.idoffre
            WHERE o.id_entreprise = :id
            GROUP BY o.idoffre
            ORDER BY o.date_mise_en_validation DESC
        ");
        $stmt->execute(['id' => $idEntreprise]);

        $offres = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            [$label, $class] = $this->mapStatutOffre($row['statut_offre']);

            $offres[] = [
                'idoffre'           => (int)$row['idoffre'],
                'titre'             => $row['titre'],
                'type_contrat'      => $row['type_contrat'],
                'date_publication'  => $row['date_mise_en_validation'],
                'nb_candidatures'   => (int)$row['nb_candidatures'],
                'statut_label'      => $label,
                'status_class'      => $class
            ];
        }

        return $offres;
    }

    private function mapStatutOffre(string $statut): array
    {
        return match ($statut) {
            'BROUILLON'              => ['Brouillon', 'status-draft'],
            'EN_ATTENTE_VALIDATION'  => ['En attente', 'status-pending'],
            'VALIDEE'                => ['Validée', 'status-validated'],
            'PUBLIEE'                => ['Publiée', 'status-published'],
            'REJETTEE'               => ['Rejetée', 'status-rejected'],
            'DESACTIVEE'             => ['Désactivée', 'status-disabled'],
            default                  => [$statut, 'status-pending'],
        };
    }

    /* =====================================================
       CREATION / BROUILLON / MODIFICATION OFFRE
       ===================================================== */
    public function creerOuModifierOffre(array $data, int $idEntreprise, string $mode): void
    {
        $statut = ($mode === 'brouillon')
            ? 'BROUILLON'
            : 'EN_ATTENTE_VALIDATION';

        if (!empty($data['idoffre'])) {
            // MODIFICATION
            $stmt = $this->db->prepare("
                UPDATE offre SET
                    titre = :titre,
                    description = :description,
                    remuneration = :remu,
                    pays = :pays,
                    ville = :ville,
                    date_debut = :debut,
                    date_fin = :fin,
                    statut_offre = :statut,
                    date_mise_en_validation = CURRENT_DATE
                WHERE idoffre = :id
                  AND id_entreprise = :ent
            ");
            $stmt->execute([
                'titre'  => $data['titre'],
                'description' => $data['description'],
                'remu'   => $data['remuneration'],
                'pays'   => $data['pays'],
                'ville'  => $data['ville'],
                'debut'  => $data['date_debut'],
                'fin'    => $data['date_fin'],
                'statut' => $statut,
                'id'     => $data['idoffre'],
                'ent'    => $idEntreprise
            ]);
        } else {
            // CREATION
            $stmt = $this->db->prepare("
                INSERT INTO offre
                (type_contrat, titre, description, remuneration, pays, ville,
                 date_debut, date_fin, statut_offre, id_entreprise)
                VALUES
                (:type, :titre, :desc, :remu, :pays, :ville,
                 :debut, :fin, :statut, :ent)
            ");
            $stmt->execute([
                'type'  => $data['type_contrat'],
                'titre' => $data['titre'],
                'desc'  => $data['description'],
                'remu'  => $data['remuneration'],
                'pays'  => $data['pays'],
                'ville' => $data['ville'],
                'debut' => $data['date_debut'],
                'fin'   => $data['date_fin'],
                'statut'=> $statut,
                'ent'   => $idEntreprise
            ]);

            if ($statut === 'EN_ATTENTE_VALIDATION') {
                $this->notifierEnseignants("Nouvelle offre à valider");
            }
        }
    }

    /* =====================================================
       GESTION DES CANDIDATURES
       ===================================================== */
    public function getCandidaturesOffre(int $idOffre): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.idcandidature,
                c.statut_candidature,
                c.date_candidature,
                u.nom,
                u.prenom,
                u.email,
                e.formation
            FROM candidature c
            JOIN etudiant e ON e.idutilisateur = c.id_etudiant
            JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
            WHERE c.idoffre = :id
            ORDER BY c.date_candidature DESC
        ");
        $stmt->execute(['id' => $idOffre]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traiterCandidature(int $idCandidature, string $action): void
    {
        $statut = ($action === 'accepter')
            ? 'EN_VALIDATION_ENSEIGNANT'
            : 'REJETEE';

        $stmt = $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = :statut
            WHERE idcandidature = :id
        ");
        $stmt->execute([
            'statut' => $statut,
            'id'     => $idCandidature
        ]);
    }

    /* =====================================================
       NOTIFICATIONS
       ===================================================== */
    private function notifierEnseignants(string $message): void
    {
        $this->db->exec("
            INSERT INTO notification (message, type, idutilisateur)
            SELECT '{$message}', 'VALIDATION_OFFRE', idutilisateur
            FROM enseignant
        ");
    }
}
