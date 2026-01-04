<?php
require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

/**
 * Si idoffre est passé → on affiche UNE offre
 * Sinon → toutes
 */
if (!empty($_GET['idoffre'])) {
    $offres = array_filter(
        $model->getToutesOffresAValider(),
        fn($o) => $o['idoffre'] === (int)$_GET['idoffre']
    );
} else {
    $offres = $model->getToutesOffresAValider();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Validation des offres</title>
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
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="section">
        <h3>Offres en attente de validation</h3>

        <?php if (empty($offres)): ?>
            <p>Aucune offre à valider.</p>
        <?php else: ?>
            <?php foreach ($offres as $offre): ?>
                <div class="validation-card">

                    <div class="validation-card-header">
                        <h4><?= htmlspecialchars($offre['titre']) ?></h4>
                        <span class="offer-type-badge"><?= htmlspecialchars($offre['type_contrat']) ?></span>
                    </div>

                    <div class="validation-info">
                        <p><strong>Entreprise :</strong> <?= htmlspecialchars($offre['raison_sociale']) ?></p>
                        <p><strong>Durée :</strong> <?= htmlspecialchars($offre['duree']) ?></p>
                        <p><strong>Localisation :</strong> <?= htmlspecialchars($offre['localisation']) ?></p>
                        <p><strong>Date de dépôt :</strong> <?= htmlspecialchars($offre['date_depot']) ?></p>
                        <p><strong>Rémunération :</strong>
                            <?= $offre['remuneration'] !== null
                                ? htmlspecialchars($offre['remuneration']) . " €"
                                : "—" ?>
                        </p>
                    </div>

                    <div class="detail-section">
                        <h5>Description</h5>
                        <p><?= htmlspecialchars($offre['description']) ?></p>
                    </div>

                    <!-- ACTIONS MÉTIER (ICI SEULEMENT) -->
                    <div class="validation-actions">
                        <form method="POST" action="<?= $base ?>/enseignant/">
                            <input type="hidden" name="idOffre" value="<?= (int)$offre['idoffre'] ?>">

                            <button class="btn-action" name="action" value="valider_offre">
                                Valider l’offre
                            </button>

                            <button class="btn-danger" name="action" value="rejeter_offre">
                                Refuser l’offre
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
