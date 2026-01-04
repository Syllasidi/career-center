<?php
/**
 * ================================
 * Vue : Candidatures Entreprise
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage UNIQUEMENT
 * - Actions : accepter / refuser candidature
 */

require_once __DIR__ . '/../../models/Entreprise.php';

$base = '/public';

/* =====================
   SÉCURITÉ
   ===================== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
    header('Location: /public/');
    exit;
}

/* =====================
   DONNÉES
   ===================== */
$model = new Entreprise();
$candidatures = $model->getCandidaturesEntreprise($_SESSION['user']['idutilisateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Candidatures – Espace Entreprise</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/entreprise.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Entreprise</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/entreprise/">Accueil</a>
        <a href="<?= $base ?>/entreprise/?page=candidatures">Candidatures</a>

        <a href="<?= $base ?>/entreprise/?page=notification">Notifications</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ================= TITRE ================= -->
    <div class="section-header">
        <h2>Candidatures reçues</h2>
        <p>Gestion des candidatures sur vos offres</p>
    </div>

    <!-- ================= MESSAGES ================= -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ================= TABLE ================= -->
    <?php if (empty($candidatures)): ?>
        <p>Aucune candidature reçue pour le moment.</p>
    <?php else: ?>
        <table class="offers-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Email</th>
                    <th>Formation</th>
                    <th>Offre</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($candidatures as $c): ?>
                
                <tr>
                    <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['formation']) ?></td>
                    <td><?= htmlspecialchars($c['offre']) ?></td>
                    <td>
                        <span class="status">
                            <?= htmlspecialchars($c['statut_candidature']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($c['statut_candidature'] === 'EN_ATTENTE'): ?>

                            <form method="POST"
                                  action="<?= $base ?>/entreprise/"
                                  style="display:inline;">
                                <input type="hidden"
                                       name="idcandidature"
                                       value="<?= (int)$c['idcandidature'] ?>">
                                <button type="submit"
                                        name="action"
                                        value="accepter_candidature"
                                        class="btn-primary">
                                    Accepter
                                </button>
                            </form>

                            <form method="POST"
                                  action="<?= $base ?>/entreprise/"
                                  style="display:inline;">
                                <input type="hidden"
                                       name="idcandidature"
                                       value="<?= (int)$c['idcandidature'] ?>">
                                <button type="submit"
                                        name="action"
                                        value="refuser_candidature"
                                        class="btn-danger">
                                    Refuser
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

</body>
</html>
