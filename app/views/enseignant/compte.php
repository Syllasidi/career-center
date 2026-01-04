<?php
/**
 * Vue : Compte Enseignant
 * Rôle : Enseignant responsable
 * Affichage uniquement
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

// messages flash
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// droit remplacement secrétariat
$peutRemplacerSecretaire = $model->toutesSecretairesEnConge();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Mon compte</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/enseignant.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Enseignant Responsable</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/enseignant/">Accueil</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ======= FLASH MESSAGES ======= -->
    <?php if (!empty($success)): ?>
        <div class="success-notice">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error-notice">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- ======= INFOS PERSONNELLES ======= -->
    <div class="section">
        <h3>Informations personnelles</h3>

        <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user']['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user']['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?></p>
        <p><strong>Rôle :</strong> Enseignant responsable</p>
    </div>

    <!-- ======= MOT DE PASSE ======= -->
    <div class="section">
        <h3>Changer le mot de passe</h3>

        <form method="POST" action="<?= $base ?>/enseignant/">
            <input type="hidden" name="action" value="changer_mdp">

            <div class="form-group">
                <label>Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" required>
            </div>

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" required>
            </div>

            <button class="btn-action">
                Mettre à jour le mot de passe
            </button>
        </form>
    </div>

    <!-- ======= REMPLACEMENT SECRÉTARIAT ======= -->
    <div class="section">
        <h3>Remplacement du secrétariat</h3>

        <p>
            En cas d’absence complète du secrétariat, un enseignant responsable
            peut assurer temporairement la gestion des attestations RC
            afin de garantir la continuité du service.
        </p>

        <?php if ($peutRemplacerSecretaire): ?>
            <div class="alert-notice">
                Toutes les secrétaires sont actuellement en congé.
                Vous pouvez accéder à la gestion des attestations RC.
            </div>

            <form method="POST" action="<?= $base ?>/enseignant/">
                <input type="hidden" name="action" value="acceder_attestations">
                <button class="btn-action">
                    Accéder aux attestations RC
                </button>
            </form>
        <?php else: ?>
            <div class="info-notice">
                Au moins une secrétaire est en service.
                Le remplacement du secrétariat n’est pas autorisé.
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
