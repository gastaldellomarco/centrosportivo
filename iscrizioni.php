<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Iscrizioni';
include __DIR__ . '/header.php';

function tableExists(PDO $pdo, string $table): bool {
  try {
    $res = $pdo->query("SHOW TABLES LIKE '" . str_replace('`','',$table) . "'")->fetchAll();
    return count($res) > 0;
  } catch (PDOException $e) {
    return false;
  }
}

$rows = [];
if (tableExists($pdo, 'iscrizioni')) {
  try {
    $order = '';
    try {
      $cols = $pdo->query("SHOW COLUMNS FROM `iscrizioni`")->fetchAll(PDO::FETCH_COLUMN,0);
      if (in_array('data_iscrizione', $cols, true)) $order = ' ORDER BY `data_iscrizione` DESC';
      elseif (in_array('id', $cols, true)) $order = ' ORDER BY `id` DESC';
    } catch (Exception $_) { }
    $stmt = $pdo->query("SELECT * FROM `iscrizioni`" . $order . " LIMIT 500");
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    $rows = [];
  }
} else {
  $rows = null; // segnala tabella mancante
}
?>

<section class="card">
  <div class="card-header">
    <div class="card-title">Iscrizioni</div>
    <div>
      <a href="#" class="btn btn-primary btn-sm">Nuova iscrizione</a>
    </div>
  </div>

  <div class="table-wrap">
    <?php if ($rows === null): ?>
      <div class="alert alert-info">La tabella <code>iscrizioni</code> non esiste nel database.
        <div style="margin-top:.6rem">
          <a href="migrate_create_core_tables.php" class="btn btn-primary btn-sm">Crea tabelle core e dati di esempio</a>
        </div>
      </div>
    <?php elseif (empty($rows)): ?>
      <div class="alert alert-info">Nessuna iscrizione trovata (tabella <code>iscrizioni</code> vuota).</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <?php foreach (array_keys($rows[0]) as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <?php foreach ($r as $val): ?>
              <td><?= htmlspecialchars((string)$val) ?></td>
            <?php endforeach; ?>
            <td><a class="btn btn-outline btn-sm" href="#">Dettagli</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php';
