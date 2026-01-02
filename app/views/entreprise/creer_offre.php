<?php
/**
 * ================================
 * Vue : Créer une offre
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage UNIQUEMENT
 * - Envoi POST vers le controller
 */

$base = '/public';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une offre – Espace Entreprise</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/entreprise.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Entreprise</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/entreprise/">Accueil</a>
        <a href="<?= $base ?>/entreprise/?page=offres">Mes offres</a>
        <a href="<?= $base ?>/entreprise/?page=compte">Compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="form-container">

        <h2>Créer une offre</h2>
        <p>Les offres sont soumises à validation avant publication.</p>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="<?= $base ?>/entreprise/">

            <input type="hidden" name="action" value="creer_offre">

            <!-- ================= CHAMPS ESSENTIELS ================= -->

            <div class="form-group">
                <label>Type de contrat</label>
                <select name="type_contrat" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="STAGE">Stage</option>
                    <option value="ALTERNANCE">Alternance</option>
                    <option value="CDD">CDD</option>
                </select>
            </div>

            <div class="form-group">
                <label>Titre de l’offre</label>
                <input type="text" name="titre" required>
            </div>

            <div class="form-group">
                <label>Description / Missions principales</label>
                <textarea name="description" required></textarea>
            </div>

            <!-- ================= CHAMPS RÉGLEMENTAIRES ================= -->

            <div class="form-row">
                <div class="form-group">
                    <label>Pays</label>
                    <select name="pays" required>
                        <option value="FRANCE">France</option>
                        <option value="ETRANGER">Étranger</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="ville" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" required>
                </div>

                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" required>
                </div>
            </div>

            <div class="form-group">
                <label>Rémunération mensuelle (€)</label>
                <input type="number" name="remuneration" step="0.01" required>
            </div>

            <!-- ================= ACTIONS ================= -->

            <div class="form-actions">
    <button type="submit" name="action" value="creer_offre">
        Soumettre l’offre
    </button>

    <button type="submit" name="action" value="brouillon_offre">
        Enregistrer en brouillon
    </button>

    <a href="<?= $base ?>/entreprise/">Annuler</a>
</div>

        </form>

    </div>

</div>

</body>
</html>
