<?php
require_once __DIR__ . '/../../models/Secretaire.php';
$model = new Secretaire();
$attestations = $model->getAttestationsEnAttente();
$base = '/public';
?>

<h2>Attestations RC</h2>

<?php foreach ($attestations as $a): ?>
<div>
    <?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?>
    <form method="post" action="<?= $base ?>/secretaire/">
        <input type="hidden" name="id_attestation" value="<?= $a['idattestation_rc'] ?>">
        <button name="action" value="valider_attestation">Valider</button>
        <button name="action" value="refuser_attestation">Refuser</button>
    </form>
</div>
<?php endforeach; ?>
