<?php


require_once __DIR__ . '/../../models/Secretaire.php';

$base = '/public';

$secretaire = new Secretaire();
$infos = $secretaire->getInfosCompte($_SESSION['user']['idutilisateur']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte – Secrétaire</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/secretaire.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Secrétaire</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/secretaire/">Accueil</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <h2>Mon compte</h2>

    <!-- ===== FEEDBACK ===== -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-notice"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-notice"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ===== INFOS PERSO ===== -->
    <div class="section">
        <h3>Informations personnelles</h3>

        <p><strong>Nom :</strong> <?= htmlspecialchars($infos['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($infos['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($infos['email']) ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($infos['role']) ?></p>
        <p>
    <strong>En congé :</strong>
    <?= $infos['en_conge'] ? 'Oui' : 'Non' ?>
</p>
    </div>

    <!-- ===== CHANGEMENT MDP ===== -->
    <div class="section">
        <h3>Changer le mot de passe</h3>

        <form method="POST" action="<?= $base ?>/secretaire/">
            <input type="hidden" name="action" value="changer_mdp">

            <label>Nouveau mot de passe</label>
            <input type="password" name="nouveau_mdp" required>

            <label>Confirmation</label>
            <input type="password" name="confirmation_mdp" required>

            <button type="submit" class="btn-primary">
                Modifier le mot de passe
            </button>
        </form>
    </div>

    <div class="section">
    <h3>Statut de congé</h3>

    <form method="POST" action="<?= $base ?>/secretaire/">
        <input type="hidden" name="action" value="changer_conge">

        <?php if ($infos['en_conge']): ?>
            <input type="hidden" name="en_conge" value="0">
            <button type="submit" class="btn-primary">
                Reprendre le travail
            </button>
        <?php else: ?>
            <input type="hidden" name="en_conge" value="1">
            <button type="submit" class="btn-danger">
                Passer en congé
            </button>
        <?php endif; ?>
    </form>
</div>


</div>

</body>
</html>
