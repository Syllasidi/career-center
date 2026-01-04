<?php
if (!isset($_SESSION)) session_start();

require_once __DIR__ . '/../../models/Attestation.php';
require_once __DIR__ . '/../../models/Secretaire.php';

$base = '/public';

$attestationModel = new Attestation();
$secretaireModel  = new Secretaire();

$idUser = (int)$_SESSION['user']['idutilisateur'];
$infos  = $secretaireModel->getInfosCompte($idUser);

$enConge = (bool)($infos['en_conge'] ?? false);
$attestations = $attestationModel->getAttestationsEnAttente();

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestations RC</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/secretaire.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Attestations RC</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/secretaire/">Accueil</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

<?php if ($success): ?>
    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($enConge): ?>
    <div class="info-notice">
        Vous êtes actuellement en congé. Les attestations sont en lecture seule.
    </div>
<?php endif; ?>

<?php foreach ($attestations as $rc): ?>
    <div class="rc-card">
        <div class="rc-card-header">
            <h4><?= htmlspecialchars($rc['nom'] . ' ' . $rc['prenom']) ?></h4>
            <span class="status status-pending">En attente</span>
        </div>

        <div class="rc-info">
            <p><strong>Email :</strong> <?= htmlspecialchars($rc['email']) ?></p>
            <p><strong>Formation :</strong> <?= htmlspecialchars($rc['formation']) ?></p>
            <p><strong>Date de dépôt :</strong> <?= htmlspecialchars($rc['date_depot']) ?></p>
        </div>

        <?php if (!$enConge): ?>
        <form method="POST" action="<?= $base ?>/secretaire/">
            <input type="hidden" name="idattestation" value="<?= (int)$rc['idattestation_rc'] ?>">

            <button class="btn-action" name="action" value="valider_attestation">
                Valider
            </button>

            <button class="btn-danger" name="action" value="refuser_attestation">
                Refuser
            </button>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</div>
</body>
</html>
