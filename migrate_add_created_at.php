<?php
/**
 * Esegue una migrazione semplice: aggiunge la colonna `created_at` alla tabella `iscritti`
 * - Safe: controlla prima se la tabella/colonna esistono
 * - Popola le righe esistenti con: IFNULL(created_at, NOW()) oppure usa colonne alternative se trovate
 * Usage: visitare questa pagina via browser; lo script richiede conferma POST per eseguire.
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Mostra conferma
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Migrazione: aggiungi created_at</title></head><body style="font-family:Arial;padding:2rem;">
    <h2>Migrazione: aggiungi colonna <code>created_at</code> alla tabella <code>iscritti</code></h2>
    <p>Questo script aggiungerà una colonna <code>created_at</code> di tipo <code>DATETIME</code> nullable e popolerà le righe esistenti con la data corrente o con una colonna alternativa se trovata.</p>
    <form method="post">
      <button type="submit">Esegui migrazione</button>
    </form>
    <p><a href="analisi.php">Indietro</a></p>
    </body></html>
    <?php
    exit;
}

// Esegui la migrazione (post)
try {
    // verifica che la tabella esista
    $res = $pdo->query("SHOW TABLES LIKE 'iscritti'")->fetchAll();
    if (count($res) === 0) {
        throw new Exception("Tabella 'iscritti' non trovata.");
    }

    // verifica colonna
    $col = $pdo->query("SHOW COLUMNS FROM `iscritti` LIKE 'created_at'")->fetch();
    if ($col) {
        throw new Exception("La colonna 'created_at' esiste già.");
    }

    // aggiungi colonna nullable
    $pdo->exec("ALTER TABLE `iscritti` ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL");

    // cerca colonne alternative comuni
    $alts = ['data','created','date','signup_date'];
    $foundAlt = null;
    $cols = $pdo->query("SHOW COLUMNS FROM `iscritti`")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($alts as $a) {
        if (in_array($a, $cols, true)) { $foundAlt = $a; break; }
    }

    if ($foundAlt) {
        // prova a copiare i valori dalla colonna alternativa
        // usa CONCAT/CAST per cercare di convertire stringhe comuni in DATETIME
        $sql = "UPDATE `iscritti` SET created_at = CASE
                  WHEN STR_TO_DATE(`$foundAlt`, '%Y-%m-%d %H:%i:%s') IS NOT NULL THEN STR_TO_DATE(`$foundAlt`, '%Y-%m-%d %H:%i:%s')
                  WHEN STR_TO_DATE(`$foundAlt`, '%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(`$foundAlt`, '%Y-%m-%d')
                  ELSE created_at END
                WHERE created_at IS NULL";
        try { $pdo->exec($sql); } catch (Exception $e) { /* ignore */ }
    }

    // Infine, imposta NOW() per eventuali null rimanenti
    $pdo->exec("UPDATE `iscritti` SET created_at = NOW() WHERE created_at IS NULL");

    // redirect di ritorno con flash
    setFlash('success', "Migrazione completata: colonna 'created_at' aggiunta e popolata.");
    header('Location: analisi.php');
} catch (Exception $e) {
    setFlash('error', 'Errore migrazione: ' . $e->getMessage());
    header('Location: analisi.php');
}
