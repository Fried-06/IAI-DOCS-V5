<?php
// backend/admin_action.php — Approve or Reject documents from DB
// On approve: runs Python pipeline (convert → clean → route → build)
// then stores the RELATIVE public file_path in the documents table.

session_start();
set_time_limit(300);
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    include __DIR__ . '/../403.php';
    exit;
}
// Increase timeout for AI generation
set_time_limit(300);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$userId = intval($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

// ============================================
// USER MANAGEMENT ACTIONS
// ============================================
if (in_array($action, ['promote_admin', 'demote_admin', 'delete_user'])) {
    if ($userId <= 0) {
        die("Invalid user ID.");
    }
    
    $pdo = getDB();
    
    if ($action === 'promote_admin') {
        $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("L'utilisateur a été promu administrateur."));
        exit;
    }
    
    if ($action === 'demote_admin') {
        $pdo->prepare("UPDATE users SET role = 'student' WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("Le rôle administrateur a été retiré."));
        exit;
    }
    
    if ($action === 'delete_user') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("L'utilisateur a été supprimé."));
        exit;
    }
}

$docId = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($docId <= 0 || !in_array($action, ['generate', 'generate_docling', 'generate_ai', 'generate_ai_clean', 'save_draft', 'publish', 'reject'])) {
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

    @unlink(__DIR__ . '/cache/document_tree.json');
    header("Location: admin.php?tab=documents&success=" . urlencode("Document rejeté."));
    exit;
}

// ============================================
// GENERATE — Multi-mode extraction pipeline
// ============================================

// Map actions to extract.py modes
$generateActions = [
    'generate'           => 'ai_clean',   // Legacy: default to Docling + AI cascade
    'generate_docling'   => 'docling',    // Docling only (fast, free)
    'generate_ai'        => 'ai',         // Gemini Vision Direct (no Docling)
    'generate_ai_clean'  => 'ai_clean',   // Docling + AI cascade
];

if (isset($generateActions[$action])) {
    $mode = $generateActions[$action];
    $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['title']);
    $filename = escapeshellarg($doc['filename']);
    $modeArg = escapeshellarg($mode);
    
    // Increase timeout for AI modes
    if ($mode !== 'docling') {
        set_time_limit(300);
    }
    
    // Construct nice display title, e.g., "Probabilités - Partiel 2026"
    $typeLabel = str_replace('_', ' ', ucfirst($doc['type_name']));
    $displayTitle = escapeshellarg("{$doc['subject_name']} - {$typeLabel} {$doc['year']}");
    
    $cmdExtract = "python extract.py ../uploads/$filename $docId " . escapeshellarg($safeTitle) . " --display-title $displayTitle --mode $modeArg 2>&1";
    $outExtract = shell_exec($cmdExtract);
    
    // Log
    $modeLabel = strtoupper($mode);
    $logMsg = date('[Y-m-d H:i:s] ') . "GENERATE [$modeLabel] DOC: {$doc['title']} | ID: $docId\n";
    $logMsg .= "  Extract: $outExtract\n";
    file_put_contents(__DIR__ . '/admin.log', $logMsg, FILE_APPEND);
    
    header("Location: admin_edit.php?id=$docId");
    exit;
}

// ============================================
// SAVE DRAFT
// ============================================
if ($action === 'save_draft') {
    $draftContent = $_POST['markdown_content'] ?? '';
    $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['title']);
    $draftFilename = __DIR__ . '/../drafts/' . $safeTitle . '.md';
    file_put_contents($draftFilename, $draftContent);
    
    // Check if it's an API request or regular form submission
    header("Location: admin_edit.php?id=$docId&success=" . urlencode("Brouillon enregistré."));
    exit;
}

