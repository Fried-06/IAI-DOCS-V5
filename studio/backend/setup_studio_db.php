<?php
require_once __DIR__ . '/../../backend/db.php';

try {
    $pdo = getDB();

    // 1. Studio User Docs Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS studio_user_docs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            storage_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(10) NOT NULL,
            status VARCHAR(50) DEFAULT 'unprocessed',
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    // 2. Studio Sessions (The Study Basket)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS studio_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            context_official JSON,
            context_private JSON,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    echo "Studio database tables created successfully.\n";

} catch (\PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
?>
