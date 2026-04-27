<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Corsi';
include __DIR__ . '/header.php';

function fetchRows(PDO $pdo, string $table, int $limit = 200): array {
    try {
        $stmt = $pdo->query("SELECT * FROM `" . str_replace('`','',$table) . "` LIMIT " . (int)$limit);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

$rows = fetchRows($pdo, 'corsi', 200);
?>

<section class="card">
  <div class="card-header">
    <div class="card-title">Elenco Corsi</div>
    <div>
      <a href="#" class="btn btn-primary btn-sm">Nuovo corso</a>
    </div>
  </div>

  <div class="table-wrap">
    <?php if (empty($rows)): ?>
      <div class="alert alert-info">Nessun corso trovato (tabella <code>corsi</code> vuota o non presente).</div>
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
            <td><a class="btn btn-outline btn-sm" href="#">Modifica</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php';
