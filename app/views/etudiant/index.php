<?php
require_once __DIR__ . '/../../models/Etudiant.php';

if (!isset($_SESSION)) session_start();

$idEtudiant = (int)($_SESSION['user']['idutilisateur'] ?? 0);
$model = new Etudiant();

$stats        = $model->getStats($idEtudiant);
$offres       = $model->getOffresActives(6);
$candidatures = $model->getCandidatures($idEtudiant);
$attestation  = $model->getAttestationRC($idEtudiant);

$prenom = $_SESSION['user']['prenom'] ?? '';
$nom    = $_SESSION['user']['nom'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Espace Étudiant</title>
    <link rel="stylesheet" href="/public/assets/css/etudiant.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Étudiant</p>
    </div>
    <div class="navbar-right">
        <a href="/public/etudiant/?page=offres">Offres</a>
        <a href="/public/etudiant/?page=candidatures">Mes candidatures</a>
        <a href="/public/etudiant/?page=compte">Compte</a>
        <a href="/public/etudiant/?page=attestation">Attestation</a>
        <a href="/public/etudiant/?page=notifications">Notifications</a>
        <a href="/public/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="welcome-section">
        <h2>Bienvenue, <?= htmlspecialchars("$prenom $nom") ?></h2>
        <p>Espace étudiant – IDMC Career Center</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $stats['offres_disponibles'] ?></div>
            <div class="label">Offres disponibles</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['candidatures_en_cours'] ?></div>
            <div class="label">Candidatures en cours</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['reponses'] ?></div>
            <div class="label">Réponses reçues</div>
        </div>
    </div>

    <div class="section">
        <h3>Offres actives</h3>
        <div class="offers-grid">
            <?php foreach ($offres as $o): ?>
                <div class="offer-card">
                    <span class="offer-type"><?= htmlspecialchars($o['type_contrat']) ?></span>
                    <h4><?= htmlspecialchars($o['titre']) ?></h4>
                    <p><strong>Entreprise :</strong> <?= htmlspecialchars($o['raison_sociale']) ?></p>
                    <p><strong>Localisation :</strong> <?= htmlspecialchars($o['ville']) ?></p>
                    <p><strong>Date limite :</strong> <?= htmlspecialchars($o['date_expiration'] ?? '—') ?></p>
                    <button>Consulter l'offre</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section">
        <h3>Mes candidatures</h3>
        <table class="applications-table">
            <thead>
                <tr>
                    <th>Poste</th>
                    <th>Entreprise</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($candidatures as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['titre']) ?></td>
                    <td><?= htmlspecialchars($c['raison_sociale']) ?></td>
                    <td><?= htmlspecialchars($c['type_contrat']) ?></td>
                    <td><?= htmlspecialchars($c['date_candidature']) ?></td>
                    <td><?= htmlspecialchars($c['statut_candidature']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Attestation de responsabilité civile</h3>
        <?php if ($attestation): ?>
            <div class="document-status">
                <p><strong>Statut :</strong> <?= htmlspecialchars($attestation['statut_attestation']) ?></p>
                <p><strong>Date dépôt :</strong> <?= htmlspecialchars($attestation['date_depot']) ?></p>
                <p><strong>Validité :</strong> <?= htmlspecialchars($attestation['date_fin_validite']) ?></p>
            </div>
        <?php else: ?>
            <p>Aucune attestation déposée.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
