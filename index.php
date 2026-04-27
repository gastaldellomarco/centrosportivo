<?php
// index.php - Dashboard principale
require_once __DIR__ . '/config.php';

$pageTitle = 'Dashboard';
include __DIR__ . '/header.php';

// Helper: conta righe in una tabella, silenzia errori se la tabella non esiste
function tableCount(PDO $pdo, string $table): int {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `" . str_replace('`', '', $table) . "`");
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Statistiche principali (nomi tabella basati sulla navigazione esistente)
$stats = [
    ['label' => 'Centri',      'table' => 'centri',     'class' => 'orange'],
    ['label' => 'Corsi',       'table' => 'corsi',      'class' => 'green'],
    ['label' => 'Iscritti',    'table' => 'iscritti',   'class' => 'amber'],
    ['label' => 'Iscrizioni',  'table' => 'iscrizioni', 'class' => 'red'],
    ['label' => 'Accessi',     'table' => 'accessi',    'class' => 'green'],
];

foreach ($stats as &$s) {
    $s['count'] = tableCount($pdo, $s['table']);
}
unset($s);

// Carica gli ultimi iscritti (se la tabella esiste) usando un ordine sicuro
$recentIscritti = [];
try {
  $tableCheck = $pdo->query("SHOW TABLES LIKE 'iscritti'")->fetch();
  if ($tableCheck) {
    // determina un ordine sicuro: preferisci created_at, poi id
    $order = '';
    try {
      $cols = $pdo->query("SHOW COLUMNS FROM `iscritti`")->fetchAll(PDO::FETCH_COLUMN,0);
      if (in_array('created_at', $cols, true)) $order = ' ORDER BY `created_at` DESC';
      elseif (in_array('id', $cols, true)) $order = ' ORDER BY `id` DESC';
    } catch (Exception $_) { /* ignore */ }

    $stmt = $pdo->query("SELECT * FROM `iscritti`" . $order . " LIMIT 8");
    $recentIscritti = $stmt->fetchAll();
  } else {
    $recentIscritti = null; // tabella mancante
  }
} catch (PDOException $e) {
  $recentIscritti = [];
}

?>

  <section class="card">
    <div class="card-header">
      <div class="card-title">Panoramica</div>
      <div>
        <a href="centri.php" class="btn btn-outline btn-sm">Gestisci Centri</a>
      </div>
    </div>

    <div class="stats-grid">
      <?php foreach ($stats as $s): ?>
      <div class="stat-card <?= htmlspecialchars($s['class']) ?>">
        <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
        <div class="stat-value"><?= number_format($s['count']) ?></div>
        <div class="stat-sub">Tabella: <?= htmlspecialchars($s['table']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card" style="margin-top:1rem;">
    <div class="card-header">
      <div class="card-title">Ultimi iscritti</div>
      <div>
        <a href="iscritti.php" class="btn btn-primary btn-sm">Vedi tutti</a>
      </div>
    </div>

    <div class="table-wrap">
      <?php if (count($recentIscritti) === 0): ?>
        <div class="alert alert-info">Nessun iscritto trovato (tabella <code>iscritti</code> vuota o non presente).</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Data</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentIscritti as $row): ?>
                <?php
                  // colonne comuni: nome, cognome, email, created_at
                  $nome = $row['nome'] ?? ($row['name'] ?? '—');
                  $cognome = $row['cognome'] ?? ($row['surname'] ?? '');
                  $email = $row['email'] ?? ($row['mail'] ?? '—');
                  $created = $row['created_at'] ?? ($row['created'] ?? ($row['data'] ?? '—'));

                  // trova un campo ID ragionevole: id, idIscritto, id_iscritto, iscritto_id, first numeric
                  $possibleId = '—';
                  $candidates = ['id','idIscritto','id_iscritto','iscritto_id','iscrittoid'];
                  foreach ($candidates as $c) {
                      if (isset($row[$c]) && $row[$c] !== '') { $possibleId = $row[$c]; break; }
                  }
                  if ($possibleId === '—') {
                      // cerca la prima colonna che sembra un intero
                      foreach ($row as $v) {
                          if (is_numeric($v) && (string)((int)$v) === (string)$v) { $possibleId = $v; break; }
                      }
                  }
                ?>
              <tr>
                <td><?= htmlspecialchars((string)$possibleId) ?></td>
                <td><?= htmlspecialchars(trim($nome . ' ' . $cognome)) ?></td>
                <td><?= htmlspecialchars($email) ?></td>
                <td><?= htmlspecialchars($created) ?></td>
                <td><a href="iscritti.php" class="btn btn-outline btn-sm">Dettagli</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </section>

<?php
include __DIR__ . '/footer.php';
