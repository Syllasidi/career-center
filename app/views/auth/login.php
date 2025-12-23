<?php
/**
 * Vue : Page de connexion
 * Conforme à la maquette du rapport
 * MVC : affichage uniquement
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC Career Center – Connexion</title>

    <!-- CSS externe -->
    <link rel="stylesheet" href="/career-center/public/assets/css/login.css">
</head>

<body>

    <div class="login-card">

        <h1>IDMC CAREER CENTER</h1>
        <p class="subtitle">Connexion à la plateforme</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="form-group">
                <label>Identifiant</label>
                <input type="text" name="email" placeholder="Votre identifiant" required>
            </div>

            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="Votre mot de passe" required>
            </div>

            <button type="submit" class="login-btn">
                Se connecter
            </button>

        </form>

        <div class="footer-text">
            Accès réservé aux étudiants, entreprises et personnels IDMC
        </div>

    </div>

</body>
</html>
