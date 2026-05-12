<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Disponibilità Posti';
include __DIR__ . '/header.php';

// ─── Blocco iscrizione: impedisce l'inserimento se corso pieno ───────────────
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idIscritto = (int)($_POST['idIscritto'] ?? 0);
    $idCorso    = (int)($_POST['idCorso'] ?? 0);
    if ($idIscritto && $idCorso) {
        // Conta iscrizioni attuali per il corso
        $stmtCount = $pdo->prepare(
            "SELECT COUNT(*) FROM ISCRIZIONI_CORSI WHERE idCorso = ?"
        );
        $stmtCount->execute([$idCorso]);
        $attuali = (int)$stmtCount->fetchColumn();

        // Recupera maxPartecipanti
        $stmtMax = $pdo->prepare("SELECT maxPartecipanti, nomeCorso FROM CORSI WHERE idCorso = ?");
        $stmtMax->execute([$idCorso]);
        $corso = $stmtMax->fetch();

        if (!$corso) {
            $msg = ['tipo' => 'error', 'testo' => 'Corso non trovato.'];
        } elseif ($attuali >= $corso['maxPartecipanti']) {
            $msg = ['tipo' => 'error', 'testo' => 'Iscrizione rifiutata: il corso <strong>' . htmlspecialchars($corso['nomeCorso']) . '</strong> ha raggiunto il limite massimo di ' . $corso['maxPartecipanti'] . ' partecipanti.'];
        } else {
            // Verifica duplicato
            $stmtDup = $pdo->prepare("SELECT COUNT(*) FROM ISCRIZIONI_CORSI WHERE idIscritto=? AND idCorso=?");
            $stmtDup->execute([$idIscritto, $idCorso]);
            if ((int)$stmtDup->fetchColumn() > 0) {
                $msg = ['tipo' => 'error', 'testo' => 'Questo iscritto è già registrato al corso selezionato.'];
            } else {
                $stmtIns = $pdo->prepare(
                    "INSERT INTO ISCRIZIONI_CORSI (idIscritto, idCorso, dataInizio, dataScadenza, pagamentoEffettuato)
                     VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 0)"
                );
                $stmtIns->execute([$idIscritto, $idCorso]);
                $msg = ['tipo' => 'success', 'testo' => 'Iscrizione a <strong>' . htmlspecialchars($corso['nomeCorso']) . '</strong> registrata con successo.'];
            }
        }
    } else {
        $msg = ['tipo' => 'error', 'testo' => 'Seleziona un iscritto e un corso.'];
    }
}

