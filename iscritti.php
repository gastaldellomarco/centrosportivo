<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Iscritti';
include __DIR__ . '/header.php';

function tableExists(PDO $pdo, string $table): bool {
  try {
    $res = $pdo->query("SHOW TABLES LIKE '" . str_replace('`','',$table) . "'")->fetchAll();
    return count($res) > 0;
  } catch (PDOException $e) {
    return false;
  }
}

function findIdColumn(PDO $pdo, string $table): ?string {
  try {
    $cols = $pdo->query("SHOW COLUMNS FROM `" . str_replace('`','',$table) . "`")->fetchAll(PDO::FETCH_COLUMN,0);
    $candidates = ['id','idIscritto','id_iscritto','iscritto_id','iscrittoid'];
    foreach ($candidates as $c) if (in_array($c, $cols, true)) return $c;
    // fallback: first numeric-like column name
    foreach ($cols as $c) {
      if (stripos($c, 'id') !== false) return $c;
    }
    return $cols[0] ?? null;
  } catch (Exception $e) {
    return null;
  }
}

$rows = [];
if (tableExists($pdo, 'iscritti')) {
  try {
    // scegli un ordine sicuro: preferisci created_at, poi id
    $order = '';
    try {
      $cols = $pdo->query("SHOW COLUMNS FROM `iscritti`")->fetchAll(PDO::FETCH_COLUMN,0);
      if (in_array('created_at', $cols, true)) $order = ' ORDER BY `created_at` DESC';
      elseif (in_array('id', $cols, true)) $order = ' ORDER BY `id` DESC';
    } catch (Exception $_) { /* ignore */ }
    $stmt = $pdo->query("SELECT * FROM `iscritti`" . $order . " LIMIT 500");
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
      <div class="card-title">Iscritti</div>
      <div>
        <a href="iscritti_new.php" class="btn btn-primary btn-sm">Nuovo iscritto</a>
      </div>
    </div>

  <div class="table-wrap">
    <?php if ($rows === null): ?>
      <div class="alert alert-info">La tabella <code>iscritti</code> non esiste nel database.
        <div style="margin-top:.6rem">
          <a href="migrate_create_core_tables.php" class="btn btn-primary btn-sm">Crea tabelle core e dati di esempio</a>
        </div>
      </div>
    <?php elseif (empty($rows)): ?>
      <div class="alert alert-info">Nessun iscritto trovato (tabella <code>iscritti</code> vuota).</div>
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
          <?php
            $idCol = findIdColumn($pdo, 'iscritti');
            foreach ($rows as $r): ?>
          <tr>
            <?php foreach ($r as $val): ?>
              <td><?= htmlspecialchars((string)$val) ?></td>
            <?php endforeach; ?>
            <td>
              <?php $idVal = $idCol && isset($r[$idCol]) ? $r[$idCol] : '';?>
              <a class="btn btn-outline btn-sm" href="iscritti_edit.php?id=<?= urlencode($idVal) ?>">Modifica</a>
              <a class="btn btn-danger btn-sm" href="iscritti_delete.php?id=<?= urlencode($idVal) ?>">Elimina</a>
              <a class="btn btn-outline btn-sm" href="iscritti_view.php?id=<?= urlencode($idVal) ?>">Dettagli</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php';
