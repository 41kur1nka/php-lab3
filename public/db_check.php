<?php

global $pdo;
require __DIR__ . '/../config.php';
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$__base = preg_replace('#/public$#', '', $__base);

try {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "DB OK. users count = " . $count;
} catch (Throwable $e) {
    echo "DB query error: " . $e->getMessage();
}