// ─── Query: disponibilità corsi ──────────────────────────────────────────────
$corsiDisp = $pdo->query("
    SELECT
        c.idCorso,
        ce.nomeCentro,
        c.nomeCorso,
        c.categoria,
        c.maxPartecipanti,
        COUNT(ic.idIscrizione) AS iscritti_attuali,
        c.maxPartecipanti - COUNT(ic.idIscrizione) AS posti_liberi,
        ROUND(COUNT(ic.idIscrizione) / c.maxPartecipanti * 100, 1) AS percentuale_occupazione
    FROM CORSI c
    JOIN CENTRI ce ON ce.idCentro = c.idCentro
    LEFT JOIN ISCRIZIONI_CORSI ic ON ic.idCorso = c.idCorso
    WHERE c.attivo = 1
    GROUP BY c.idCorso, ce.nomeCentro, c.nomeCorso, c.categoria, c.maxPartecipanti
    ORDER BY percentuale_occupazione DESC
")->fetchAll();

// Lista iscritti e corsi per il form
$iscritti = $pdo->query("SELECT idIscritto, CONCAT(nome,' ',cognome) as nomeCompleto FROM ISCRITTI ORDER BY cognome")->fetchAll();
$corsiAttivi = $pdo->query("SELECT idCorso, nomeCorso FROM CORSI WHERE attivo=1 ORDER BY nomeCorso")->fetchAll();
?>

<section class="card">
  <div class="card-header">
    <div class="card-title">Disponibilità Posti nei Corsi</div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg['tipo'] === 'success' ? 'success' : 'error' ?>" style="margin-bottom:1rem">
      <?= $msg['testo'] ?>
    </div>
  <?php endif; ?>

  <!-- Form aggiunta iscrizione con controllo capienza -->
  <div class="card" style="margin-bottom:1.5rem;background:var(--bg-offset,#f8f8f8);">
    <div class="card-header"><div class="card-title" style="font-size:.95rem">➕ Nuova Iscrizione a Corso (con controllo capienza)</div></div>
    <form method="post" class="form-grid" style="margin-top:.75rem">
      <div class="form-group">
        <label for="idIscritto">Iscritto</label>
        <select name="idIscritto" id="idIscritto" required>
          <option value="">— Seleziona —</option>
          <?php foreach ($iscritti as $i): ?>
            <option value="<?= $i['idIscritto'] ?>"><?= htmlspecialchars($i['nomeCompleto']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="idCorso">Corso</label>
        <select name="idCorso" id="idCorso" required>
          <option value="">— Seleziona —</option>
          <?php foreach ($corsiAttivi as $c): ?>
            <option value="<?= $c['idCorso'] ?>"><?= htmlspecialchars($c['nomeCorso']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary">Registra Iscrizione</button>
      </div>
    </form>
  </div>

  <!-- Tabella disponibilità -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Centro</th>
          <th>Corso</th>
          <th>Categoria</th>
          <th>Max Partecipanti</th>
          <th>Iscritti</th>
          <th>Posti Liberi</th>
          <th>Occupazione</th>
          <th>Stato</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($corsiDisp as $r):
            $perc = (float)$r['percentuale_occupazione'];
            $liberi = (int)$r['posti_liberi'];
            $max = (int)$r['maxPartecipanti'];
            $disponibilitaPerc = $max > 0 ? ($liberi / $max * 100) : 0;

            if ($perc >= 100) {
                $statoClass = 'badge-error';
                $statoLabel = 'PIENO';
            } elseif ($disponibilitaPerc < 10) {
                $statoClass = 'badge-warning';
                $statoLabel = '⚠ Quasi pieno';
            } else {
                $statoClass = 'badge-success';
                $statoLabel = 'Disponibile';
            }
        ?>
        <tr class="<?= $perc >= 100 ? 'row-danger' : ($disponibilitaPerc < 10 ? 'row-warning' : '') ?>">
          <td><?= htmlspecialchars($r['nomeCentro']) ?></td>
          <td><?= htmlspecialchars($r['nomeCorso']) ?></td>
          <td><span class="badge"><?= htmlspecialchars($r['categoria']) ?></span></td>
          <td style="text-align:center"><?= $r['maxPartecipanti'] ?></td>
          <td style="text-align:center"><?= $r['iscritti_attuali'] ?></td>
          <td style="text-align:center;font-weight:600"><?= $liberi ?></td>
          <td>
            <div class="progress-bar-wrap">
              <div class="progress-bar" style="width:<?= min($perc,100) ?>%;background:<?= $perc>=100 ? 'var(--error,#dc3545)' : ($disponibilitaPerc<10 ? 'var(--warning,#fd7e14)' : 'var(--success,#28a745)') ?>"><?= $perc ?>%</div>
            </div>
          </td>
          <td><span class="badge <?= $statoClass ?>"><?= $statoLabel ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:.75rem;font-size:.85rem;color:#666">
    ⚠ I corsi con meno del <strong>10% di disponibilità residua</strong> sono evidenziati in arancione. I corsi <strong>pieni</strong> sono in rosso e non accettano nuove iscrizioni.
  </p>
</section>

<style>
.row-danger td { background: rgba(220,53,69,.07); }
.row-warning td { background: rgba(253,126,20,.07); }
.progress-bar-wrap { background:#e9ecef; border-radius:4px; height:18px; min-width:80px; overflow:hidden; }
.progress-bar { height:100%; border-radius:4px; font-size:.75rem; color:#fff; text-align:center; line-height:18px; transition:width .3s; white-space:nowrap; }
.badge-error { background:var(--error,#dc3545)!important; color:#fff; }
.badge-warning { background:var(--warning,#fd7e14)!important; color:#fff; }
.badge-success { background:var(--success,#28a745)!important; color:#fff; }
</style>

<?php include __DIR__ . '/footer.php'; ?>
