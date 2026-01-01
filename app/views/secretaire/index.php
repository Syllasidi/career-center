<?php
/**
 * ================================
 * Vue : Dashboard Secrétaire
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage UNIQUEMENT
 * - Les données viennent du modèle Secretaire
 */

require_once __DIR__ . '/../../models/Secretaire.php';

$base = '/public';

// Instanciation du modèle
$secretaireModel = new Secretaire();

// Données (branchées plus tard sur la base)
$stats        = $secretaireModel->getStats();
$attestations = $secretaireModel->getAttestationsEnAttente();
$etudiants    = $secretaireModel->getEtudiants();

// Sécurité valeurs par défaut
$totalEtudiants   = $stats['total_etudiants'] ?? 0;
$rcEnAttente      = $stats['rc_en_attente'] ?? 0;
$rcValidees       = $stats['rc_validees'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDMC CAREER CENTER - Espace Secrétaire</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/secretaire.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Secrétaire</p>
    </div>
    <div class="navbar-right">
        <a href="<?= $base ?>/secretaire/?page=creer_etudiants">Créer des étudiants</a>
        <a href="<?= $base ?>/secretaire/?page=attestations">Attestations RC</a>
        <a href="<?= $base ?>/secretaire/?page=compte">Compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- ================= WELCOME ================= -->
    <div class="welcome-section">
      <h2>
    <?= htmlspecialchars(
        ($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')
    ) ?>
</h2>
        <p>Secrétariat pédagogique</p>
    </div>

    <!-- ================= STATS ================= -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= (int)$totalEtudiants ?></div>
            <div class="label">Étudiants inscrits</div>
        </div>

        <div class="stat-card alert">
            <div class="number"><?= (int)$rcEnAttente ?></div>
            <div class="label">Attestations RC à valider</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$rcValidees ?></div>
            <div class="label">Attestations RC validées</div>
        </div>
    </div>

    <!-- ================= ATTESTATIONS RC ================= -->
    <div class="section">
        <h3>Attestations de responsabilité civile en attente</h3>

        <?php if (empty($attestations)): ?>
            <div class="info-notice">
                <p>Aucune attestation en attente.</p>
            </div>
        <?php else: ?>
            <?php foreach ($attestations as $rc): ?>
                <div class="rc-card">
                    <div class="rc-card-header">
                        <h4><?= htmlspecialchars($rc['nom_prenom']) ?></h4>
                        <span class="status status-pending">En attente</span>
                    </div>

                    <div class="rc-info">
                        <p><strong>Formation :</strong> <?= htmlspecialchars($rc['formation']) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($rc['email']) ?></p>
                        <p><strong>Date de dépôt :</strong> <?= htmlspecialchars($rc['date_depot']) ?></p>
                        <p><strong>Période de validité :</strong>
                            <?= htmlspecialchars($rc['date_debut']) ?> -
                            <?= htmlspecialchars($rc['date_fin']) ?>
                        </p>
                    </div>

                    <div class="rc-actions">
                        <button class="btn-action">Valider</button>
                        <button class="btn-danger">Refuser</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ================= ETUDIANTS ================= -->
    <div class="section">
        <h3>Gestion des étudiants</h3>

        <table class="students-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom et Prénom</th>
                    <th>Formation</th>
                    <th>Email</th>
                    <th>Attestation RC</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($etudiants)): ?>
                <tr>
                    <td colspan="6">Aucun étudiant enregistré</td>
                </tr>
            <?php else: ?>
                <?php foreach ($etudiants as $etu): ?>
                    <tr>
                        <td><?= htmlspecialchars($etu['id']) ?></td>
                        <td><?= htmlspecialchars($etu['nom_prenom']) ?></td>
                        <td><?= htmlspecialchars($etu['formation']) ?></td>
                        <td><?= htmlspecialchars($etu['email']) ?></td>
                        <td>
                            <span class="status <?= $etu['rc_status_class'] ?>">
                                <?= htmlspecialchars($etu['rc_status_label']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action">Modifier</button>
        
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
