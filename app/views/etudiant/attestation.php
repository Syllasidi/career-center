<?php
/**
 * ================================
 * Vue : Attestation RC Étudiant
 * ================================
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Attestation.php';

$base = '/public';
$model = new Attestation();

$idEtudiant = (int) $_SESSION['user']['idutilisateur'];
$attestation = $model->getAttestationEtudiant($idEtudiant);

// messages flash
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER – Attestation RC</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/etudiant.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Étudiant</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/etudiant/">Accueil</a>
        <a href="<?= $base ?>/etudiant/?page=compte">Mon compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ===== FLASH ===== -->
    <?php if ($success): ?>
        <div class="success-notice"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-notice"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ===== ÉTAT ATTESTATION ===== -->
    <div class="section">
        <h3>Attestation de responsabilité civile</h3>

        <?php if ($attestation): ?>
            <p><strong>Statut :</strong>
                <?= htmlspecialchars($attestation['statut_attestation']) ?>
            </p>

            <p><strong>Date de dépôt :</strong>
                <?= htmlspecialchars($attestation['date_depot']) ?>
            </p>

            <p><strong>Période de validité :</strong>
                <?= htmlspecialchars($attestation['date_debut_validite']) ?>
                →
                <?= htmlspecialchars($attestation['date_fin_validite']) ?>
            </p>

            <?php if ($attestation['statut_attestation'] === 'EN_ATTENTE_VALIDATION'): ?>
                <div class="info-notice">
                    Votre attestation est en cours de validation par le secrétariat.
                </div>
            <?php endif; ?>

            <?php if ($attestation['statut_attestation'] === 'VALIDEE'): ?>
                <div class="success-notice">
                    Votre attestation est validée.
                </div>
            <?php endif; ?>

            <?php if ($attestation['statut_attestation'] === 'REFUSEE'): ?>
                <div class="error-notice">
                    Votre attestation a été refusée.
                    Vous pouvez en déposer une nouvelle.
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="info-notice">
                Aucune attestation déposée pour le moment.
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== FORMULAIRE DÉPÔT ===== -->
    <?php if (!$attestation || $attestation['statut_attestation'] === 'REFUSEE'): ?>
        <div class="section">
            <h3>Déposer une attestation</h3>

            <form method="POST" action="<?= $base ?>/etudiant/">
                <input type="hidden" name="action" value="deposer_attestation">

                <div class="form-group">
                    <label>Date de début de validité</label>
                    <input type="date" name="date_debut" required>
                </div>

                <div class="form-group">
                    <label>Date de fin de validité</label>
                    <input type="date" name="date_fin" required>
                </div>

                <button class="btn-action">
                    Déposer l’attestation
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
