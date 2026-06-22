<?php
require_once __DIR__ . '/beta_check.php';
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

$limit      = intval($_GET['limit'] ?? 12);
$page       = intval($_GET['page'] ?? 1);
if ($limit <= 0 || $limit > 100) $limit = 12;
if ($page <= 0) $page = 1;
$offset     = ($page - 1) * $limit;

// Base URL for document links (configurable)
$docsBaseUrl = '/Docs/_build/html/';

try {
    $pdo = getDB();

    $whereSql = " WHERE d.status = 'approved'";
    $params = [];

    if ($levelId > 0) {
        $whereSql .= " AND l.id = ?";
        $params[] = $levelId;
    }
    if ($levelStr !== '') {
        $whereSql .= " AND l.name = ?";
        $params[] = str_replace('_', ' ', $levelStr);
    }
    if ($semesterId > 0) {
        $whereSql .= " AND sem.id = ?";
        $params[] = $semesterId;
    }
    if ($semesterStr !== '') {
        $whereSql .= " AND sem.name = ?";
        $params[] = $semesterStr;
    }
    if ($subjectId > 0) {
        $whereSql .= " AND s.id = ?";
        $params[] = $subjectId;
    }
    if ($typeId > 0) {
        $whereSql .= " AND dt.id = ?";
        $params[] = $typeId;
    }
    if ($typeStr !== '') {
        $whereSql .= " AND dt.name = ?";
        $params[] = $typeStr;
    }
    if ($yearId > 0) {
        $whereSql .= " AND y.id = ?";
        $params[] = $yearId;
    }
    if (!empty($query)) {
        $whereSql .= " AND (d.title LIKE ? OR s.name LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }

    // 1. Get total count for pagination
    $countSql = "SELECT COUNT(d.id)
                 FROM documents d
                 JOIN subjects s ON d.subject_id = s.id
                 JOIN semesters sem ON s.semester_id = sem.id
                 JOIN levels l ON sem.level_id = l.id
                 JOIN document_types dt ON d.type_id = dt.id
                 JOIN years y ON d.year_id = y.id
                 " . $whereSql;
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = intval($countStmt->fetchColumn());

    // 2. Get paginated results
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
            " . $whereSql . "
            ORDER BY y.year DESC, l.name, sem.name, s.name
            LIMIT " . intval($limit) . " OFFSET " . intval($offset);

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

        // HTML Link: Use the Premium Viewer with the document ID
        $htmlLink = '#';
        if ($row['status'] === 'approved' && (!empty($row['file_path']) || !empty($row['pdf_url']))) {
            $htmlLink = '/viewer/' . $row['id'];
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
        'total'    => $totalCount,
        'count'    => count($output),
        'has_more' => (($page * $limit) < $totalCount),
        'results'  => $output
    ], JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
