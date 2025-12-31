<?php
// ================================
// Vue admin - Dashboard
// Logique IDENTIQUE au projet Atelier
// ================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Admin.php';

// Connexion DB
$pdo = Database::getConnection();

// Instanciation du modèle (COMME Atelier)
$adminModel = new Admin($pdo);

// Récupération des stats
$stats = $adminModel->getStats();

// Base URL car ton serveur est sur /public
$base = '/public';

// Sécurité : valeurs par défaut
$totalUsers = $stats['total_users'] ?? 0;
$totalEntreprises = $stats['total_entreprises'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDMC CAREER CENTER - Espace Administrateur</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Administrateur</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/admin/">Accueil</a>
        <a href="<?= $base ?>/admin/?page=Comptes">Gestion des comptes</a>
        <a href="<?= $base ?>/admin/?page=notifications">Notifications</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <div class="welcome-section">
        <h2>Administration Système</h2>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= (int)$totalUsers ?></div>
            <div class="label">Comptes utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= (int)$totalEntreprises ?></div>
            <div class="label">Entreprises inscrites</div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h3>Gestion des comptes utilisateurs</h3>
            <a href="<?= $base ?>/admin/?page=creer_compte">
                <button class="btn-primary">Créer un nouveau compte</button>
            </a>
        </div>

        <p>Accéder à la liste complète et gérer les statuts des comptes.</p>
        <br>
        <a href="<?= $base ?>/admin/?page=Comptes">
            <button class="btn-primary">Voir la liste des comptes</button>
        </a>
    </div>
</div>

</body>
</html>
