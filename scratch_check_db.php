<?php
require 'backend/config.php';
require 'backend/db.php';
$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, title, file_path, pdf_url, worker_status FROM documents WHERE id = 7714');
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
