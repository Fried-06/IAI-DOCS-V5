<?php
// backend/setup_db.php — Initialize or reset the database from init.sql
// Usage: php setup_db.php
// This script drops existing tables and recreates them from init.sql.

require_once __DIR__ . '/db.php';

try {
    // Connect WITHOUT selecting a specific database first
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sqlFile = __DIR__ . '/init.sql';
    if (!file_exists($sqlFile)) {
        die("Error: init.sql not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Execute the full SQL file (multi-statement)
    $pdo->exec($sql);
    
    echo "Database initialized successfully from init.sql.\n";
    echo "Tables created: users, years, levels, semesters, subjects, document_types, documents, notifications\n";
    echo "Seed data inserted.\n";

} catch (\PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
