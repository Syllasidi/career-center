<?php
/**
 * Vue : Affectations à valider
 * Rôle : Enseignant
 * Affichage uniquement
 */

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

$affectations = $model->getAffectationsAValider();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Validation des affectations</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/enseignant.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Enseignant Responsable</p>
    </div>

    <div class="navbar-right">
        <a href="<?= $base ?>/enseignant/">Accueil</a>
        <a href="<?= $base ?>/enseignant/?page=offres">Validation offres</a>
        <a href="<?= $base ?>/enseignant/?page=affectations">Validation affectations</a>
        <a href="<?= $base ?>/enseignant/?page=attestations">Attestations RC</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="section">
        <h3>Affectations en attente de validation</h3>

        <?php if (empty($affectations)): ?>
            <p>Aucune affectation à valider.</p>
        <?php else: ?>
            <?php foreach ($affectations as $a): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h4>Affectation proposée</h4>
                        <span class="status status-awaiting">En attente</span>
                    </div>

                    <div class="assignment-details">
                        <div class="assignment-column">
                            <h5>Étudiant</h5>
                            <p><strong>Nom :</strong> <?= htmlspecialchars($a['etudiant']) ?></p>
                            <p><strong>Formation :</strong> <?= htmlspecialchars($a['formation']) ?></p>
                            <p><strong>Email :</strong> <?= htmlspecialchars($a['email_etudiant']) ?></p>
                        </div>

                        <div class="assignment-column">
                            <h5>Offre</h5>
                            <p><strong>Poste :</strong> <?= htmlspecialchars($a['offre']) ?></p>
                            <p><strong>Entreprise :</strong> <?= htmlspecialchars($a['entreprise']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars($a['type_contrat']) ?></p>
                        </div>
                    </div>

                    <!-- Actions (implémentées plus tard) -->
                    <div class="validation-actions">
                        <button class="btn-action" disabled>Valider l'affectation</button>
                        <button class="btn-danger" disabled>Refuser l'affectation</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
