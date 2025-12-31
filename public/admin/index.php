<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMINISTRATEUR') {
    header('Location: /public/index.php');
    exit;
}

/* =====================
   TRAITEMENT POST ADMIN
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once __DIR__ . '/../../app/controllers/adminController.php';
    $controller = new adminController();

    if ($_POST['action'] === 'creer_compte_interne') {
        $controller->creerCompteInterne();
        exit;
    }
}

/* =====================
   AFFICHAGE DES VUES
   ===================== */
$page = $_GET['page'] ?? 'index';

$allowed = ['index', 'Comptes', 'creer_compte','notifications'];
if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/admin/{$page}.php";
