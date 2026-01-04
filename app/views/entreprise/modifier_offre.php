<?php


$base = '/public';

/* =====================
   SÉCURITÉ
   ===================== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
    header('Location: /public/');
    exit;
}

/* =====================
   PARAMÈTRES
   ===================== */
$idOffre = (int)($_GET['id'] ?? 0);
$idEntreprise = (int)($_SESSION['user']['idutilisateur'] ?? 0);

if ($idOffre <= 0 || $idEntreprise <= 0) {
    $_SESSION['error'] = "Offre invalide.";
    header('Location: /public/entreprise/');
    exit;
}

/* =====================
   CHARGEMENT OFFRE
   ===================== */
require_once __DIR__ . '/../../models/Entreprise.php';

$entrepriseModel = new Entreprise();
$offre = $entrepriseModel->getOffreEntrepriseById($idOffre, $idEntreprise);

if (!$offre) {
    $_SESSION['error'] = "Offre introuvable.";
    header('Location: /public/entreprise/');
    exit;
}

/* =====================
   RÈGLE MÉTIER
   ===================== */

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une offre – Espace Entreprise</title>
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
        <a href="<?= $base ?>/entreprise/?page=compte">Compte</a>
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="form-container">

        <h2>Modifier une offre</h2>
        <p>⚠️ Toute modification de champs réglementaires entraîne une revalidation par un enseignant.</p>

        <!-- ================= MESSAGES ================= -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-success"><?= htmlspecialchars((string)$_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error"><?= htmlspecialchars((string)$_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- ================= FORMULAIRE ================= -->
        <form method="POST" action="<?= $base ?>/entreprise/?page=modifier_offre&id=<?= (int)$offre['idoffre'] ?>">


            <input type="hidden" name="action" value="modifier_offre">
            <input type="hidden" name="idoffre" value="<?= (int)$offre['idoffre'] ?>">

            <div class="form-group">
                <label>Type de contrat</label>
                <select name="type_contrat" required>
                    <option value="STAGE" <?= $offre['type_contrat'] === 'STAGE' ? 'selected' : '' ?>>Stage</option>
                    <option value="ALTERNANCE" <?= $offre['type_contrat'] === 'ALTERNANCE' ? 'selected' : '' ?>>Alternance</option>
                    <option value="CDD" <?= $offre['type_contrat'] === 'CDD' ? 'selected' : '' ?>>CDD</option>
                </select>
            </div>

            <div class="form-group">
                <label>Titre de l’offre</label>
                <input
                    type="text"
                    name="titre"
                    required
                    value="<?= htmlspecialchars((string)$offre['titre']) ?>">
            </div>

            <div class="form-group">
                <label>Description / Missions</label>
                <textarea name="description" required><?= htmlspecialchars((string)$offre['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Pays</label>
                    <select name="pays" required>
                        <option value="FRANCE" <?= $offre['pays'] === 'FRANCE' ? 'selected' : '' ?>>France</option>
                        <option value="ETRANGER" <?= $offre['pays'] === 'ETRANGER' ? 'selected' : '' ?>>Étranger</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ville</label>
                    <input
                        type="text"
                        name="ville"
                        required
                        value="<?= htmlspecialchars((string)$offre['ville']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date de début</label>
                    <input
                        type="date"
                        name="date_debut"
                        required
                        value="<?= htmlspecialchars((string)$offre['date_debut']) ?>">
                </div>

                <div class="form-group">
                    <label>Date de fin</label>
                    <input
                        type="date"
                        name="date_fin"
                        required
                        value="<?= htmlspecialchars((string)$offre['date_fin']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Rémunération mensuelle (€)</label>
                <input
                    type="number"
                    step="0.01"
                    name="remuneration"
                    required
                    value="<?= htmlspecialchars((string)$offre['remuneration']) ?>">
            </div>

            <div class="form-actions">
    <button type="submit" name="action" value="enregistrer_modification">
        Enregistrer
    </button>

    <?php if ($offre['statut_offre'] === 'BROUILLON'): ?>
        <button type="submit" name="action" value="soumettre_modification">
            Soumettre pour validation
        </button>
    <?php endif; ?>

    <a href="<?= $base ?>/entreprise/">Annuler</a>
</div>


        </form>

    </div>
</div>

</body>
</html>
