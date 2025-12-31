<?php
/**
 * Gestion de la connexion à PostgreSQL
 * Base hébergée sur le serveur de l'école
 */

class Database {

    private static $connection = null;

    /**
     * Retourne une connexion PDO unique
     */
    public static function getConnection() {

        if (self::$connection === null) {

            try {
                self::$connection = new PDO(
                    "pgsql:host= postgresql-std-47868935748d.apps.kappsul.su.univ-lorraine.fr;port=5432;dbname=csi2025",
                    "postgres",
                    "n301GviVRE",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

            } catch (PDOException $e) {
                // En cas d'erreur, on stoppe tout
                die("Erreur de connexion à la base PostgreSQL");
            }
        }

        return self::$connection;
    }
}
