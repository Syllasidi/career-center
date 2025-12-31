<?php
require_once __DIR__ . '/../../models/Admin.php';

$base = '/public';

$adminModel = new Admin();
$notifications = $adminModel->getNotificationsAdmin($_SESSION['user']['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Admin</title>
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
        <a href="<?= $base ?>/admin/?page=Comptes">Comptes</a>
        <a href="<?= $base ?>/admin/?page=notifications">Notifications</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h2>Notifications système</h2>

    <?php if (empty($notifications)): ?>
        <p>Aucune notification.</p>
    <?php else: ?>
        <div class="activity-list">
            <?php foreach ($notifications as $notif): ?>
                <div class="activity-item">
                    <div class="activity-header">
                        <span class="activity-user"><?= htmlspecialchars($notif['type']) ?></span>
                        <span class="activity-time"><?= htmlspecialchars($notif['date_notification']) ?></span>
                    </div>
                    <div class="activity-description">
                        <?= htmlspecialchars($notif['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
