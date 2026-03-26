<?php
// backend/form_options.php — Returns dynamic form options as JSON
// Used by contribute.html to populate dropdowns from the database.
// Supports: ?type=levels | semesters&level_id=X | subjects&semester_id=X | types | years

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$type = $_GET['type'] ?? '';

try {
    $pdo = getDB();
    $data = [];

    switch ($type) {
        case 'levels':
            $stmt = $pdo->query("SELECT id, name FROM levels ORDER BY id");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'semesters':
            $levelId = intval($_GET['level_id'] ?? 0);
            if ($levelId <= 0) {
                echo json_encode(['error' => 'level_id is required']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT id, name FROM semesters WHERE level_id = ? ORDER BY name");
            $stmt->execute([$levelId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'subjects':
            $semesterId = intval($_GET['semester_id'] ?? 0);
            if ($semesterId <= 0) {
                echo json_encode(['error' => 'semester_id is required']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT id, name FROM subjects WHERE semester_id = ? AND is_active = 1 ORDER BY name");
            $stmt->execute([$semesterId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'types':
            $stmt = $pdo->query("SELECT id, name FROM document_types WHERE name IN ('devoir', 'corrige_devoir', 'partiel', 'corrige_partiel') ORDER BY name");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'years':
            $stmt = $pdo->query("SELECT id, year FROM years ORDER BY year DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        default:
            echo json_encode(['error' => 'Unknown type. Use: levels, semesters, subjects, types, years']);
            exit;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
