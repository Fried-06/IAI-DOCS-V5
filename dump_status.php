<?php
require_once __DIR__ . '/backend/db.php';
$pdo = getDB();
$stmt = $pdo->query("SELECT DISTINCT worker_status FROM documents");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
