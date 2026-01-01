<?php
$base = '/public';
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<h2>Créer un étudiant</h2>

<?php if ($message): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post" action="<?= $base ?>/secretaire/">
    <input type="hidden" name="action" value="creer_etudiant">

    <input name="nom" placeholder="Nom" required>
    <input name="prenom" placeholder="Prénom" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="formation" placeholder="Formation" required>

    <button type="submit">Créer l’étudiant</button>
</form>
