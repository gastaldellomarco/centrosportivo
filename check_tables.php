<?php
require_once __DIR__ . '/config.php';
$tables = ['iscritti','iscrizioni','accessi'];
$out = [];
foreach ($tables as $t) {
    try {
        $exists = (bool) $pdo->query("SHOW TABLES LIKE '".str_replace('`','',$t)."'")->fetch();
        if ($exists) {
            $cnt = (int) $pdo->query("SELECT COUNT(*) FROM `".str_replace('`','',$t)."`")->fetchColumn();
        } else {
            $cnt = null;
        }
        $out[$t] = ['exists' => $exists, 'count' => $cnt];
    } catch (Exception $e) {
        $out[$t] = ['exists' => false, 'error' => $e->getMessage()];
    }
}
header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
