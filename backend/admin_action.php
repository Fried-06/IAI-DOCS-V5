<?php
// backend/admin_action.php — Approve or Reject documents from DB
// On approve: runs Python pipeline (convert → clean → route → build)
// then stores the RELATIVE public file_path in the documents table.

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$docId = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($docId <= 0 || !in_array($action, ['approve', 'reject'])) {
    die("Invalid parameters.");
}

$pdo = getDB();

// Fetch the pending document with all joined metadata
$stmt = $pdo->prepare(
    "SELECT d.*, 
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
     WHERE d.id = ? AND d.status = 'pending'"
);
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    die("Document not found or already processed.");
}

// ============================================
// REJECT
// ============================================
if ($action === 'reject') {
    $pdo->prepare("UPDATE documents SET status = 'rejected' WHERE id = ?")->execute([$docId]);

    // Notification
    if ($doc['user_id']) {
        $notifStmt = $pdo->prepare(
            "INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'rejection', ?, ?)"
        );
        $notifStmt->execute([
            $doc['user_id'],
            $docId,
            'Document refusé',
            "Votre document '{$doc['title']}' a été refusé par un administrateur."
        ]);
    }

    // Log
    $logMsg = date('[Y-m-d H:i:s] ') . "REJECTED DOC: {$doc['title']} | ID: $docId\n";
    file_put_contents(__DIR__ . '/admin.log', $logMsg, FILE_APPEND);

    header("Location: admin.php?tab=documents&success=" . urlencode("Document rejeté."));
    exit;
}

// ============================================
// APPROVE — Run Python pipeline then store relative file_path
// ============================================
$filename = escapeshellarg($doc['filename']);
$title    = escapeshellarg($doc['title']);

// Build a safe category/type name for routing
$typeName = $doc['type_name']; // e.g. "partiel", "devoir", "cours"

// Sanitize subject name for filesystem paths
$subjectSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $doc['subject_name']);
$subjectSlug = strtolower(trim($subjectSlug, '_'));

// Sanitize level name for filesystem
$levelSlug = preg_replace('/\s+/', '_', $doc['level_name']); // "L3 GLSI" → "L3_GLSI"

// Sanitize semester
$semesterSlug = preg_replace('/\s+/', '', strtolower($doc['semester_name'])); // "Semestre 1" → "semestre1"

$year = $doc['year'];

// 1. Convert (PDF/DOCX → Markdown)
$cmdConvert = "python convert.py ../uploads/$filename 2>&1";
$outConvert = shell_exec($cmdConvert);

// 2. Clean
$nameWithoutExt = pathinfo($doc['filename'], PATHINFO_FILENAME);
$mdFilenameArg = escapeshellarg($nameWithoutExt . '.md');
$typeArg = escapeshellarg($typeName);
$cmdClean = "python clean.py ../processed/$mdFilenameArg " . escapeshellarg($doc['title']) . " $typeArg 2>&1";
$outClean = shell_exec($cmdClean);

// 3. Route — place the cleaned .md into the correct Sphinx source folder
$cmdRoute = "python route.py ../cleaned/$mdFilenameArg " . escapeshellarg($doc['title']) . " " . escapeshellarg($levelSlug) . " $typeArg 2>&1";
$outRoute = shell_exec($cmdRoute);

// 4. Build Sphinx
$cmdBuild = "python build_docs.py 2>&1";
$outBuild = shell_exec($cmdBuild);

// 5. Compute the RELATIVE public file_path
//    Format: {level}/{semester}/{subject}/{type}/{year}.html
//    Example: L1/semestre1/algorithmique/partiel/2024.html
$relativePath = "$levelSlug/$semesterSlug/$subjectSlug/$typeName/$year.html";

// 6. Update document in DB with approved status and relative file_path
$updateStmt = $pdo->prepare("UPDATE documents SET status = 'approved', file_path = ? WHERE id = ?");
$updateStmt->execute([$relativePath, $docId]);

// 7. Notification
if ($doc['user_id']) {
    $notifStmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'approval', ?, ?)"
    );
    $notifStmt->execute([
        $doc['user_id'],
        $docId,
        'Document publié',
        "Votre document '{$doc['title']}' a été approuvé et publié !"
    ]);
}

// 8. Log
$logMsg = date('[Y-m-d H:i:s] ') . "APPROVED DOC: {$doc['title']} | ID: $docId | Path: $relativePath\n";
$logMsg .= "  Convert: $outConvert\n  Clean: $outClean\n  Route: $outRoute\n  Build: $outBuild\n";
file_put_contents(__DIR__ . '/admin.log', $logMsg, FILE_APPEND);

header("Location: admin.php?tab=documents&success=" . urlencode("Document approuvé et publié !"));
exit;
?>
