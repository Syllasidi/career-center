<?php
/**
 * Vue : Compte Enseignant
 * Affichage uniquement
 */

$base = '/public';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Mon compte</title>
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
        <a href="<?= $base ?>/enseignant/?page=notifications">Notifications</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="section">
        <h3>Informations personnelles</h3>

        <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user']['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user']['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?></p>
        <p><strong>Rôle :</strong> Enseignant responsable</p>
    </div>

    <div class="section">
        <h3>Changer le mot de passe</h3>

        <form method="POST" action="<?= $base ?>/enseignant/">
            <div class="form-group">
                <label>Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" required>
            </div>

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" required>
            </div>

            <button class="btn-action" disabled>
                Mettre à jour (à implémenter)
            </button>
        </form>
    </div>

    <div class="section">
        <h3>Remplacement du secrétariat</h3>

        <p>
            En cas d’absence de l’ensemble du secrétariat, un enseignant responsable
            peut être amené à assurer temporairement certaines tâches administratives
            (ex : attestations RC).
        </p>

        <div class="alert-notice">
            <p>Ce mécanisme est géré automatiquement par le système.</p>
        </div>
    </div>

</div>

</body>
</html>
