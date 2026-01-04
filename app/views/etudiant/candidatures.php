<?php
/**
 * ================================
 * Vue : Mes candidatures (Étudiant)
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage + actions étudiant
 * - Renoncement simple ou justifié
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../../models/Etudiant.php';

$base  = '/public';
$model = new Etudiant();

$idEtudiant = (int) $_SESSION['user']['idutilisateur'];

/* ===== DONNÉES ===== */
$candidatures = $model->getCandidaturesDetaillees($idEtudiant);

/* ===== FLASH ===== */
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER – Mes candidatures</title>
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

    <!-- ================= TABLE ================= -->
    <div class="section">
        <h3>Mes candidatures</h3>

        <?php if (empty($candidatures)): ?>
            <div class="info-notice">
                Vous n’avez encore effectué aucune candidature.
            </div>
        <?php else: ?>

        <table class="offers-table">
            <thead>
                <tr>
                    <th>Offre</th>
                    <th>Entreprise</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($candidatures as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['offre']) ?></td>
                    <td><?= htmlspecialchars($c['entreprise']) ?></td>
                    <td><?= htmlspecialchars($c['type_contrat']) ?></td>
                    <td><?= htmlspecialchars($c['date_candidature']) ?></td>
                    <td>
                        <span class="status <?= strtolower($c['statut_candidature']) ?>">
                            <?= htmlspecialchars($c['statut_candidature']) ?>
                        </span>
                    </td>
                    <td>

                        <?php if ($c['statut_candidature'] !== 'RENONCEE'): ?>

                            <form method="POST" action="<?= $base ?>/etudiant/" class="inline-form">
                                <input type="hidden" name="action" value="renoncer_candidature">
                                <input type="hidden" name="idcandidature" value="<?= (int)$c['idcandidature'] ?>">

                                <?php if ($c['statut_candidature'] === 'AFFECTEE'): ?>
                                    <textarea name="justification"
                                              placeholder="Justification obligatoire"
                                              required
                                              class="justification-textarea"></textarea>
                                <?php endif; ?>

                                <button class="btn-danger">
                                    Renoncer
                                </button>
                            </form>

                        <?php else: ?>
                            —
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

        <?php endif; ?>
    </div>

</div>

</body>
</html>
