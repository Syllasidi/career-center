<?php
/**
 * ================================
 * Vue : Compte Étudiant
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage uniquement
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Etudiant.php';

$base = '/public';
$model = new Etudiant();

$idEtudiant = (int) $_SESSION['user']['idutilisateur'];
$infos = $model->getInfosCompte($idEtudiant);

// messages flash
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Mon compte étudiant</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/etudiant.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
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

    <!-- ======= FLASH ======= -->
    <?php if ($success): ?>
        <div class="success-notice"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-notice"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ======= INFOS PERSONNELLES ======= -->
    <div class="section">
        <h3>Informations personnelles</h3>

        <p><strong>Nom :</strong> <?= htmlspecialchars($infos['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($infos['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($infos['email']) ?></p>
        <p><strong>Formation :</strong> <?= htmlspecialchars($infos['formation']) ?></p>
        <p><strong>Date de naissance :</strong>
            <?= $infos['date_naissance'] ? htmlspecialchars($infos['date_naissance']) : '—' ?>
        </p>
    </div>

    <!-- ======= MODIFIER PROFIL ======= -->
    <div class="section">
        <h3>Modifier mon profil</h3>

        <form method="POST" action="<?= $base ?>/etudiant/">
            <input type="hidden" name="action" value="modifier_profil">

            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse"
                       value="<?= htmlspecialchars($infos['adresse'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Compétences</label>
                <textarea name="competence"><?= htmlspecialchars($infos['competence'] ?? '') ?></textarea>
            </div>

            <button class="btn-action">Enregistrer les modifications</button>
        </form>
    </div>

    <!-- ======= PROFIL VISIBLE ======= -->
    <div class="section">
        <h3>Visibilité du profil</h3>

        <p>
            Statut actuel :
            <strong><?= $infos['profil_visible'] ? 'Visible' : 'Invisible' ?></strong>
        </p>

        <form method="POST" action="<?= $base ?>/etudiant/">
            <?php if ($infos['profil_visible']): ?>
                <input type="hidden" name="action" value="desactiver_profil">
                <button class="btn-danger">Rendre invisible</button>
            <?php else: ?>
                <input type="hidden" name="action" value="activer_profil">
                <button class="btn-action">Rendre visible</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- ======= RECHERCHE ACTIVE ======= -->
    <div class="section">
        <h3>Recherche active</h3>

        <p>
            Statut actuel :
            <strong><?= $infos['en_recherche_active'] ? 'Activée' : 'Désactivée' ?></strong>
        </p>

        <form method="POST" action="<?= $base ?>/etudiant/">
            <?php if ($infos['en_recherche_active']): ?>
                <input type="hidden" name="action" value="desactiver_recherche">
                <button class="btn-danger">Désactiver la recherche</button>
            <?php else: ?>
                <input type="hidden" name="action" value="activer_recherche">
                <button class="btn-action">Activer la recherche</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- ======= MOT DE PASSE ======= -->
    <div class="section">
        <h3>Changer le mot de passe</h3>

        <form method="POST" action="<?= $base ?>/etudiant/">
            <input type="hidden" name="action" value="changer_mdp">

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" required>
            </div>

            <button class="btn-action">Changer le mot de passe</button>
        </form>
    </div>

</div>

</body>
</html>
