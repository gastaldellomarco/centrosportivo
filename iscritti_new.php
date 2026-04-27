<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Nuovo Iscritto';
include __DIR__ . '/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nome === '') $errors[] = 'Nome richiesto.';
    if ($email === '') $errors[] = 'Email richiesta.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO `iscritti` (nome,cognome,email,created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome,$cognome,$email,date('Y-m-d H:i:s')]);
        setFlash('success','Iscritto creato.');
        header('Location: iscritti.php'); exit;
    }
}
?>
<section class="card">
  <div class="card-header"><div class="card-title">Nuovo Iscritto</div></div>
  <?php if ($errors): ?>
    <div class="alert alert-error"><?= htmlspecialchars(implode('\n', $errors)) ?></div>
  <?php endif; ?>
  <form method="post" class="form-grid">
    <div class="form-group"><label>Nome</label><input name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"></div>
    <div class="form-group"><label>Cognome</label><input name="cognome" value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>"></div>
    <div class="form-group full"><label>Email</label><input name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
    <div class="form-actions"><button class="btn btn-primary">Crea</button><a href="iscritti.php" class="btn btn-outline">Annulla</a></div>
  </form>
</section>
<?php include __DIR__ . '/footer.php';