// ============================================
// PUBLISH — Save draft, run pipeline, update DB
// ============================================
if ($action === 'publish') {
    $draftContent = $_POST['markdown_content'] ?? '';
    $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['title']);
    $draftFilename = __DIR__ . '/../drafts/' . $safeTitle . '.md';
    file_put_contents($draftFilename, $draftContent);
    
    // ── Helper: strip accents & spaces from a slug ──────────────────
    // Uses iconv transliteration so é→e, à→a, ç→c, etc.
    $slugify = function($text) {
        // Transliterate to ASCII (removes accents)
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii === false) {
            // Fallback: manual replacements for common French chars
            $map = ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a',
                    'ä'=>'a','î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u',
                    'û'=>'u','ü'=>'u','ç'=>'c','É'=>'E','È'=>'E','Ê'=>'E',
                    'À'=>'A','Â'=>'A','Î'=>'I','Ô'=>'O','Ù'=>'U','Û'=>'U','Ç'=>'C'];
            $ascii = strtr($text, $map);
        }
        // Remove spaces and non-alphanumeric chars
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', str_replace(' ', '', $ascii));
    };

    // Exact Sphinx directory casing synchronization (accent-free)
    $levelSlug    = $slugify(str_replace('Licence ', 'L', trim($doc['level_name'])));
    $semesterSlug = $slugify(trim($doc['semester_name']));
    $subjectSlug  = $slugify(trim($doc['subject_name']));

    
    $typeSlug = $doc['type_name'];
    if (strpos($typeSlug, 'corrige_') === 0) {
        $typeSlug = 'corrige/' . $typeSlug; // Handles nested corrige routing
    }
    
    $year         = intval($doc['year']);

    // 1. Route — move draft to Sphinx tree
    // route.py expected format: python route.py <clean_md_file> <level> <sem> <sub_name> <type> <year>
    $routeScript = __DIR__ . '/route.py';
    $draftFileArg = escapeshellarg($draftFilename);
    $cmdRoute = "python " . escapeshellarg($routeScript) . " $draftFileArg " . 
                escapeshellarg($levelSlug) . " " . 
                escapeshellarg($semesterSlug) . " " . 
                escapeshellarg($subjectSlug) . " " . 
                escapeshellarg($typeSlug) . " " . 
                $year . " 2>&1";
    $outRoute = shell_exec($cmdRoute);

    // 2. Build Sphinx ASYNCHRONOUSLY
    // We NO LONGER run $cmdBuild = "python build_docs.py 2>&1"; here!
    
    // 3. Compute the RELATIVE public file_path
    $relativePath = "$levelSlug/$semesterSlug/$subjectSlug/$typeSlug/$year.html";

    // 4. Update document in DB with approved status, relative file_path, and pending worker status
    $updateStmt = $pdo->prepare("UPDATE documents SET status = 'approved', file_path = ?, worker_status = 'pending', locked_by = NULL, raw_markdown = NULL WHERE id = ?");
    $updateStmt->execute([$relativePath, $docId]);
    
    // 4b. Récupérer le pdf_url des doublons existants avant de les supprimer
    $pdfCheckStmt = $pdo->prepare("SELECT pdf_url, original_name FROM documents WHERE subject_id = ? AND type_id = ? AND year_id = ? AND id != ? AND status = 'approved' AND pdf_url IS NOT NULL LIMIT 1");
    $pdfCheckStmt->execute([$doc['subject_id'], $doc['type_id'], $doc['year_id'], $docId]);
    $existingPdf = $pdfCheckStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingPdf) {
        // Transférer le lien PDF et le nom d'origine sur le document nouvellement approuvé
        $copyPdfStmt = $pdo->prepare("UPDATE documents SET pdf_url = ?, original_name = COALESCE(original_name, ?) WHERE id = ?");
        $copyPdfStmt->execute([$existingPdf['pdf_url'], $existingPdf['original_name'], $docId]);
    }

    // Supprimer tout document doublon pour cette même matière/type/année
    $deleteStmt = $pdo->prepare("DELETE FROM documents WHERE subject_id = ? AND type_id = ? AND year_id = ? AND id != ? AND status = 'approved'");
    $deleteStmt->execute([$doc['subject_id'], $doc['type_id'], $doc['year_id'], $docId]);

    // 5. Notification
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
    
    // Add admin notification tracking for the worker
    $logAdmin = $_SESSION['user_id'] ?? 0;
    if ($logAdmin) {
        $pdo->prepare("UPDATE documents SET admin_id = ? WHERE id = ?")->execute([$logAdmin, $docId]);
    }

    // 6. Log
    $logMsg = date('[Y-m-d H:i:s] ') . "APPROVED DOC: {$doc['title']} | ID: $docId | Path: $relativePath\n";
    $logMsg .= "  Route: $outRoute\n  Build: Queued for background worker\n";
    file_put_contents(__DIR__ . '/admin.log', $logMsg, FILE_APPEND);

    @unlink(__DIR__ . '/cache/document_tree.json');
    header("Location: admin.php?tab=documents&success=" . urlencode("Document validé et placé en file d'attente pour publication en arrière-plan."));
    exit;
}

// ============================================
// CANCEL EDIT (Unlock)
// ============================================
if ($action === 'cancel') {
    $pdo->prepare("UPDATE documents SET locked_by = NULL WHERE id = ?")->execute([$docId]);
    header("Location: admin.php?tab=documents");
    exit;
}
?>
