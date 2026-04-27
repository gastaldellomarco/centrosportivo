<?php
/**
 * Migrazione rapida: crea le tabelle `iscritti`, `iscrizioni`, `accessi` se non esistono
 * e inserisce alcuni record di esempio.
 * Uso: visitare via browser e confermare con POST.
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Crea tabelle core</title></head><body style="font-family:Arial;padding:2rem;">
    <h2>Crea tabelle core: <code>iscritti</code>, <code>iscrizioni</code>, <code>accessi</code></h2>
    <p>Questo script creerà le tabelle se non esistono e inserirà alcuni record di esempio. Esegui solo in ambiente di sviluppo o dopo aver fatto un backup.</p>
    <form method="post">
      <button type="submit">Crea tabelle e inserisci dati di esempio</button>
    </form>
    <p style="margin-top:1rem"><a href="index.php">Indietro</a></p>
    </body></html>
    <?php
    exit;
}

try {
    $pdo->beginTransaction();

    // creare table iscritti
    $pdo->exec("CREATE TABLE IF NOT EXISTS `iscritti` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nome` VARCHAR(100) NOT NULL,
      `cognome` VARCHAR(100) DEFAULT '',
      `email` VARCHAR(150) DEFAULT '',
      `created_at` DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // iscrizioni
    $pdo->exec("CREATE TABLE IF NOT EXISTS `iscrizioni` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `iscritto_id` INT NOT NULL,
      `corso_id` INT DEFAULT NULL,
      `data_iscrizione` DATETIME DEFAULT NULL,
      `status` VARCHAR(32) DEFAULT 'attiva'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // accessi
    $pdo->exec("CREATE TABLE IF NOT EXISTS `accessi` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `iscritto_id` INT DEFAULT NULL,
      `centro_id` INT DEFAULT NULL,
      `access_time` DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // seed: inserisci alcuni iscritti se tabella vuota
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `iscritti`")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO `iscritti` (nome,cognome,email,created_at) VALUES (?, ?, ?, ?)");
        $now = date('Y-m-d H:i:s');
        $stmt->execute(['Mario','Rossi','mario.rossi@example.com',$now]);
        $stmt->execute(['Giulia','Bianchi','giulia.bianchi@example.com',$now]);
        $stmt->execute(['Luca','Verdi','luca.verdi@example.com',$now]);
    }

    // seed iscrizioni se vuota
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `iscrizioni`")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO `iscrizioni` (iscritto_id,corso_id,data_iscrizione,status) VALUES (?, ?, ?, ?)");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([1, 1, $now, 'attiva']);
        $stmt->execute([2, 2, $now, 'attiva']);
    }

    // seed accessi se vuota
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `accessi`")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO `accessi` (iscritto_id,centro_id,access_time) VALUES (?, ?, ?)");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([1, 1, $now]);
        $stmt->execute([2, 1, $now]);
    }

    $pdo->commit();
    setFlash('success', 'Tabelle create e dati di esempio inseriti.');
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    try { $pdo->rollBack(); } catch (Exception $_) { /* ignore */ }
  }
  setFlash('error', 'Errore creazione tabelle: ' . $e->getMessage());
}

header('Location: index.php');
exit;
