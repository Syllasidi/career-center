<?php
/**
 * Vue : Page de connexion

 * MVC : affichage uniquement
 */
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC Career Center – Connexion</title>

    <!-- CSS externe -->
  <link rel="stylesheet" href="assets/css/login.css">
  <?php if (!empty($_SESSION['success'])): ?>
    <meta http-equiv="refresh" content="2;url=/public">
<?php endif; ?>


</head>

<body>

    <div class="login-card">

        <h1>IDMC CAREER CENTER</h1>
        <p class="subtitle">Connexion à la plateforme</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
    <div class="success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
<?php
    unset($_SESSION['success']);
endif;
?>


     <form method="post" action="auth.php">


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

        <!-- 🔽 AJOUT ICI -->
        <div class="separator">— ou —</div>

       <a href="/public/entreprise/" class="register-btn">
    Créer un compte entreprise
</a>




        <div class="footer-text">
            Accès réservé aux étudiants, entreprises et personnels IDMC
        </div>

    </div>

</body>
</html>
