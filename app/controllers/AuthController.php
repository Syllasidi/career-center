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
        header('Location: /public');
        exit;
    }
    $_SESSION['success'] = "Connexion réussie. Redirection ...";
    $_SESSION['user'] = [
    'idutilisateur' => $user['idutilisateur'],
    'nom'           => $user['nom'],
    'prenom'        => $user['prenom'],
    'email'         => $user['email'],
    'role'          => $user['role']
];

    require __DIR__ . '/../views/auth/login.php';
    exit;
}
