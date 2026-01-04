<?php
$base = '/public';

/* =====================
   SÉCURITÉ
   ===================== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
    header('Location: /public/');
    exit;
}

$idOffre = (int)($_GET['id'] ?? 0);
$idEntreprise = (int)$_SESSION['user']['idutilisateur'];

/* =====================
   CHARGEMENT MODÈLES
   ===================== */
require_once __DIR__ . '/../../models/Entreprise.php';

$entrepriseModel = new Entreprise();
$offre = $entrepriseModel->getOffreEntrepriseById($idOffre, $idEntreprise);




/* =====================
   CANDIDATURES LIÉES
   ===================== */
$candidatures = $entrepriseModel->getCandidaturesEntreprise($idEntreprise);
$candidaturesOffre = array_filter(
    $candidatures,
    fn($c) => $c['offre'] === $offre['titre']
);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer une offre – Espace Entreprise</title>
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
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ================= OFFRE ================= -->
    <div class="section">
        <h2><?= htmlspecialchars($offre['titre']) ?></h2>

        <p>
            <strong>Type :</strong> <?= htmlspecialchars($offre['type_contrat']) ?><br>
            <strong>Lieu :</strong> <?= htmlspecialchars($offre['ville']) ?> (<?= htmlspecialchars($offre['pays']) ?>)<br>
            <strong>Dates :</strong>
            <?= htmlspecialchars($offre['date_debut']) ?> → <?= htmlspecialchars($offre['date_fin']) ?><br>
            <strong>Rémunération :</strong> <?= htmlspecialchars($offre['remuneration']) ?> €<br>
            <strong>Statut :</strong>
            <span class="status status-<?= strtolower($offre['statut_offre']) ?>">
                <?= htmlspecialchars($offre['statut_offre']) ?>
            </span>
        </p>
    </div>

    <!-- ================= MESSAGES ================= -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ================= ACTIONS OFFRE ================= -->
    <div class="section">
        <h3>Actions sur l’offre</h3>

        <form method="POST"
      action="<?= $base ?>/entreprise/?page=gerer_offre&id=<?= $offre['idoffre'] ?>">

            <input type="hidden" name="idoffre" value="<?= $offre['idoffre'] ?>">

            <?php if ($offre['statut_offre'] === 'VALIDEE'): ?>
                <button name="action" value="publier_offre" class="btn-primary">
                    Publier l’offre
                </button>
            <?php endif; ?>

            <?php if ($offre['statut_offre'] === 'PUBLIEE'): ?>
                <button name="action" value="desactiver_offre" class="btn-danger">
                    Désactiver l’offre
                </button>
            <?php endif; ?>

            <?php if ($offre['statut_offre'] === 'DESACTIVEE'): ?>
                <button name="action" value="reactiver_offre" class="btn-primary">
                    Réactiver l’offre
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- ================= CANDIDATURES ================= -->
    <div class="section">
        <h3>Candidatures reçues</h3>

        <?php if (empty($candidaturesOffre)): ?>
            <p>Aucune candidature pour cette offre.</p>
        <?php else: ?>
            <table class="offers-table">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Formation</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($candidaturesOffre as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></td>
                        <td><?= htmlspecialchars($c['formation']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['date_candidature']) ?></td>
                        <td><?= htmlspecialchars($c['statut_candidature']) ?></td>
                        <td>
                            <?php if ($c['statut_candidature'] === 'EN_ATTENTE'): ?>
                                <form method="POST" action="<?= $base ?>/entreprise/" style="display:inline;">
                                    <input type="hidden" name="idcandidature" value="<?= (int)$c['idcandidature'] ?>">
                                    <button name="action" value="accepter_candidature" class="btn-primary">
                                        Accepter
                                    </button>
                                </form>

                                <form method="POST" action="<?= $base ?>/entreprise/" style="display:inline;">
                                    <input type="hidden" name="idcandidature" value="<?= (int)$c['idcandidature'] ?>">
                                    <button name="action" value="refuser_candidature" class="btn-danger">
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

</div>

</body>
</html>
