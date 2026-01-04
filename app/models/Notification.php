<?php

require_once __DIR__ . '/../config/database.php';

class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getNotificationsByUtilisateur(int $idUtilisateur): array
    {
        $stmt = $this->db->prepare("
    SELECT message, type, date_notification
    FROM notification
    WHERE idutilisateur = :id
    ORDER BY date_notification DESC
");


        $stmt->execute(['id' => $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
