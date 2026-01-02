<?php
require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

$offres = $model->getOffresAValider();
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
        <a href="<?= $base ?>/enseignant/?page=affectations">Validation affectations</a>
        <a href="<?= $base ?>/enseignant/?page=notifications">Notifications</a>
        <a href="<?= $base ?>/enseignant/?page=compte">Compte</a>
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
                        <p><strong>Entreprise :</strong> <?= htmlspecialchars($offre['entreprise']) ?></p>
                        <p><strong>Ville :</strong> <?= htmlspecialchars($offre['ville']) ?></p>
                        <p><strong>Durée :</strong> <?= htmlspecialchars($offre['duree']) ?> mois</p>
                        <p><strong>Rémunération :</strong> <?= htmlspecialchars($offre['remuneration']) ?></p>
                    </div>

                    <div class="validation-actions">
                        <form method="POST" action="<?= $base ?>/enseignant/">
                            <input type="hidden" name="idOffre" value="<?= $offre['idoffre'] ?>">
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
