<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Enseignant.php';
require_once __DIR__ . '/../../models/Attestation.php';

$base = '/public';

$ens = new Enseignant();
if (!$ens->toutesSecretairesEnConge()) {
    header('Location: /public/enseignant/?page=compte');
    exit;
}

$model = new Attestation();
$attestations = $model->getAttestationsEnAttente();

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Attestations RC</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/enseignant.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Remplacement du secrétariat</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/enseignant/">Accueil</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

<?php if ($success): ?>
    <div class="success-notice"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error-notice"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="section">
    <h3>Attestations RC en attente</h3>

    <?php if (empty($attestations)): ?>
        <p>Aucune attestation en attente.</p>
    <?php else: ?>
        <?php foreach ($attestations as $a): ?>
            <div class="validation-card">
                <p><strong>Étudiant :</strong> <?= htmlspecialchars($a['prenom'].' '.$a['nom']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($a['email']) ?></p>
                <p><strong>Formation :</strong> <?= htmlspecialchars($a['formation']) ?></p>
                <p><strong>Date dépôt :</strong> <?= htmlspecialchars($a['date_depot']) ?></p>

                <form method="POST" action="<?= $base ?>/enseignant/">
                    <input type="hidden" name="idattestation" value="<?= (int)$a['idattestation_rc'] ?>">

                    <button class="btn-action" name="action" value="valider_attestation">
                        Valider
                    </button>

                    <button class="btn-danger" name="action" value="rejeter_attestation">
                        Rejeter
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>
</body>
</html>
