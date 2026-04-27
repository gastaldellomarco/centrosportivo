<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Analisi';
include __DIR__ . '/header.php';

// Prepara dataset per i grafici
$tableMissing = false;
$columnMissing = false;
$iscrittiPerMese = [];
$iscrizioniByCorso = [];
$accessiPerGiorno = [];
try {
  $tExists = (bool)$pdo->query("SHOW TABLES LIKE 'iscritti'")->fetch();
  if (!$tExists) { $tableMissing = true; }
  else {
    // iscritti per mese (ultimi 12 mesi)
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c FROM iscritti GROUP BY ym ORDER BY ym DESC LIMIT 12");
    $iscrittiPerMese = $stmt->fetchAll();
  }
} catch (PDOException $e) {
  $msg = $e->getMessage();
  if (stripos($msg, 'Unknown column') !== false) $columnMissing = true;
}

// iscrizioni per corso (se tabella iscrizioni esiste)
try {
  if ($pdo->query("SHOW TABLES LIKE 'iscrizioni'")->fetch()) {
    $stmt = $pdo->query("SELECT corso_id, COUNT(*) as c FROM iscrizioni GROUP BY corso_id ORDER BY c DESC LIMIT 12");
    $iscrizioniByCorso = $stmt->fetchAll();
  }
} catch (Exception $_) { }

// accessi per giorno (ultimi 14 giorni)
try {
  if ($pdo->query("SHOW TABLES LIKE 'accessi'")->fetch()) {
    $stmt = $pdo->query("SELECT DATE(access_time) as d, COUNT(*) as c FROM accessi WHERE access_time >= DATE_SUB(NOW(), INTERVAL 14 DAY) GROUP BY d ORDER BY d ASC");
    $accessiPerGiorno = $stmt->fetchAll();
  }
} catch (Exception $_) { }

?>

<section class="card">
  <div class="card-header">
    <div class="card-title">Analisi rapido</div>
    <div>
      <a href="#" class="btn btn-outline btn-sm">Esporta</a>
    </div>
  </div>

  <?php if (empty($iscrittiPerMese)): ?>
    <?php if ($tableMissing): ?>
      <div class="alert alert-info">La tabella <code>iscritti</code> non esiste. Importa il DB o crea la tabella corretta (vedi <code>db.sql</code>).</div>
    <?php elseif ($columnMissing): ?>
      <div class="alert alert-info">
        La tabella <code>iscritti</code> non contiene la colonna <code>created_at</code> necessaria per le analisi.
        <div style="margin-top:.6rem">
          <a href="migrate_add_created_at.php" class="btn btn-primary btn-sm">Aggiungi colonna created_at (migrazione)</a>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">Nessun dato per le analisi: la tabella <code>iscritti</code> potrebbe essere vuota.</div>
    <?php endif; ?>
  <?php else: ?>
    <div class="card">
      <div class="card-header"><div class="card-title">Iscritti ultimi 12 mesi</div></div>
      <div style="margin-top:0.75rem;">
        <canvas id="chartIscritti" height="120"></canvas>
      </div>
    </div>
    <div class="grid-2" style="margin-top:1rem">
      <div class="card">
        <div class="card-header"><div class="card-title">Iscrizioni per corso</div></div>
        <div style="margin-top:0.75rem;"><canvas id="chartIscrizioni"></canvas></div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title">Accessi ultimi 14 giorni</div></div>
        <div style="margin-top:0.75rem;"><canvas id="chartAccessi"></canvas></div>
      </div>
    </div>
  <?php endif; ?>

</section>

<?php include __DIR__ . '/footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const iscrittiData = <?php echo json_encode(array_reverse(array_map(function($r){ return [$r['ym'],$r['c']]; }, $iscrittiPerMese))); ?>;
const iscrittiLabels = iscrittiData.map(r=>r[0]);
const iscrittiCounts = iscrittiData.map(r=>+r[1]);

const ctxI = document.getElementById('chartIscritti').getContext('2d');
new Chart(ctxI, { type: 'line', data: { labels: iscrittiLabels, datasets: [{ label: 'Iscritti', data: iscrittiCounts, borderColor: '#d95f1a', backgroundColor: 'rgba(217,95,26,0.08)', fill:true }] }, options: { responsive:true } });

const iscrizioniData = <?php echo json_encode($iscrizioniByCorso); ?>;
const iscrLabels = iscrizioniData.map(r=> 'Corso ' + (r['corso_id'] ?? 'ID'));
const iscrCounts = iscrizioniData.map(r=>+r['c']);
const ctxC = document.getElementById('chartIscrizioni').getContext('2d');
new Chart(ctxC, { type: 'bar', data: { labels: iscrLabels, datasets: [{ label: 'Iscrizioni', data: iscrCounts, backgroundColor: '#52b788' }] }, options: { responsive:true } });

const accessiData = <?php echo json_encode($accessiPerGiorno); ?>;
const accLabels = accessiData.map(r=>r['d']);
const accCounts = accessiData.map(r=>+r['c']);
const ctxA = document.getElementById('chartAccessi').getContext('2d');
new Chart(ctxA, { type: 'line', data: { labels: accLabels, datasets: [{ label: 'Accessi', data: accCounts, borderColor: '#2d6a4f', backgroundColor: 'rgba(45,106,79,0.08)', fill:true }] }, options: { responsive:true } });
</script>
