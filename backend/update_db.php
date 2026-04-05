<?php
require_once __DIR__ . '/db.php';

try {
    $pdo = getDB();

    // Ajouter raw_markdown
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN raw_markdown TEXT NULL");
        echo "Colonne raw_markdown ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte raw_markdown: " . $e->getMessage() . "\n";
    }

    // Ajouter locked_by
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN locked_by INT NULL");
        echo "Colonne locked_by ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte locked_by: " . $e->getMessage() . "\n";
    }

    // Ajouter locked_at
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN locked_at DATETIME NULL");
        echo "Colonne locked_at ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte locked_at: " . $e->getMessage() . "\n";
    }

    // Ajouter worker_status 
    // Types: 'pending' = will be built by worker, 'building' = worker is building it right now, 'success', 'error'
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN worker_status ENUM('idle', 'pending', 'building', 'success', 'error') DEFAULT 'idle'");
        echo "Colonne worker_status ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte worker_status: " . $e->getMessage() . "\n";
    }

    // Ajouter worker_error (if any error from Sphinx)
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN worker_error TEXT NULL");
        echo "Colonne worker_error ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte worker_error: " . $e->getMessage() . "\n";
    }
    
    // Add processed_by to know WHICH admin locked it or processed it if not using users table for admins directly
    try {
        $pdo->exec("ALTER TABLE documents ADD COLUMN admin_id INT NULL");
        echo "Colonne admin_id ajoutée.\n";
    } catch (PDOException $e) {
        echo "Alerte admin_id: " . $e->getMessage() . "\n";
    }

    echo "Base de données mise à jour avec succès !\n";

} catch (PDOException $e) {
    die("Échec de la connexion à la bd: " . $e->getMessage());
}
