<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Dettaglio Iscritto';
include __DIR__ . '/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { setFlash('error','ID mancante'); header('Location: iscritti.php'); exit; }
try {
    $stmt = $pdo->prepare("SELECT * FROM `iscritti` WHERE id = ? OR idIscritto = ? LIMIT 1");
    $stmt->execute([$id,$id]);
    $row = $stmt->fetch();
} catch (Exception $e) { $row = false; }
if (!$row) { setFlash('error','Iscritto non trovato'); header('Location: iscritti.php'); exit; }
?>
<section class="card">
  <div class="card-header"><div class="card-title">Dettaglio: <?= htmlspecialchars($row['nome'] . ' ' . ($row['cognome'] ?? '')) ?></div></div>
  <div class="card">
    <table>
      <tbody>
        <?php foreach ($row as $k=>$v): ?>
        <tr><th><?= htmlspecialchars($k) ?></th><td><?= htmlspecialchars((string)$v) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="form-actions" style="margin-top:1rem">
      <a class="btn btn-outline" href="iscritti_edit.php?id=<?= urlencode($id) ?>">Modifica</a>
      <a class="btn btn-outline" href="iscritti.php">Torna</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/footer.php';
