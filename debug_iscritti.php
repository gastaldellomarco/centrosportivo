<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
try {
    $exists = (bool)$pdo->query("SHOW TABLES LIKE 'iscritti'")->fetch();
    $count = $exists ? (int)$pdo->query("SELECT COUNT(*) FROM `iscritti`")->fetchColumn() : null;
    $rows = [];
    if ($exists) {
        $order = '';
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `iscritti`")->fetchAll(PDO::FETCH_COLUMN,0);
            if (in_array('created_at', $cols, true)) $order = ' ORDER BY `created_at` DESC';
            elseif (in_array('id', $cols, true)) $order = ' ORDER BY `id` DESC';
        } catch (Exception $_) { }
        $stmt = $pdo->query("SELECT * FROM `iscritti`" . $order . " LIMIT 5");
        $rows = $stmt->fetchAll();
    }
    echo json_encode(['exists'=>$exists,'count'=>$count,'sample'=>$rows], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error'=>$e->getMessage()]);
}
