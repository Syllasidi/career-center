<?php
/**
 * ================================
 * Vue : Accueil Entreprise
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage UNIQUEMENT
 * - Les données viennent du modèle Entreprise
 */

require_once __DIR__ . '/../../models/Entreprise.php';

$base = '/public';

// Instanciation du modèle
$entrepriseModel = new Entreprise();

// ID entreprise depuis la session
$idEntreprise = $_SESSION['user']['idutilisateur'] ?? null;

// Données (branchées plus tard sur la base)
$stats        = $entrepriseModel->getStatsEntreprise($idEntreprise);
$offres       = $entrepriseModel->getOffresEntreprise($idEntreprise);
$candidatures = $entrepriseModel->getCandidaturesEntreprise($idEntreprise);

// Sécurité valeurs par défaut
$nbOffresActives   = $stats['offres_actives'] ?? 0;
$nbOffresAttente   = $stats['offres_attente'] ?? 0;
$nbCandidatures    = $stats['candidatures'] ?? 0;
$nbOffresPourvues  = $stats['offres_pourvues'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDMC CAREER CENTER - Espace Entreprise</title>
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
        <a href="<?= $base ?>/entreprise/?page=offres">Mes offres</a>
        <a href="<?= $base ?>/entreprise/?page=candidatures">Candidatures</a>
        <a href="<?= $base ?>/entreprise/?page=creer_offre">Créer une offre</a>
        <a href="<?= $base ?>/entreprise/?page=compte">Compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ================= WELCOME ================= -->
    <div class="welcome-section">
        <h2><?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?></h2>
        <p>Espace entreprise – gestion des offres et candidatures</p>
    </div>

    <!-- ================= STATS ================= -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= (int)$nbOffresActives ?></div>
            <div class="label">Offres actives</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$nbOffresAttente ?></div>
            <div class="label">En attente de validation</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$nbCandidatures ?></div>
            <div class="label">Candidatures reçues</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$nbOffresPourvues ?></div>
            <div class="label">Offres pourvues</div>
        </div>
    </div>

    <!-- ================= OFFRES ================= -->
    <div class="section">
        <div class="section-header">
            <h3>Mes offres</h3>
            <a href="<?= $base ?>/entreprise/?page=creer_offre">
                <button class="btn-primary">Créer une nouvelle offre</button>
            </a>
        </div>

        <?php if (empty($offres)): ?>
            <p>Aucune offre créée.</p>
        <?php else: ?>
            <table class="offers-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Candidatures</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($offres as $offre): ?>
                    <tr>
                        <td><?= htmlspecialchars($offre['titre']) ?></td>
                        <td>
                            <span class="offer-type-badge">
                                <?= htmlspecialchars($offre['type_contrat']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($offre['date_publication']) ?></td>
                        <td><?= (int)$offre['nb_candidatures'] ?></td>
                        <td>
                            <span class="status <?= $offre['status_class'] ?>">
                                <?= htmlspecialchars($offre['statut_label']) ?>
                            </span>
                        </td>
                        <td>
    <a href="<?= $base ?>/entreprise/?page=modifier_offre&id=<?= (int)$offre['idoffre'] ?>">
        <button class="btn-action">Modifier</button>
    </a>

    <a href="<?= $base ?>/entreprise/?page=gerer_offre&id=<?= (int)$offre['idoffre'] ?>">
        <button class="btn-danger">Gérer</button>
    </a>
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
