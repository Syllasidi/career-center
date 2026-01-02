<?php
$base = '/public';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer des étudiants – Secrétaire</title>
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

    <h2>Création  des étudiants</h2>

    <div class="info-notice">
        <p>
            Format attendu (1 étudiant par ligne) :<br>
            <strong>nom;prenom;email;date_naissance;formation</strong>
        </p>
    </div>
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="info-notice">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="error-notice">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>


    <form method="POST" action="<?= $base ?>/secretaire/">
        <input type="hidden" name="action" value="creer_etudiants">

        <textarea name="liste_etudiants" rows="10" required
                  placeholder="Dupont;Jean;jean.dupont@etu.fr;2002-05-12;MIAGE"></textarea>

        <button type="submit" class="btn-primary">
            Créer les comptes étudiants
        </button>
    </form>

</div>

</body>
</html>
