<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENSEIGNANT') {
    header('Location: /public/index.php');
    exit;
}

/* =====================
   TRAITEMENT POST
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../app/controllers/EnseignantController.php';
    $controller = new EnseignantController();

    if ($_POST['action'] === 'valider_offre') {
        $controller->validerOffre();
        exit;
    }

    if ($_POST['action'] === 'rejeter_offre') {
        $controller->rejeterOffre();
        exit;
    if ($_POST['action'] === 'changer_mdp') {
        $ctrl->changerMdp();
        exit;
    }
}}

/* =====================
   AFFICHAGE VUE
   ===================== */
$page = $_GET['page'] ?? 'index';

$allowed = ['index', 'offres', 'affectations', 'notifications', 'compte','reglementation','attestations'];
if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/enseignant/{$page}.php";