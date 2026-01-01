<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'SECRETAIRE') {
    header('Location: /public/index.php');
    exit;
}

/* POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../app/controllers/secretaireController.php';
    $ctrl = new secretaireController();

    if ($_POST['action'] === 'creer_etudiants') {
        $ctrl->creerEtudiants();
    }

    if ($_POST['action'] === 'valider_attestation') {
        $ctrl->validerAttestation();
    }

    if ($_POST['action'] === 'refuser_attestation') {
        $ctrl->refuserAttestation();
    }
}

/* GET */
$page = $_GET['page'] ?? 'index';
$allowed = ['index', 'etudiants', 'creer_etudiants','attestations'];
if (!in_array($page, $allowed)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/secretaire/{$page}.php";
