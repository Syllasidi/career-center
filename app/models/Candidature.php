<?php

require_once __DIR__ . '/../config/database.php';

class Candidature
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * REFUSER une candidature
     */
    public function refuserCandidature(int $idCandidature, int $idEntreprise): void
    {
        // Vérification candidature + appartenance entreprise
        $stmt = $this->db->prepare("
            SELECT c.idcandidature, c.id_etudiant, o.titre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
              AND o.id_entreprise = :entreprise
              AND c.statut_candidature = 'EN_ATTENTE'
        ");
        $stmt->execute([
            'id' => $idCandidature,
            'entreprise' => $idEntreprise
        ]);

        $candidature = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidature) {
            throw new Exception("Refus impossible.");
        }

        // Mise à jour statut
        $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = 'REJETEE'
            WHERE idcandidature = :id
        ")->execute(['id' => $idCandidature]);

        // Notification étudiant
        $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur, idcandidature)
            VALUES (
                :message,
                'CANDIDATURE_REFUSEE',
                :idEtudiant,
                :idCandidature
            )
        ")->execute([
            'message' => "Votre candidature pour l’offre « {$candidature['titre']} » a été refusée.",
            'idEtudiant' => $candidature['id_etudiant'],
            'idCandidature' => $idCandidature
        ]);
    }

    /**
     * ACCEPTER une candidature
     */
    public function accepterCandidature(int $idCandidature, int $idEntreprise): void
    {
        $stmt = $this->db->prepare("
            SELECT c.idcandidature, c.id_etudiant, o.titre
            FROM candidature c
            JOIN offre o ON o.idoffre = c.idoffre
            WHERE c.idcandidature = :id
              AND o.id_entreprise = :entreprise
              AND c.statut_candidature = 'EN_ATTENTE'
        ");
        $stmt->execute([
            'id' => $idCandidature,
            'entreprise' => $idEntreprise
        ]);

        $candidature = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidature) {
            throw new Exception("Acceptation impossible.");
        }

        // Passage à validation enseignant
        $this->db->prepare("
            UPDATE candidature
            SET statut_candidature = 'EN_VALIDATION_ENSEIGNANT'
            WHERE idcandidature = :id
        ")->execute(['id' => $idCandidature]);

        // Notification étudiant
        $this->db->prepare("
            INSERT INTO notification (message, type, idutilisateur, idcandidature)
            VALUES (
                :message,
                'CANDIDATURE_PRESELECTIONNEE',
                :idEtudiant,
                :idCandidature
            )
        ")->execute([
            'message' => "Votre candidature a été retenue par l’entreprise et transmise à un enseignant.",
            'idEtudiant' => $candidature['id_etudiant'],
            'idCandidature' => $idCandidature
        ]);

        // Notification enseignant
        $this->db->exec("
            INSERT INTO notification (message, type, idcandidature)
            SELECT
                'Une candidature nécessite votre validation.',
                'VALIDATION_CANDIDATURE',
                {$idCandidature}
            FROM enseignant
        ");
    }
    public function proposerOffre(
    int $idOffre,
    int $idEtudiant,
    int $idEntreprise
): void {

    // 🔐 Sécurité : l’offre doit appartenir à l’entreprise et être publiée
    $stmt = $this->db->prepare("
        SELECT idoffre
        FROM offre
        WHERE idoffre = :idoffre
          AND id_entreprise = :idEntreprise
          AND statut_offre = 'PUBLIEE'
    ");
    $stmt->execute([
        'idoffre' => $idOffre,
        'idEntreprise' => $idEntreprise
    ]);

    if (!$stmt->fetch()) {
        throw new Exception("Offre non autorisée.");
    }

    // ❌ Empêcher doublon (même offre → même étudiant)
    $stmt = $this->db->prepare("
        SELECT 1
        FROM candidature
        WHERE idoffre = :idoffre
          AND id_etudiant = :idEtudiant
    ");
    $stmt->execute([
        'idoffre' => $idOffre,
        'idEtudiant' => $idEtudiant
    ]);

    if ($stmt->fetch()) {
        throw new Exception("Cette offre a déjà été proposée à cet étudiant.");
    }

    // 🧾 Création candidature (origine ENTREPRISE)
    $stmt = $this->db->prepare("
        INSERT INTO candidature (
            idoffre,
            id_etudiant,
            statut_candidature,
            origine_candidature,
            date_candidature
        )
        VALUES (
            :idoffre,
            :idEtudiant,
            'EN_ATTENTE',
            'ENTREPRISE',
            CURRENT_DATE
        )
    ");
    $stmt->execute([
        'idoffre' => $idOffre,
        'idEtudiant' => $idEtudiant
    ]);

    // 🔔 Notification étudiant
    $stmt = $this->db->prepare("
        INSERT INTO notification (
            message,
            type,
            idutilisateur,
            idoffre,
            date_notification
        )
        VALUES (
            'Une entreprise vous a proposé une offre',
            'PROPOSITION_OFFRE',
            :idEtudiant,
            :idoffre,
            CURRENT_DATE
        )
    ");
    $stmt->execute([
        'idEtudiant' => $idEtudiant,
        'idoffre' => $idOffre
    ]);
}
}
