<?php
$base = '/public';

/* =====================
   SÉCURITÉ
   ===================== */
if (!isset($_SESSION['user'])) {
    header("Location: {$base}/");
    exit;
}

$idUtilisateur = (int)($_SESSION['user']['idutilisateur'] ?? 0);
$role = $_SESSION['user']['role'] ?? '';

if ($idUtilisateur <= 0 || $role === '') {
    header("Location: {$base}/");
    exit;
}

/* =====================
   ROUTE ACCUEIL PAR RÔLE
   ===================== */
$home = match ($role) {
    'ENTREPRISE' => "{$base}/entreprise/",
    'ENSEIGNANT' => "{$base}/enseignant/",
    'SECRETAIRE' => "{$base}/secretaire/",
    'ETUDIANT'   => "{$base}/etudiant/",
    default      => "{$base}/"
};

/* =====================
   DONNÉES
   ===================== */
require_once __DIR__ . '/../../models/Notification.php';

$notificationModel = new Notification();
$notifications = $notificationModel->getNotificationsPourUtilisateur($idUtilisateur, $role);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>

    <!-- ✅ UN SEUL CSS -->
    <link rel="stylesheet" href="<?= $base ?>/assets/css/entreprise.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Notifications</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $home ?>">Accueil</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="section">
        <div class="section-header">
            <h3>Mes notifications</h3>
        </div>

        <!-- ===== Messages session ===== -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- ===== Tableau ===== -->
        <?php if (empty($notifications)): ?>
            <p>Aucune notification.</p>
        <?php else: ?>
            <table class="offers-table">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($notifications as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['message'] ?? '') ?></td>
                        <td><?= htmlspecialchars($n['type'] ?? '') ?></td>
                        <td>
                            <?= !empty($n['date_notification'])
                                ? date('d/m/Y H:i', strtotime($n['date_notification']))
                                : '—'
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
