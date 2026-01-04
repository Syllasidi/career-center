<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ETUDIANT') {
    header('Location: /public/index.php');
    exit;
}

/* ===== POST ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../app/controllers/EtudiantController.php';
    $controller = new EtudiantController();

    switch ($_POST['action']) {
        case 'modifier_profil': $controller->modifierProfil(); break;
        case 'activer_profil': $controller->activerProfil(); break;
        case 'desactiver_profil': $controller->desactiverProfil(); break;
        case 'activer_recherche': $controller->activerRecherche(); break;
        case 'desactiver_recherche': $controller->desactiverRecherche(); break;
        case 'changer_mdp': $controller->changerMotDePasse(); break;
        case 'deposer_attestation' : $controller->deposerAttestation(); break;
        case 'postuler' : $controller->postuler(); break;
        case 'renoncer_candidature':$controller->renoncerCandidature();    break;
    }
}

/* ===== VIEW ===== */
$page = $_GET['page'] ?? 'index';
$allowed = ['index','compte','offres','candidatures','notifications','attestation'];
if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/etudiant/{$page}.php";
