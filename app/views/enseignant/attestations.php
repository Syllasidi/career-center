<?php
/**
 * Vue : Attestations RC (remplacement secrétariat)
 * Rôle : Enseignant
 * Affichage uniquement
 */

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();

/**
 * Les méthodes sont prévues,
 * la validation sera implémentée plus tard
 */
$secretaireAbsente = $model->toutesSecretairesEnConge();
$attestations = $secretaireAbsente
    ? $model->getAttestationsRCEnAttente()
    : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Attestations RC</title>
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
        <h3>Attestations de responsabilité civile</h3>

        <?php if (!$secretaireAbsente): ?>
            <div class="alert-notice">
                <p>
                    Le secrétariat est actuellement en poste.
                    La validation des attestations RC n’est pas accessible.
                </p>
            </div>
        <?php else: ?>

            <div class="alert-notice">
                <p><strong>Remplacement du secrétariat</strong></p>
                <p>Vous êtes autorisé à consulter les attestations RC en attente.</p>
            </div>

            <?php if (empty($attestations)): ?>
                <p>Aucune attestation en attente.</p>
            <?php else: ?>
                <?php foreach ($attestations as $rc): ?>
                    <div class="validation-card">
                        <h4><?= htmlspecialchars($rc['etudiant']) ?></h4>

                        <p><strong>Formation :</strong> <?= htmlspecialchars($rc['formation']) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($rc['email']) ?></p>
                        <p>
                            <strong>Période de validité :</strong>
                            <?= htmlspecialchars($rc['date_debut_validite']) ?>
                            →
                            <?= htmlspecialchars($rc['date_fin_validite']) ?>
                        </p>

                        <!-- Actions prévues mais non actives -->
                        <div class="validation-actions">
                            <button class="btn-action" disabled>Valider</button>
                            <button class="btn-danger" disabled>Refuser</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    </div>

</div>

</body>
</html>
