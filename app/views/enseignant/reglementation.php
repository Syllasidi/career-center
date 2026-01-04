<?php
/**
 * Vue : Réglementation
 * Rôle : Enseignant
 * Voir + modifier sur une seule page
 */

require_once __DIR__ . '/../../models/Reglementation.php';

$base = '/public';
$model = new Reglementation();

$regles = $model->getToutesReglementations();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Réglementation</title>
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
        <a href="<?= $base ?>/enseignant/?page=reglementation">Réglementation</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
<?php
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

    <div class="section">
        <h3>Réglementation des offres</h3>

        <table class="offers-table">
            <thead>
                <tr>
                    <th>Type de contrat</th>
                    <th>Pays</th>
                    <th>Durée min</th>
                    <th>Durée max</th>
                    <th>Rémunération min (€)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($regles as $r): ?>
                <tr>
                    <form method="POST" action="<?= $base ?>/enseignant/">
                        <input type="hidden" name="action" value="modifier_reglementation">
                        <input type="hidden" name="idReglementation" value="<?= $r['idreglementation'] ?>">

                        <td><?= htmlspecialchars($r['type_contrat']) ?></td>
                        <td><?= htmlspecialchars($r['pays']) ?></td>

                        <td>
                            <input type="number" name="duree_min"
                                   value="<?= htmlspecialchars($r['duree_min']) ?>" min="0">
                        </td>
                        <td>
                            <input type="number" name="duree_max"
                                   value="<?= htmlspecialchars($r['duree_max']) ?>" min="0">
                        </td>
                        <td>
                            <input type="number" name="remuneration_min"
                                   value="<?= htmlspecialchars($r['remuneration_min']) ?>" min="0" step="0.01">
                        </td>
                        <td>
                            <button class="btn-action">Mettre à jour</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
