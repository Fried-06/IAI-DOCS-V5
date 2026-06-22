<?php
require 'backend/config.php';
require 'backend/db.php';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, title, file_path, pdf_url, worker_status FROM documents WHERE title LIKE '%Examen de XML & Web Service 2026%'");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
