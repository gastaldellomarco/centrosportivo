<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Modifica Iscritto';
include __DIR__ . '/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { setFlash('error','ID mancante'); header('Location: iscritti.php'); exit; }

// carica record (tenta con id o idIscritto)
try {
    $stmt = $pdo->prepare("SELECT * FROM `iscritti` WHERE id = ? OR idIscritto = ? LIMIT 1");
    $stmt->execute([$id,$id]);
    $row = $stmt->fetch();
} catch (Exception $e) { $row = false; }
if (!$row) { setFlash('error','Iscritto non trovato'); header('Location: iscritti.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nome === '') $errors[] = 'Nome richiesto.';
    if ($email === '') $errors[] = 'Email richiesta.';
    if (empty($errors)) {
        // prova a aggiornare colonne comuni
        $update = $pdo->prepare("UPDATE `iscritti` SET nome = ?, cognome = ?, email = ? WHERE id = ? OR idIscritto = ?");
        $update->execute([$nome,$cognome,$email,$id,$id]);
        setFlash('success','Iscritto aggiornato.');
        header('Location: iscritti.php'); exit;
    }
}
?>
<section class="card">
  <div class="card-header"><div class="card-title">Modifica Iscritto</div></div>
  <?php if ($errors): ?><div class="alert alert-error"><?= htmlspecialchars(implode('\n',$errors)) ?></div><?php endif; ?>
  <form method="post" class="form-grid">
    <div class="form-group"><label>Nome</label><input name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? $row['nome'] ?? '') ?>"></div>
    <div class="form-group"><label>Cognome</label><input name="cognome" value="<?= htmlspecialchars($_POST['cognome'] ?? $row['cognome'] ?? '') ?>"></div>
    <div class="form-group full"><label>Email</label><input name="email" value="<?= htmlspecialchars($_POST['email'] ?? $row['email'] ?? '') ?>"></div>
    <div class="form-actions"><button class="btn btn-primary">Salva</button><a href="iscritti.php" class="btn btn-outline">Annulla</a></div>
  </form>
</section>
<?php include __DIR__ . '/footer.php';
