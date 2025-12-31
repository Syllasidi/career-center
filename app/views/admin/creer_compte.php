<?php
// ================================
// Vue : Création compte interne
// Logique IDENTIQUE au projet Atelier
// ================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Admin.php';

$pdo = Database::getConnection();
$adminModel = new Admin($pdo);

$base = '/public';

// Message retour éventuel
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte interne</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body>

<nav class="navbar">
    <a href="<?= $base ?>/admin/">Accueil</a>
    <a href="<?= $base ?>/admin/?page=Comptes">Gestion des comptes</a>
</nav>

<div class="container">

    <h2>Créer un compte interne</h2>

    <?php if ($message): ?>
        <div class="info-notice"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $base ?>/admin/">
    <input type="hidden" name="action" value="creer_compte_interne">


    <div class="form-group">
        <label>Nom</label>
        <input type="text" name="nom" required>
    </div>

    <div class="form-group">
        <label>Prénom</label>
        <input type="text" name="prenom" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>

    <div class="form-group">
        <label>Rôle</label>
        <select name="role" required>
            <option value="">-- Choisir un rôle --</option>
            <option value="ENSEIGNANT">Enseignant</option>
            <option value="SECRETAIRE">Secrétaire</option>
        </select>
    </div>

    <button type="submit" class="btn-primary">
        Créer le compte
    </button>
</form>


</div>

</body>
</html>
