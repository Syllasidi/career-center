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
if ($_POST['action'] === 'brouillon_offre') {
        $controller->enregistrerBrouillon();
        exit;
    }
    if ($_POST['action'] === 'modifier_offre') {
    $controller->modifierOffrePost();
    exit;
}
if ($_POST['action'] === 'modifier_offre'
        || $_POST['action'] === 'enregistrer_modification'
        || $_POST['action'] === 'soumettre_modification') {

        $controller->modifierOffrePost();
    }
    if ($_POST['action'] === 'accepter_candidature'
 || $_POST['action'] === 'refuser_candidature') {

    $controller->gererCandidatureAction();
}

    if (in_array($_POST['action'], [
    'publier_offre',
    'desactiver_offre',
    'reactiver_offre'
], true)) {

    $controller->gererOffreAction();
   
}
if ($_POST['action'] === 'proposer_offre') {
    $controller->proposerOffreAction();
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
$allowed = ['inscription', 'index','creer_offre','modifier_offre','gerer_offre','notification','candidatures','etudiant'];

if (!in_array($page, $allowed, true)) {
    $page = 'index';
}

require __DIR__ . "/../../app/views/entreprise/{$page}.php";