<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Accessi';
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
if (tableExists($pdo, 'accessi')) {
  try {
    $order = '';
    try {
      $cols = $pdo->query("SHOW COLUMNS FROM `accessi`")->fetchAll(PDO::FETCH_COLUMN,0);
      if (in_array('access_time', $cols, true)) $order = ' ORDER BY `access_time` DESC';
      elseif (in_array('id', $cols, true)) $order = ' ORDER BY `id` DESC';
    } catch (Exception $_) { }
    $stmt = $pdo->query("SELECT * FROM `accessi`" . $order . " LIMIT 500");
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
    <div class="card-title">Accessi</div>
    <div>
      <a href="#" class="btn btn-outline btn-sm">Filtri</a>
    </div>
  </div>

  <div class="table-wrap">
    <?php if ($rows === null): ?>
      <div class="alert alert-info">La tabella <code>accessi</code> non esiste nel database.
        <div style="margin-top:.6rem">
          <a href="migrate_create_core_tables.php" class="btn btn-primary btn-sm">Crea tabelle core e dati di esempio</a>
        </div>
      </div>
    <?php elseif (empty($rows)): ?>
      <div class="alert alert-info">Nessun accesso registrato (tabella <code>accessi</code> vuota).</div>
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
