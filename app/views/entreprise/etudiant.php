<?php
require_once __DIR__ . '/../../models/Entreprise.php';

$base = '/public';
$model = new Entreprise();

$idEntreprise = $_SESSION['user']['idutilisateur'];
$etudiants = $model->getEtudiantsVisibles();

$offres = array_filter(
    $model->getOffresEntreprise($idEntreprise),
    fn($o) => $o['statut_label'] === 'Publiée'
);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Proposer une offre – Entreprise</title>
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
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <h2>Étudiants disponibles</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <table class="offers-table">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Formation</th>
                <th>Proposer une offre</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($etudiants as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></td>
                <td><?= htmlspecialchars($e['formation']) ?></td>
                <td>
                    <?php if (!empty($offres)): ?>
                        <form method="POST" action="<?= $base ?>/entreprise/">
                            <input type="hidden" name="id_etudiant" value="<?= (int)$e['idutilisateur'] ?>">
                            <select name="idoffre" required>
                                <option value="">— Choisir une offre —</option>
                                <?php foreach ($offres as $o): ?>
                                    <option value="<?= (int)$o['idoffre'] ?>">
                                        <?= htmlspecialchars($o['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button
                                type="submit"
                                name="action"
                                value="proposer_offre"
                                class="btn-primary">
                                Proposer
                            </button>
                        </form>
                    <?php else: ?>
                        Aucune offre publiée
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>
