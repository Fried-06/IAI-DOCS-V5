<?php
require 'backend/db.php';
$pdo = getDB();
try {
    $pdo->exec('ALTER TABLE users ADD COLUMN last_active TIMESTAMP NULL DEFAULT NULL');
    echo "Success\n";
} catch(Exception $e) {
    if(strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Already exists\n";
    } else {
        echo $e->getMessage() . "\n";
    }
}
