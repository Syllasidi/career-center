<?php
$base = '/public';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Entreprise – IDMC Career Center</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/entreprise.css">
</head>
<body>

<div class="container">

    <div class="form-container">

        <h2>Inscription Entreprise</h2>
        <p>Création d’un compte entreprise</p>

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

        <form method="POST" action="<?= $base ?>/entreprise/">

            <input type="hidden" name="action" value="inscription">

            <div class="form-group">
                <label>Raison sociale</label>
                <input type="text" name="raison_sociale" required>
            </div>

            <div class="form-group">
                <label>SIRET</label>
                <input type="text" name="siret" maxlength="14" required>
            </div>

            <div class="form-group">
                <label>Secteur d’activité</label>
                <input type="text" name="secteur_activite">
            </div>

            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mdp" required>
                </div>

                <div class="form-group">
                    <label>Confirmation mot de passe</label>
                    <input type="password" name="confirmation_mdp" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Créer le compte entreprise</button>
                <a href="<?= $base ?>/">Se connecter</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>
