<?php
require 'backend/config.php';
require 'backend/db.php';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, title, file_path, pdf_url, worker_status FROM documents WHERE title LIKE '%XML%' LIMIT 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
