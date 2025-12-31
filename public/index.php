<?php
session_start();

if (isset($_SESSION['user'])) {

    switch ($_SESSION['user']['role']) {

        case 'ETUDIANT':
            header('Location: /etudiant/');
            exit;

        case 'ENTREPRISE':
            header('Location: /entreprise/');
            exit;

        case 'ENSEIGNANT':
            header('Location: /enseignant/');
            exit;

        case 'SECRETAIRE':
            header('Location: /secretaire/');
            exit;

        case 'ADMINISTRATEUR':
            header('Location: /public/admin/');
            exit;
    }
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Affiche la vue login (app reste privé)
require __DIR__ . '/../app/views/auth/login.php';
