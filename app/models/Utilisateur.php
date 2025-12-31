<?php
require_once __DIR__ . '/../config/database.php';

class Utilisateur {

    public static function verifierIdentifiants($email, $password) {

        // 🔑 On récupère la connexion CORRECTEMENT
        $pdo = Database::getConnection();

        $sql = "
            SELECT u.idutilisateur, u.role, c.mdp
            FROM utilisateur u
            JOIN compte c ON c.idutilisateur = u.idUtilisateur
            WHERE u.email = ?
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['mdp'])) {
            return false;
        }

        return $user;
    }
}
