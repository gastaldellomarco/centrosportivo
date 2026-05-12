<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Analisi Centri – Tipologia Corso';
include __DIR__ . '/header.php';

// ─── Query: categoria con più accessi per ogni centro ────────────────────────
// Logica: JOIN ACCESSI → ISCRIZIONI_CORSI → CORSI → CENTRI
// Raggruppo per centro+categoria, poi prendo il MAX con subquery
$stmtCrossincrociata = $pdo->query("
    SELECT
        ce.idCentro,
        ce.nomeCentro,
        ce.tipologia AS tipologiaCentro,
        c.categoria,
        COUNT(a.idAccesso) AS totale_accessi,
        COUNT(DISTINCT ic.idIscritto) AS iscritti_distinti
    FROM ACCESSI a
    JOIN ISCRIZIONI_CORSI ic ON ic.idIscritto = a.idIscritto
    JOIN CORSI c ON c.idCorso = ic.idCorso
    JOIN CENTRI ce ON ce.idCentro = c.idCentro
    GROUP BY ce.idCentro, ce.nomeCentro, ce.tipologiaCentro, c.categoria
    ORDER BY ce.idCentro, totale_accessi DESC
");
$righe = $stmtCrossincrociata->fetchAll();

// Raggruppa per centro
$perCentro = [];
foreach ($righe as $r) {
    $perCentro[$r['idCentro']]['nome'] = $r['nomeCentro'];
    $perCentro[$r['idCentro']]['tipologia'] = $r['tipologiaCentro'];
    $perCentro[$r['idCentro']]['categorie'][] = $r;
}

// ─── Query: top categoria per ogni centro (ottimizzazione pianificazione) ────
$stmtTop = $pdo->query("
    SELECT
        sub.idCentro,
        sub.nomeCentro,
        sub.categoria AS categoria_top,
        sub.totale_accessi AS accessi_top
    FROM (
        SELECT
            ce.idCentro,
            ce.nomeCentro,
            c.categoria,
            COUNT(a.idAccesso) AS totale_accessi,
            RANK() OVER (PARTITION BY ce.idCentro ORDER BY COUNT(a.idAccesso) DESC) AS rnk
        FROM ACCESSI a
        JOIN ISCRIZIONI_CORSI ic ON ic.idIscritto = a.idIscritto
        JOIN CORSI c ON c.idCorso = ic.idCorso
        JOIN CENTRI ce ON ce.idCentro = c.idCentro
        GROUP BY ce.idCentro, ce.nomeCentro, c.categoria
    ) sub
    WHERE sub.rnk = 1
    ORDER BY accessi_top DESC
");
$topPerCentro = $stmtTop->fetchAll();

// Preparo dati per Chart.js
$chartLabels = [];
$chartData   = [];
$chartCat    = [];
foreach ($topPerCentro as $t) {
    $chartLabels[] = $t['nomeCentro'];
    $chartData[]   = (int)$t['accessi_top'];
    $chartCat[]    = $t['categoria_top'];
}
?>

<section class="card">
  <div class="card-header">
    <div class="card-title">Analisi Incrociata Centri × Tipologia Corso</div>
    <span style="font-size:.82rem;color:#666">Ottimizzazione pianificazione spazi</span>
  </div>

  <!-- Card riepilogo top categoria per centro -->
  <div class="grid-2" style="margin-bottom:1.5rem">
    <?php foreach ($topPerCentro as $t): ?>
    <div class="card" style="border-left:3px solid var(--primary,#2d6a4f)">
      <div style="font-size:.78rem;color:#888;text-transform:uppercase;letter-spacing:.05em"><?= htmlspecialchars($t['nomeCentro']) ?></div>
      <div style="font-size:1.1rem;font-weight:600;margin:.25rem 0"><?= htmlspecialchars($t['categoria_top']) ?></div>
      <div style="font-size:.85rem;color:#555"><?= $t['accessi_top'] ?> accessi registrati</div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Grafico a barre: top categoria accessi per centro -->
  <div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><div class="card-title">Accessi per categoria dominante – per centro</div></div>
    <canvas id="chartTopCategoria" height="100"></canvas>
  </div>

  <!-- Tabella dettaglio completo -->
  <div class="card-header" style="margin-top:1.5rem"><div class="card-title">Dettaglio completo per centro</div></div>
  <?php foreach ($perCentro as $centroId => $data): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <div class="card-title"><?= htmlspecialchars($data['nome']) ?></div>
      <span class="badge"><?= htmlspecialchars($data['tipologia']) ?></span>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Categoria Corso</th><th>Totale Accessi</th><th>Iscritti Distinti</th><th>Quota</th></tr>
        </thead>
        <tbody>
          <?php
          $totCentro = array_sum(array_column($data['categorie'], 'totale_accessi'));
          foreach ($data['categorie'] as $idx => $cat):
            $quota = $totCentro > 0 ? round($cat['totale_accessi'] / $totCentro * 100, 1) : 0;
          ?>
          <tr style="<?= $idx === 0 ? 'font-weight:600;background:rgba(45,106,79,.05)' : '' ?>">
            <td>
              <?php if ($idx === 0): ?><span style="color:var(--primary,#2d6a4f)">★ </span><?php endif; ?>
              <?= htmlspecialchars($cat['categoria']) ?>
            </td>
            <td><?= $cat['totale_accessi'] ?></td>
            <td><?= $cat['iscritti_distinti'] ?></td>
            <td>
              <div class="progress-bar-wrap">
                <div class="progress-bar" style="width:<?= $quota ?>%;background:var(--primary,#2d6a4f)"><?= $quota ?>%</div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

  <div class="alert alert-info" style="margin-top:1rem">
    <strong>💡 Come usare questi dati:</strong> La categoria con ★ è quella che genera più accessi in ogni centro.
    Considera di aumentare il numero di corsi o di ampliare gli spazi dedicati a quella categoria per ottimizzare la pianificazione.
  </div>
</section>

<style>
.progress-bar-wrap { background:#e9ecef; border-radius:4px; height:18px; min-width:80px; overflow:hidden; }
.progress-bar { height:100%; border-radius:4px; font-size:.75rem; color:#fff; text-align:center; line-height:18px; transition:width .3s; white-space:nowrap; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($chartLabels) ?>;
const data   = <?= json_encode($chartData) ?>;
const cats   = <?= json_encode($chartCat) ?>;

const palette = [
    '#2d6a4f','#52b788','#d95f1a','#e9c46a','#264653','#e76f51','#a8dadc','#457b9d'
];

new Chart(document.getElementById('chartTopCategoria').getContext('2d'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Accessi categoria top',
            data: data,
            backgroundColor: palette.slice(0, labels.length),
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        return 'Categoria: ' + cats[context.dataIndex];
                    }
                }
            },
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
