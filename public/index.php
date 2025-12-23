<?php
/**
 * Front Controller
 * Point d’entrée unique de l’application
 * Toute requête passe par ce fichier
 */

session_start();

// Chargement des bases
require_once '../app/config/database.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Model.php';

// Par défaut : page de connexion
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

// Construction du contrôleur
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = "../app/controllers/$controllerName.php";

// Si le contrôleur existe
if (file_exists($controllerFile)) {

    require_once $controllerFile;
    $controllerObject = new $controllerName();

    // Si l’action existe
    if (method_exists($controllerObject, $action)) {
        $controllerObject->$action();
        exit;
    }
}

// CAS PAR DÉFAUT : redirection propre vers login
header("Location: index.php?controller=auth&action=login");
exit;
