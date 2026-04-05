<?php
require_once __DIR__ . '/db.php';
$pdo = getDB();
$stmt = $pdo->query("SELECT id, title, worker_error FROM documents WHERE worker_status = 'error' ORDER BY id DESC LIMIT 5");
$errors = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo $errors[0]['worker_error'];
echo "</pre>";
?>
