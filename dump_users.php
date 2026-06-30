<?php
require_once __DIR__ . '/backend/db.php';
$pdo = getDB();
$stmt = $pdo->query("SELECT id, name, email FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
