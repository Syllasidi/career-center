<?php
/**
 * Déconnexion générale
 * Projet CSI – IDMC Career Center
 */

// Démarrer la session (si pas déjà fait)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supprimer toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Redirection vers la page de connexion
header('Location: /public/index.php');
exit;
