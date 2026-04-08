<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../../backend/db.php';
$pdo = getDB();

$q = $_GET['q'] ?? '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Search documents that are approved
// Using CONCAT to make search intuitive
try {
    $stmt = $pdo->prepare("
        SELECT d.id, d.title, d.year_id, t.name as type_name, s.name as subject_name 
        FROM documents d
        JOIN document_types t ON d.type_id = t.id
        JOIN subjects s ON d.subject_id = s.id
        JOIN years y ON d.year_id = y.id
        WHERE (d.title LIKE :q OR s.name LIKE :q OR t.name LIKE :q OR y.year LIKE :q)
        LIMIT 10
    ");
    $searchTerm = '%' . $q . '%';
    $stmt->execute(['q' => $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format slightly
    $formatted = array_map(function($r) {
        return [
            'id' => $r['id'],
            'type' => 'official',
            'label' => $r['title'] . ' (' . $r['subject_name'] . ' - ' . str_replace('_', ' ', $r['type_name']) . ')'
        ];
    }, $results);
    
    echo json_encode($formatted);
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
