<?php
// ================================
// Vue : Gestion des comptes internes
// ================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Admin.php';

$pdo = Database::getConnection();
$adminModel = new Admin($pdo);

$comptes = $adminModel->getComptesInternes();

$base = '/public';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des comptes</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-right">
    <a href="<?= $base ?>/admin/">Accueil</a>
    <a href="<?= $base ?>/admin/?page=creer_compte">Créer un compte</a>
    <a href="<?= $base ?>/logout.php">Déconnexion</a>
</div>
</nav>

<div class="container">

    <h2>Comptes internes</h2>

    <table class="users-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Identifiant</th>
                <th>Date création</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>

        <?php if (empty($comptes)): ?>
            <tr>
                <td colspan="6">Aucun compte interne</td>
            </tr>
        <?php else: ?>
            <?php foreach ($comptes as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= $c['role'] ?></td>
                    <td><?= htmlspecialchars($c['identifiant']) ?></td>
                    <td><?= $c['date_creation'] ?></td>
                    <td><?= $c['statut'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>
