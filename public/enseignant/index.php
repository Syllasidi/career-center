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
    }

    if ($_POST['action'] === 'changer_mdp') {
        $controller->changerMdp();
        exit;
    }
    if ($_POST['action'] === 'valider_affectation') {
    $controller->validerAffectation();
    exit;
}

if ($_POST['action'] === 'rejeter_affectation') {
    $controller->rejeterAffectation();
    exit;
}
if ($_POST['action'] === 'valider_affectation_urgente') {
    $controller->validerAffectationUrgente();
    exit;
}
if ($_POST['action'] === 'modifier_reglementation') {
    $controller->modifierReglementation();
    exit;
}
if ($_POST['action'] === 'acceder_attestations') {
    $controller->accederAttestations();
}

if ($_POST['action'] === 'valider_attestation') {
    $controller->validerAttestation();
}

if ($_POST['action'] === 'rejeter_attestation') {
    $controller->rejeterAttestation();
}



}
require_once __DIR__ . '/../../app/controllers/EnseignantController.php';

$controller = new EnseignantController();

// 🔔 déclenchement automatique
$controller->declencherRelances();


/* =====================
   AFFICHAGE VUE
   ===================== */
$page = $_GET['page'] ?? 'index';

$allowed = ['index', 'offres', 'affectations', 'notifications', 'compte','reglementation','attestations'];
if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/enseignant/{$page}.php";
