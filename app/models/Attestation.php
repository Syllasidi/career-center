<?php

require_once __DIR__ . '/../config/database.php';

class Attestation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* =====================================
       LISTE DES ATTESTATIONS EN ATTENTE
       ===================================== */
    public function getAttestationsEnAttente(): array
    {
        $sql = "
            SELECT
                a.idattestation_rc,
                a.date_depot,
                u.nom,
                u.prenom,
                u.email,
                e.formation
            FROM attestation_rc a
            JOIN etudiant e ON e.idutilisateur = a.id_etudiant
            JOIN utilisateur u ON u.idutilisateur = e.idutilisateur
            WHERE a.statut_attestation = 'EN_ATTENTE_VALIDATION'
            ORDER BY a.date_depot ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================
       VALIDER ATTESTATION
       ===================================== */
    public function validerAttestation(
    int $idAttestation,
    int $idUtilisateur,
    string $role
): void {
    $this->db->beginTransaction();

    try {
        // verrou
        $stmt = $this->db->prepare("
            SELECT id_etudiant
            FROM attestation_rc
            WHERE idattestation_rc = :id
              AND statut_attestation = 'EN_ATTENTE_VALIDATION'
            FOR UPDATE
        ");
        $stmt->execute(['id' => $idAttestation]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Attestation introuvable ou déjà traitée.");
        }

        // cas secrétaire
        if ($role === 'SECRETAIRE') {
            $stmt = $this->db->prepare("
                UPDATE attestation_rc
                SET
                    statut_attestation = 'VALIDEE',
                    date_validation = CURRENT_DATE,
                    id_secretaire = :idUser
                WHERE idattestation_rc = :id
            ");
            $stmt->execute([
                'idUser' => $idUtilisateur,
                'id'     => $idAttestation
            ]);
        }

        // cas enseignant (remplacement)
        if ($role === 'ENSEIGNANT') {
            $stmt = $this->db->prepare("
                UPDATE attestation_rc
                SET
                    statut_attestation = 'VALIDEE',
                    date_validation = CURRENT_DATE
                WHERE idattestation_rc = :id
            ");
            $stmt->execute(['id' => $idAttestation]);
        }

        // 🔔 notification étudiant (OBLIGATOIRE)
        $stmt = $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur)
            VALUES (
                'Votre attestation RC a été validée.',
                'ATTESTATION_VALIDEE',
                :idEtudiant
            )
        ");
        $stmt->execute([
            'idEtudiant' => $row['id_etudiant'],
            
        ]);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}


    /* =====================================
       REFUSER ATTESTATION
       ===================================== */
    public function refuserAttestation(int $idAttestation, int $idValidateur): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT idattestation_rc, id_etudiant
                FROM attestation_rc
                WHERE idattestation_rc = :id
                  AND statut_attestation = 'EN_ATTENTE_VALIDATION'
                FOR UPDATE
            ");
            $stmt->execute(['id' => $idAttestation]);
            $att = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$att) {
                throw new Exception("Attestation introuvable ou déjà traitée.");
            }

            $this->db->prepare("
                UPDATE attestation_rc
                SET
                    statut_attestation = 'REFUSEE',
                    date_validation = CURRENT_DATE,
                    id_secretaire = :idSec
                WHERE idattestation_rc = :id
            ")->execute([
                'id'    => $idAttestation,
                'idSec' => $idValidateur
            ]);

            $this->db->prepare("
                INSERT INTO notification (message, type, idutilisateur)
                VALUES (:msg, 'ATTESTATION_REFUSEE', :idUser)
            ")->execute([
                'msg'    => 'Votre attestation RC a été refusée.',
                'idUser' => $att['id_etudiant']
            ]);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function deposerAttestation(
    int $idEtudiant,
    string $dateDebut,
    string $dateFin
): void {
    $this->db->beginTransaction();

    try {
        // 1️⃣ Insertion attestation
        $stmt = $this->db->prepare("
            INSERT INTO attestation_rc
            (statut_attestation, date_depot, date_debut_validite, date_fin_validite, id_etudiant)
            VALUES
            ('EN_ATTENTE_VALIDATION', CURRENT_DATE, :debut, :fin, :id)
        ");
        $stmt->execute([
            'debut' => $dateDebut,
            'fin'   => $dateFin,
            'id'    => $idEtudiant
        ]);

        // 2️⃣ Notification SECRÉTARIAT (globale)
        $stmt = $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur)
            VALUES (:msg, 'ATTESTATION_DEPOT', NULL)
        ");
        $stmt->execute([
            'msg' => "Une nouvelle attestation RC a été déposée par un étudiant."
        ]);

        $this->db->commit();

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}

public function getAttestationEtudiant(int $idEtudiant): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                idattestation_rc,
                statut_attestation,
                date_depot,
                date_debut_validite,
                date_fin_validite
            FROM attestation_rc
            WHERE id_etudiant = :id
        ");
        $stmt->execute(['id' => $idEtudiant]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

}
