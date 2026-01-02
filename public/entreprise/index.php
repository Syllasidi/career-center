<?php
session_start();

/* =====================
   TRAITEMENT POST
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once __DIR__ . '/../../app/controllers/entrepriseController.php';
    $controller = new entrepriseController();

    if ($_POST['action'] === 'inscription') {
        $controller->inscription();
        exit;
    }
    if ($_POST['action'] === 'creer_offre') {
        $controller->creerOffre();
        exit;
}
}


/* =====================
   SÉCURITÉ
   ===================== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {

    // NON connecté → inscription par défaut
    $page = $_GET['page'] ?? 'inscription';

} else {

    // CONNECTÉ → accueil par défaut
    $page = $_GET['page'] ?? 'index';
}

/* =====================
   PAGES AUTORISÉES
   ===================== */
$allowed = ['inscription', 'index','creer_offre'];

if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/entreprise/{$page}.php";