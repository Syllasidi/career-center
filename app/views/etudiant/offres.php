<?php
/**
 * ================================
 * Vue : Offres disponibles (Étudiant)
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage uniquement
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Etudiant.php';

$base  = '/public';
$model = new Etudiant();

$idEtudiant = (int) $_SESSION['user']['idutilisateur'];

/* ===== FILTRES ===== */
$type = $_GET['type'] ?? null;
$pays = $_GET['pays'] ?? null;

$offres = $model->getOffresDisponibles($type, $pays);

/* ===== FLASH ===== */
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER – Offres</title>
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
        <a href="<?= $base ?>/etudiant/?page=offres">Offres</a>
        <a href="<?= $base ?>/etudiant/?page=candidatures">Mes candidatures</a>
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

    <!-- ================= FILTRES ================= -->
    <div class="section">
        <h3>Filtrer les offres</h3>

        <form method="GET" class="filters-form">
        <input type="hidden" name="page" value="offres">


            <div class="filters-grid">
                <div class="form-group">
                    <label>Type de contrat</label>
                    <select name="type">
    <option value="">Tous</option>
    <option value="STAGE" <?= $type === 'STAGE' ? 'selected' : '' ?>>Stage</option>
    <option value="ALTERNANCE" <?= $type === 'ALTERNANCE' ? 'selected' : '' ?>>Alternance</option>
    <option value="CDD" <?= $type === 'CDD' ? 'selected' : '' ?>>CDD</option>
</select>

                </div>

                <div class="form-group">
                    <label>Pays</label>
                    <select name="pays">
                        <option value="">Tous</option>
                        <option value="FRANCE" <?= $pays === 'FRANCE' ? 'selected' : '' ?>>France</option>
                        <option value="ETRANGER" <?= $pays === 'ETRANGER' ? 'selected' : '' ?>>Étranger</option>
                    </select>
                </div>
            </div>

            <button class="btn-action">Appliquer les filtres</button>
        </form>
    </div>

    <!-- ================= OFFRES ================= -->
    <div class="section">
        <h3>Offres disponibles</h3>

        <?php if (empty($offres)): ?>
            <div class="info-notice">
                Aucune offre ne correspond aux critères sélectionnés.
            </div>
        <?php else: ?>

            <div class="offers-grid">
                <?php foreach ($offres as $o): ?>

                    <?php
                    // Vérification droit à postuler
                    $check = $model->peutPostuler($idEtudiant, (int)$o['idoffre']);
                    ?>

                    <div class="offer-card">
                        <h4><?= htmlspecialchars($o['titre']) ?></h4>

                        <p><strong>Entreprise :</strong> <?= htmlspecialchars($o['raison_sociale']) ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($o['type_contrat']) ?></p>
                        <p><strong>Localisation :</strong>
                            <?= htmlspecialchars($o['ville']) ?> (<?= htmlspecialchars($o['pays']) ?>)
                        </p>
                        <p><strong>Date de fin :</strong> <?= htmlspecialchars($o['date_fin']) ?></p>

                        <?php if ($check['ok']): ?>
                            <!-- ===== POSTULER ===== -->
                            <form method="POST" action="<?= $base ?>/etudiant/">
    <input type="hidden" name="action" value="postuler">
    <input type="hidden" name="idoffre" value="<?= (int)$o['idoffre'] ?>">
    <button type="submit" class="btn-action">Postuler</button>
</form>

                        <?php else: ?>
                            <!-- ===== BLOQUÉ ===== -->
                            <div class="blocked-zone">
                                <button class="btn-disabled" disabled>
                                    Postulation indisponible
                                </button>
                                <p class="blocked-reason">
                                    <?= htmlspecialchars($check['msg']) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

</div>

</body>
</html>
