<?php
/**
 * Vue : Notifications Enseignant
 * Affichage uniquement
 */

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

$notifications = $model->getNotifications($_SESSION['user']['idutilisateur']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Notifications</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/enseignant.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Enseignant Responsable</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/enseignant/">Accueil</a>
        <a href="<?= $base ?>/enseignant/?page=offres">Validation offres</a>
        <a href="<?= $base ?>/enseignant/?page=affectations">Validation affectations</a>
        <a href="<?= $base ?>/enseignant/?page=attestations">Attestations RC</a>
        <a href="<?= $base ?>/enseignant/?page=compte">Compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <div class="section">
        <h3>Notifications</h3>

        <?php if (empty($notifications)): ?>
            <p>Aucune notification.</p>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notification-card">
                    <p class="notification-date">
                        <?= htmlspecialchars($n['date_notification']) ?>
                    </p>
                    <p class="notification-message">
                        <?= htmlspecialchars($n['message']) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
