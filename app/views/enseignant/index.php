<?php
/**
 * ================================
 * Vue : Accueil Enseignant
 * Projet : IDMC Career Center (CSI)
 * ================================
 * - Affichage uniquement
 * - Les données viennent du modèle Enseignant
 */

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';

if (!isset($_SESSION)) {
    session_start();
}

$idEnseignant = (int)($_SESSION['user']['idutilisateur'] ?? 0);

$model = new Enseignant();

$stats = $model->getStats($idEnseignant);
$offresAValider = $model->getOffresAValider();

// navbar notif (simple)
$notifCount = $model->countNotifications($idEnseignant);

// valeurs par défaut
$offresAttente       = $stats['offres_attente'] ?? 0;
$affectationsAttente = $stats['affectations_attente'] ?? 0;
$offresValidees      = $stats['offres_validees'] ?? 0;
$affectationsOk      = $stats['affectations_ok'] ?? 0;

// messages flash
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDMC CAREER CENTER - Espace Enseignant Responsable</title>
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
<a href="<?= $base ?>/enseignant/?page=reglementation">Reglementations</a>
<a href="<?= $base ?>/enseignant/?page=notifications">Notifications</a>
<a href="<?= $base ?>/enseignant/?page=compte">Compte</a>
<a href="<?= $base ?>/logout.php">Déconnexion</a>

    </div>
</nav>

<div class="container">

    <!-- ======= WELCOME ======= -->
    <div class="welcome-section">
        <h2>
            <?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?>
        </h2>
        <p>Enseignant Responsable</p>
    </div>

    <!-- ======= FLASH MESSAGES ======= -->
    <?php if (!empty($success)): ?>
        <div class="success-notice">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error-notice">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- ======= STATS ======= -->
    <div class="stats-grid">
        <div class="stat-card alert">
            <div class="number"><?= (int)$offresAttente ?></div>
            <div class="label">Offres en attente de validation</div>
        </div>

        <div class="stat-card alert">
            <div class="number"><?= (int)$affectationsAttente ?></div>
            <div class="label">Affectations à valider</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$offresValidees ?></div>
            <div class="label">Offres validées</div>
        </div>

        <div class="stat-card">
            <div class="number"><?= (int)$affectationsOk ?></div>
            <div class="label">Affectations confirmées</div>
        </div>
    </div>

    <!-- ======= OFFRES À VALIDER ======= -->
    <div class="section">
        <h3>Offres en attente de validation</h3>

        <?php if (empty($offresAValider)): ?>
            <div class="info-notice">
                <p>Aucune offre en attente.</p>
            </div>
        <?php else: ?>
            <?php foreach ($offresAValider as $o): ?>
                <div class="validation-card">
                    <div class="validation-card-header">
                        <h4><?= htmlspecialchars($o['titre']) ?></h4>
                        <span class="offer-type-badge"><?= htmlspecialchars($o['type_contrat']) ?></span>
                    </div>

                    <div class="validation-info">
                        <p><strong>Entreprise :</strong> <?= htmlspecialchars($o['raison_sociale']) ?></p>
                        <p><strong>Durée :</strong> <?= htmlspecialchars($o['duree'] ?? '—') ?></p>
                        <p><strong>Localisation :</strong> <?= htmlspecialchars($o['localisation'] ?? '—') ?></p>
                        <p><strong>Date de dépôt :</strong> <?= htmlspecialchars($o['date_depot'] ?? '—') ?></p>
                        <p><strong>Rémunération :</strong>
                            <?= $o['remuneration'] !== null ? htmlspecialchars($o['remuneration']) . " €" : "—" ?>
                        </p>
                    </div>

                    <div class="detail-section">
                        <h5>Description</h5>
                        <p>
                            <?= !empty($o['description']) ? htmlspecialchars($o['description']) : "Description non renseignée." ?>
                        </p>
                    </div>

                    <div class="validation-actions">
                        <!-- VALIDER -->
                        <form method="POST" action="<?= $base ?>/enseignant/" style="display:inline;">
                            <input type="hidden" name="action" value="valider_offre">
                            <input type="hidden" name="idoffre" value="<?= (int)$o['idoffre'] ?>">
                            <button class="btn-action" type="submit">Valider l'offre</button>
                        </form>

                        <!-- REFUSER -->
                        <form method="POST" action="<?= $base ?>/enseignant/" style="display:inline;">
                            <input type="hidden" name="action" value="rejeter_offre">
                            <input type="hidden" name="idoffre" value="<?= (int)$o['idoffre'] ?>">
                            <button class="btn-danger" type="submit">Refuser l'offre</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ======= AFFECTATIONS (placeholder) ======= -->
    <div class="section">
        <h3>Affectations en attente de validation</h3>

        <div class="alert-notice">
            <p><strong>Validation des affectations :</strong></p>
            <p>
                Une fois qu'une entreprise accepte la candidature d'un étudiant,
                l'affectation doit être validée par l'enseignant responsable.
                (Cette partie sera branchée après.)
            </p>
        </div>

        <div class="info-notice">
            <p>Aucune affectation en attente pour le moment.</p>
        </div>
    </div>
    <h3 style="margin-top: 30px; font-size: 18px;">Historique des validations récentes</h3>

<table class="offers-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Objet</th>
            <th>Entreprise / Étudiant</th>
            <th>Action</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>

    <?php if (empty($historique)): ?>
        <tr>
            <td colspan="6">Aucune validation récente</td>
        </tr>
    <?php else: ?>
        <?php foreach ($historique as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['date']) ?></td>
                <td><?= htmlspecialchars($item['type']) ?></td>
                <td><?= htmlspecialchars($item['objet']) ?></td>
                <td><?= htmlspecialchars($item['acteur']) ?></td>
                <td><?= htmlspecialchars($item['action']) ?></td>
                <td>
                    <span class="status <?= htmlspecialchars($item['status_class']) ?>">
                        <?= htmlspecialchars($item['status_label']) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
</table>


</div>

</body>
</html>
