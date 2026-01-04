<?php

require_once __DIR__ . '/../config/database.php';

class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getNotificationsPourUtilisateur(
        int $idUtilisateur,
        string $role
    ): array {

        // 🔹 Notifications personnelles
        $sql = "
            SELECT message, type, date_notification
            FROM notification
            WHERE idutilisateur = :id
        ";

        $params = [
            'id' => $idUtilisateur
        ];

        // 🔹 Notifications globales pour ENSEIGNANT uniquement
        if ($role === 'ENSEIGNANT') {
            $sql .= "
                OR (
                    idutilisateur IS NULL
                    AND type IN (
                        'VALIDATION_OFFRE',
                        'VALIDATION_CANDIDATURE',
                        'RELANCE_AFFECTATION'
                    )
                )
            ";
        }
        if ($role === 'SECRETAIRE') {
    $sql .= "
        OR (
            idutilisateur IS NULL
            AND type IN (
                'ATTESTATION_DEPOT'
            )
        )
    ";
}

        $sql .= " ORDER BY date_notification DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
