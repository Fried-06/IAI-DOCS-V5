<?php
// backend/add_indexes.php — Database index migration for performance optimization
// Designed for PostgreSQL (Supabase)

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/db.php';

// Verify if user is admin or if called via CLI
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die("Accès refusé. Réservé aux administrateurs.");
    }
}

try {
    $pdo = getDB();
    echo "Connexion établie avec succès.\n";
    echo "Début de la création des index...\n\n";

    $queries = [
        "documents_status" => "CREATE INDEX IF NOT EXISTS idx_documents_status ON documents(status)",
        "documents_subject_id" => "CREATE INDEX IF NOT EXISTS idx_documents_subject_id ON documents(subject_id)",
        "documents_type_id" => "CREATE INDEX IF NOT EXISTS idx_documents_type_id ON documents(type_id)",
        "documents_year_id" => "CREATE INDEX IF NOT EXISTS idx_documents_year_id ON documents(year_id)",
        "documents_user_id" => "CREATE INDEX IF NOT EXISTS idx_documents_user_id ON documents(user_id)",
        "subjects_semester_id" => "CREATE INDEX IF NOT EXISTS idx_subjects_semester_id ON subjects(semester_id)",
        "semesters_level_id" => "CREATE INDEX IF NOT EXISTS idx_semesters_level_id ON semesters(level_id)",
        "users_beta_approved" => "CREATE INDEX IF NOT EXISTS idx_users_beta_approved ON users(is_beta_approved)"
    ];

    foreach ($queries as $name => $sql) {
        echo "Exécution de : $name... ";
        $start = microtime(true);
        $pdo->exec($sql);
        $duration = round((microtime(true) - $start) * 1000, 2);
        echo "OK ({$duration} ms)\n";
    }

    echo "\nMigration des index terminée avec succès !";
} catch (\Exception $e) {
    echo "\nErreur lors de la création des index : " . $e->getMessage();
}
?>
