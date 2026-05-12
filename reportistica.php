<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Reportistica Temporale';
include __DIR__ . '/header.php';

// ─── PARTE 6A: Fasce orarie di picco ─────────────────────────────────────────
// Mattina: 06:00-12:59 | Pomeriggio: 13:00-17:59 | Sera: 18:00-23:59 | Notte: 00:00-05:59
$stmtFasce = $pdo->query("
    SELECT
        CASE
            WHEN HOUR(dataOraIngresso) BETWEEN 6  AND 12 THEN 'Mattina (06-12)'
            WHEN HOUR(dataOraIngresso) BETWEEN 13 AND 17 THEN 'Pomeriggio (13-17)'
            WHEN HOUR(dataOraIngresso) BETWEEN 18 AND 22 THEN 'Sera (18-22)'
            ELSE                                              'Fuori orario (23-05)'
        END AS fascia_oraria,
        COUNT(*) AS totale_accessi,
        ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 1) AS percentuale
    FROM ACCESSI
    GROUP BY fascia_oraria
    ORDER BY FIELD(fascia_oraria,
        'Mattina (06-12)',
        'Pomeriggio (13-17)',
        'Sera (18-22)',
        'Fuori orario (23-05)')
");
$fasce = $stmtFasce->fetchAll();

// Dettaglio ora per ora (istogramma orario)
$stmtOre = $pdo->query("
    SELECT
        HOUR(dataOraIngresso) AS ora,
        COUNT(*)              AS accessi
    FROM ACCESSI
    GROUP BY ora
    ORDER BY ora
");
$accessiPerOra = $stmtOre->fetchAll();
// Riempie tutte le 24 ore anche quelle con 0 accessi
$accessiPerOraMap = array_fill(0, 24, 0);
foreach ($accessiPerOra as $r) {
    $accessiPerOraMap[(int)$r['ora']] = (int)$r['accessi'];
}

// ─── PARTE 6B: Andamento mensile iscrizioni ───────────────────────────────────
// Anno corrente
$annoCorrente = date('Y');
$stmtMensile = $pdo->prepare("
    SELECT
        MONTH(dataInizio)     AS mese_num,
        MONTHNAME(dataInizio) AS mese_nome,
        COUNT(*)              AS nuove_iscrizioni
    FROM ISCRIZIONI_CORSI
    WHERE YEAR(dataInizio) = ?
    GROUP BY mese_num, mese_nome
    ORDER BY mese_num
");
$stmtMensile->execute([$annoCorrente]);
$mensile = $stmtMensile->fetchAll();

// Mappa mese (1-12) -> conteggio, con 0 per mesi senza iscrizioni
$mesiLabels = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
$mesiData   = array_fill(0, 12, 0);
foreach ($mensile as $r) {
    $mesiData[(int)$r['mese_num'] - 1] = (int)$r['nuove_iscrizioni'];
}

// Statistiche mese
$totAnno     = array_sum($mesiData);
$mesePicco   = array_search(max($mesiData), $mesiData);
$mediaPerMese = $totAnno > 0 ? round($totAnno / max(1, count(array_filter($mesiData))), 1) : 0;
?>

<!-- ═══ SEZIONE FASCE ORARIE ═══════════════════════════════════════════════ -->
<section class="card" style="margin-bottom:1.5rem">
  <div class="card-header">
    <div class="card-title">📊 Fasce Orarie di Picco</div>
    <span style="font-size:.82rem;color:#666">Distribuzione accessi per fascia temporale</span>
  </div>

  <!-- KPI cards fasce -->
  <div class="grid-3" style="margin:1rem 0">
    <?php
    $fasceColors = [
        'Mattina (06-12)'       => ['icon'=>'🌅','color'=>'#e9c46a'],
        'Pomeriggio (13-17)'    => ['icon'=>'☀️','color'=>'#d95f1a'],
        'Sera (18-22)'          => ['icon'=>'🌆','color'=>'#264653'],
        'Fuori orario (23-05)'  => ['icon'=>'🌙','color'=>'#adb5bd'],
    ];
    $piccoFascia = null;
    $piccoVal = 0;
    foreach ($fasce as $f) { if ($f['totale_accessi'] > $piccoVal) { $piccoVal = $f['totale_accessi']; $piccoFascia = $f['fascia_oraria']; } }
    foreach ($fasce as $f):
        $meta = $fasceColors[$f['fascia_oraria']] ?? ['icon'=>'⏰','color'=>'#888'];
    ?>
    <div class="card" style="border-top:3px solid <?= $meta['color'] ?>;<?= $f['fascia_oraria'] === $piccoFascia ? 'box-shadow:0 2px 12px rgba(0,0,0,.12)' : '' ?>">
      <div style="font-size:1.5rem"><?= $meta['icon'] ?></div>
      <div style="font-size:.82rem;color:#888;margin:.25rem 0"><?= htmlspecialchars($f['fascia_oraria']) ?><?= $f['fascia_oraria'] === $piccoFascia ? ' <span class="badge badge-warning">PICCO</span>' : '' ?></div>
      <div style="font-size:1.6rem;font-weight:700"><?= $f['totale_accessi'] ?></div>
      <div style="font-size:.82rem;color:#888"><?= $f['percentuale'] ?>% del totale</div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Grafico istogramma orario (ora per ora) -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><div class="card-title">Distribuzione oraria (0–23)</div></div>
    <canvas id="chartOre" height="90"></canvas>
  </div>

  <!-- Grafico a ciambella fasce -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="card">
      <div class="card-header"><div class="card-title">Quota per fascia oraria</div></div>
      <canvas id="chartFasceDoughnut" height="200"></canvas>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Tabella riepilogo</div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Fascia</th><th>Accessi</th><th>%</th></tr></thead>
          <tbody>
            <?php foreach ($fasce as $f): ?>
            <tr>
              <td><?= htmlspecialchars($f['fascia_oraria']) ?></td>
              <td><?= $f['totale_accessi'] ?></td>
              <td><strong><?= $f['percentuale'] ?>%</strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- ═══ SEZIONE ANDAMENTO MENSILE ══════════════════════════════════════════ -->
<section class="card">
  <div class="card-header">
    <div class="card-title">📅 Andamento Mensile Iscrizioni – <?= $annoCorrente ?></div>
    <span style="font-size:.82rem;color:#666">Nuove iscrizioni ai corsi per mese</span>
  </div>

  <!-- KPI sintetici -->
  <div class="grid-3" style="margin:1rem 0">
    <div class="card">
      <div style="font-size:.82rem;color:#888">Totale iscrizioni <?= $annoCorrente ?></div>
      <div style="font-size:1.8rem;font-weight:700"><?= $totAnno ?></div>
    </div>
    <div class="card">
      <div style="font-size:.82rem;color:#888">Mese con più iscrizioni</div>
      <div style="font-size:1.3rem;font-weight:700"><?= $mesiLabels[$mesePicco] ?? '—' ?></div>
      <div style="font-size:.82rem;color:#888"><?= max($mesiData) ?> iscrizioni</div>
    </div>
    <div class="card">
      <div style="font-size:.82rem;color:#888">Media mensile</div>
      <div style="font-size:1.8rem;font-weight:700"><?= $mediaPerMese ?></div>
    </div>
  </div>

  <!-- Grafico linea andamento mensile -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><div class="card-title">Trend iscrizioni mensili</div></div>
    <canvas id="chartMensile" height="100"></canvas>
  </div>

  <!-- Grafico a barre mesi -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><div class="card-title">Iscrizioni per mese (barre)</div></div>
    <canvas id="chartMensileBarre" height="100"></canvas>
  </div>

  <!-- Tabella mensile -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>Mese</th><th>Nuove Iscrizioni</th><th>Variazione</th></tr></thead>
      <tbody>
        <?php
        $prev = null;
        foreach ($mesiLabels as $idx => $mese):
            $val = $mesiData[$idx];
            if ($val === 0 && $idx >= (int)date('n')) break; // non mostrare mesi futuri vuoti
            $variazione = '';
            if ($prev !== null && $prev > 0) {
                $delta = $val - $prev;
                $variazione = ($delta >= 0 ? '+' : '') . $delta . ' (' . round(($delta / $prev) * 100, 1) . '%)';
            } elseif ($prev === 0 && $val > 0) {
                $variazione = '↑ Nuovo';
            }
            $prev = $val;
        ?>
        <tr style="<?= $idx === $mesePicco ? 'font-weight:600;background:rgba(45,106,79,.06)' : '' ?>">
          <td><?= $mese ?><?= $idx === $mesePicco ? ' ★' : '' ?></td>
          <td><?= $val ?></td>
          <td style="color:<?= str_starts_with($variazione,'+') ? 'green' : (str_starts_with($variazione,'-') ? '#dc3545' : '#888') ?>">
            <?= $variazione ?: '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ─── Grafico distribuzione oraria (barre) ─────────────────────────────────
const oreLabels = Array.from({length:24}, (_,i) => i + ':00');
const oreData   = <?= json_encode(array_values($accessiPerOraMap)) ?>;
const oreColors = oreLabels.map((_,i) => {
    if (i >= 6  && i <= 12) return '#e9c46a';
    if (i >= 13 && i <= 17) return '#d95f1a';
    if (i >= 18 && i <= 22) return '#264653';
    return '#adb5bd';
});
new Chart(document.getElementById('chartOre').getContext('2d'), {
    type: 'bar',
    data: { labels: oreLabels, datasets: [{ label: 'Accessi', data: oreData, backgroundColor: oreColors }] },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});

// ─── Grafico ciambella fasce ───────────────────────────────────────────────
const fasceLabels = <?= json_encode(array_column($fasce, 'fascia_oraria')) ?>;
const fasceData   = <?= json_encode(array_column($fasce, 'totale_accessi')) ?>;
new Chart(document.getElementById('chartFasceDoughnut').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: fasceLabels,
        datasets: [{ data: fasceData, backgroundColor: ['#e9c46a','#d95f1a','#264653','#adb5bd'] }]
    },
    options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
});

// ─── Grafico linea mensile ─────────────────────────────────────────────────
const mesiLabels = <?= json_encode($mesiLabels) ?>;
const mesiData   = <?= json_encode($mesiData) ?>;
new Chart(document.getElementById('chartMensile').getContext('2d'), {
    type: 'line',
    data: {
        labels: mesiLabels,
        datasets: [{
            label: 'Nuove iscrizioni',
            data: mesiData,
            borderColor: '#2d6a4f',
            backgroundColor: 'rgba(45,106,79,.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 5
        }]
    },
    options: { responsive:true, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});

// ─── Grafico barre mensile ─────────────────────────────────────────────────
new Chart(document.getElementById('chartMensileBarre').getContext('2d'), {
    type: 'bar',
    data: {
        labels: mesiLabels,
        datasets: [{
            label: 'Iscrizioni',
            data: mesiData,
            backgroundColor: mesiData.map((v,i) => i === <?= $mesePicco ?> ? '#2d6a4f' : '#52b788')
        }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});
</script>

<style>
.badge-warning { background:#fd7e14!important;color:#fff; }
.grid-3 { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap:1rem; }
</style>

<?php include __DIR__ . '/footer.php'; ?>
