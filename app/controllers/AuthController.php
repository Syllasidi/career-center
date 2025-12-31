<?php
session_start();

require_once __DIR__ . '/../models/Utilisateur.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
        header('Location: /');
        exit;
    }

    $user = Utilisateur::verifierIdentifiants($email, $password);

    if (!$user) {
        $_SESSION['error'] = "Identifiants incorrects";
        header('Location: /');
        exit;
    }

    $_SESSION['user'] = [
        'id'   => $user['idutilisateur'], 
        'role' => $user['role']
    ];

    header('Location: /public'); // index.php redirige vers /admin/ /etudiant/ etc
    exit;
}
