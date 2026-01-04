<?php
if (!isset($_SESSION)) {
    session_start();
}

/**
 * Vue : Affectations à valider
 * Rôle : Enseignant
 * Affichage uniquement
 */

require_once __DIR__ . '/../../models/Enseignant.php';

$base = '/public';
$model = new Enseignant();
$affectations = $model->getAffectationsAValider($_SESSION['user']['idutilisateur']);
$urgentes = $model->getAffectationsUrgentes();

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDMC CAREER CENTER - Validation des affectations</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/enseignant.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="navbar-left">
        <h1>IDMC CAREER CENTER</h1>
        <p>Espace Enseignant Responsable</p>
    </div>

    <div class="navbar-right">
        <a href="<?= $base ?>/enseignant/">Accueil</a>
        
        <a href="<?= $base ?>/logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
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


    <div class="section">
        <h3>Affectations en attente de validation</h3>

        <?php if (empty($affectations)): ?>
            <p>Aucune affectation à valider.</p>
        <?php else: ?>
            <?php foreach ($affectations as $a): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h4>Affectation proposée</h4>
                        <span class="status status-awaiting">En attente</span>
                    </div>

                    <div class="assignment-details">
                        <div class="assignment-column">
                            <h5>Étudiant</h5>
                         <p><strong>Nom :</strong> <?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></p>
<p><strong>Email :</strong> <?= htmlspecialchars($a['email']) ?></p>

                        </div>

                        <div class="assignment-column">
                            <h5>Offre</h5>
                            <p><strong>Poste :</strong> <?= htmlspecialchars($a['offre']) ?></p>
                            <p><strong>Entreprise :</strong> <?= htmlspecialchars($a['entreprise']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars($a['type_contrat']) ?></p>
                        </div>
                    </div>

                   <div class="validation-actions">
    <form method="POST" action="<?= $base ?>/enseignant/">
        <input type="hidden" name="idcandidature" value="<?= (int)$a['idcandidature'] ?>">

        <button class="btn-action" name="action" value="valider_affectation">
            Valider l'affectation
        </button>

        <button class="btn-danger" name="action" value="rejeter_affectation">
            Refuser l'affectation
        </button>
    </form>
</div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
<?php if (!empty($urgentes)): ?>
<h3 style="margin-top:40px">🚨 Affectations urgentes</h3>

<?php foreach ($urgentes as $a): ?>
<div class="assignment-card urgent">
    <div class="assignment-header">
        <h4><?= htmlspecialchars($a['offre']) ?></h4>
        <span class="status status-urgent">URGENT</span>
    </div>

    <p><strong>Étudiant :</strong> <?= htmlspecialchars($a['etudiant']) ?></p>
    <p><strong>Entreprise :</strong> <?= htmlspecialchars($a['entreprise']) ?></p>

    <form method="POST" action="<?= $base ?>/enseignant/">
        <input type="hidden" name="idcandidature" value="<?= (int)$a['idcandidature'] ?>">
        <button class="btn-action" name="action" value="valider_affectation_urgente">
            Valider en urgence
        </button>
    </form>
</div>
<?php endforeach; ?>
<?php endif; ?>


</body>
</html>
