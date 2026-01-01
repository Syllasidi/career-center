<?php
session_start();

if (isset($_SESSION['user'])) {

    switch ($_SESSION['user']['role']) {

        case 'ETUDIANT':
            header('Location: /public/etudiant/');
            exit;

        case 'ENTREPRISE':
            header('Location: /public/entreprise/');
            exit;

        case 'ENSEIGNANT':
            header('Location: /public/enseignant/');
            exit;

        case 'SECRETAIRE':
            header('Location: /public/secretaire/');
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
