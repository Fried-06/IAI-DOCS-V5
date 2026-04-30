<?php
require 'backend/db.php';
$pdo = getDB();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table) {
    echo "\nTABLE: $table\n";
    $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
