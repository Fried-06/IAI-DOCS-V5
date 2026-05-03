<?php
// backend/search_api.php — Search API using relational database
// Filters by level_id, semester_id, subject_id, type_id, year_id + free text query
// Returns JSON with relative public URLs for document links

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$query      = strtolower(trim($_GET['q'] ?? ''));
$levelId    = intval($_GET['level_id'] ?? 0);
$semesterId = intval($_GET['semester_id'] ?? 0);
$subjectId  = intval($_GET['subject_id'] ?? 0);
$typeId     = intval($_GET['type_id'] ?? 0);
$yearId     = intval($_GET['year_id'] ?? 0);

$levelStr   = trim($_GET['level'] ?? '');
$semesterStr= trim($_GET['semester'] ?? '');
$typeStr    = trim($_GET['type'] ?? '');

// Base URL for document links (configurable)
$docsBaseUrl = '/Docs/_build/html/';

try {
    $pdo = getDB();

    $sql = "SELECT d.id, d.title, d.file_path, d.created_at, d.pdf_url, d.status,
                   s.name AS subject_name,
                   sem.name AS semester_name,
                   l.name AS level_name,
                   dt.name AS type_name,
                   y.year,
                   u.name AS user_name
            FROM documents d
            JOIN subjects s ON d.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN levels l ON sem.level_id = l.id
            JOIN document_types dt ON d.type_id = dt.id
            JOIN years y ON d.year_id = y.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.status = 'approved'";
    $params = [];

    if ($levelId > 0) {
        $sql .= " AND l.id = ?";
        $params[] = $levelId;
    }
    if ($levelStr !== '') {
        $sql .= " AND l.name = ?";
        $params[] = str_replace('_', ' ', $levelStr);
    }
    if ($semesterId > 0) {
        $sql .= " AND sem.id = ?";
        $params[] = $semesterId;
    }
    if ($semesterStr !== '') {
        $sql .= " AND sem.name = ?";
        $params[] = $semesterStr;
    }
    if ($subjectId > 0) {
        $sql .= " AND s.id = ?";
        $params[] = $subjectId;
    }
    if ($typeId > 0) {
        $sql .= " AND dt.id = ?";
        $params[] = $typeId;
    }
    if ($typeStr !== '') {
        $sql .= " AND dt.name = ?";
        $params[] = $typeStr;
    }
    if ($yearId > 0) {
        $sql .= " AND y.id = ?";
        $params[] = $yearId;
    }
    if (!empty($query)) {
        $sql .= " AND (d.title LIKE ? OR s.name LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }

    $sql .= " ORDER BY y.year DESC, l.name, sem.name, s.name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build response with proper links
    $output = [];
    foreach ($results as $row) {
        // PDF Link: Check if it's already an absolute URL
        if (!empty($row['pdf_url'])) {
            if (strpos($row['pdf_url'], 'http') === 0) {
                $pdfLink = $row['pdf_url'];
            } else {
                $pdfLink = '/' . ltrim($row['pdf_url'], '/');
            }
        } else {
            $pdfLink = '#';
        }

        // HTML Link: Check if it's already an absolute URL
        $htmlLink = '#';
        if ($row['status'] === 'approved' && !empty($row['file_path'])) {
            if (strpos($row['file_path'], 'http') === 0) {
                $htmlLink = 'viewer.php?url=' . urlencode($row['file_path']);
            } else {
                $cleanedPath = ltrim($row['file_path'], '/');
                $htmlLink = $docsBaseUrl . $cleanedPath;
            }
        }
        $hasHtml = ($htmlLink !== '#');

        $output[] = [
            'title'          => $row['title'],
            'subject'        => $row['subject_name'],
            'level_label'    => $row['level_name'],
            'semester_label' => $row['semester_name'],
            'type'           => $row['type_name'],
            'year'           => $row['year'],
            'pdfLink'        => $pdfLink,
            'htmlLink'       => $htmlLink,
            'hasHtml'        => $hasHtml,
            'contributor'    => $row['user_name'] ?? 'Anonyme',
            'date'           => $row['created_at']
        ];
    }

    echo json_encode([
        'count'   => count($output),
        'results' => $output
    ], JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
