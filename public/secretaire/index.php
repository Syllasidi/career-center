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
    if ($_POST['action'] === 'changer_mdp') {
    $ctrl->changerMdp();
    exit;
}
if ($_POST['action'] === 'changer_conge') {
    $ctrl->changerConge();
    exit;
}
if ($_POST['action'] === 'valider_attestation') {
    $controller->validerAttestation();
    exit;
}

if ($_POST['action'] === 'refuser_attestation') {
    $controller->refuserAttestation();
    exit;
}


}

/* GET */
$page = $_GET['page'] ?? 'index';
$allowed = ['index', 'compte', 'creer_etudiants','attestations','notifications'];
if (!in_array($page, $allowed)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/secretaire/{$page}.php";
