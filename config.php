<?php
// ============================================================
//  config.php – Connessione al database
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // modifica se necessario
define('DB_PASS', '');           // modifica se necessario
define('DB_NAME', 'centro_sportivo');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:monospace;padding:2rem;background:#fff1f0;border:1px solid #ff4d4f;border-radius:8px;color:#cf1322;margin:2rem;">
        <strong>Errore di connessione al database:</strong><br>' . htmlspecialchars($e->getMessage()) . '<br><br>
        Verifica che XAMPP sia avviato e il database <em>centro_sportivo</em> sia stato importato.
    </div>');
}

// Funzione helper per i messaggi flash
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = compact('type', 'msg');
}
function getFlash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}
session_start();
