<?php
require 'backend/db.php';
$pdo = getDB();
$rows = $pdo->query('SELECT file_path, pdf_url FROM documents LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo "FILE_PATH: " . $row['file_path'] . " | PDF_URL: " . $row['pdf_url'] . "\n";
}
