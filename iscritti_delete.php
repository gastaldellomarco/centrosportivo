<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Elimina Iscritto';
include __DIR__ . '/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { setFlash('error','ID mancante'); header('Location: iscritti.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM `iscritti` WHERE id = ? OR idIscritto = ?");
        $stmt->execute([$id,$id]);
        setFlash('success','Iscritto eliminato.');
    } catch (Exception $e) {
        setFlash('error','Errore eliminazione: ' . $e->getMessage());
    }
    header('Location: iscritti.php'); exit;
}

?>
<section class="card">
  <div class="card-header"><div class="card-title">Conferma eliminazione</div></div>
  <div class="card">Sei sicuro di voler eliminare l'iscritto con ID <?= htmlspecialchars($id) ?>?</div>
  <form method="post" style="margin-top:1rem">
    <button class="btn btn-danger">Elimina definitivamente</button>
    <a href="iscritti.php" class="btn btn-outline">Annulla</a>
  </form>
</section>
<?php include __DIR__ . '/footer.php';
