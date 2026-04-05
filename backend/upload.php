<?php
// backend/upload.php — Document upload with relational DB storage
// Receives IDs (subject_id, type_id, year_id) from the dynamic form.

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contribute.html");
    exit;
}

$title      = trim($_POST['title'] ?? '');
$subjectId  = intval($_POST['subject_id'] ?? 0);
$typeId     = intval($_POST['type_id'] ?? 0);
$yearId     = intval($_POST['year_id'] ?? 0);
$description = trim($_POST['description'] ?? '');

// --- Post Size Check (Detect if file too large for server config) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo "<script>alert('Erreur: Le fichier est trop lourd pour le serveur (limite PHP post_max_size). Veuillez réduire la taille du document.'); window.history.back();</script>";
    exit;
}

// --- Validation ---
if (empty($title)) {
    echo "<script>alert('Erreur: Le titre est requis.'); window.history.back();</script>";
    exit;
}
if ($subjectId <= 0 || $typeId <= 0 || $yearId <= 0) {
    echo "<script>alert('Erreur: Veuillez sélectionner la matière, le type de document et l\\'année.'); window.history.back();</script>";
    exit;
}

// --- Duplicate Detection (DB level) ---
try {
    $pdo = getDB();

    $dupStmt = $pdo->prepare(
        "SELECT id, file_path FROM documents 
         WHERE subject_id = ? AND type_id = ? AND year_id = ? AND status = 'approved' 
         LIMIT 1"
    );
    $dupStmt->execute([$subjectId, $typeId, $yearId]);
    $existingDoc = $dupStmt->fetch(PDO::FETCH_ASSOC);

    $isDuplicate = false;

    if ($existingDoc) {
        // Default: assume NOT a duplicate until proven otherwise
        $isDuplicate = false;
        if (!empty($existingDoc['file_path'])) {
            $htmlPath = __DIR__ . '/../Docs/_build/html/' . ltrim($existingDoc['file_path'], '/');
            if (file_exists($htmlPath)) {
                $htmlContent = file_get_contents($htmlPath);
                // It IS a duplicate ONLY if the file exists AND has real content
                if (strpos($htmlContent, 'Aucun contenu disponible pour le moment') === false &&
                    strpos($htmlContent, 'Contenu en attente de contribution') === false) {
                    $isDuplicate = true; // Real content exists, block upload
                }
                // Otherwise: file exists but is a placeholder → allow overwrite
            }
            // If file doesn't exist on disk → not a duplicate, allow upload
        }
        // If file_path is empty → not a duplicate, allow upload
    } else {
        // Not in DB, but check if predicted file exists and has real content
        $metaStmt = $pdo->prepare("
            SELECT s.name AS s_name, sem.name AS sem_name, l.name AS l_name, dt.name AS type_name, y.year
            FROM subjects s
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN levels l ON sem.level_id = l.id
            CROSS JOIN document_types dt
            CROSS JOIN years y
            WHERE s.id = ? AND dt.id = ? AND y.id = ?
        ");
        $metaStmt->execute([$subjectId, $typeId, $yearId]);
        if ($meta = $metaStmt->fetch(PDO::FETCH_ASSOC)) {
            $subjectSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['s_name']), '_'));
            $levelSlug = preg_replace('/\s+/', '_', $meta['l_name']);
            $semesterSlug = preg_replace('/\s+/', '', strtolower($meta['sem_name']));
            
            $predictedPath = "$levelSlug/$semesterSlug/$subjectSlug/{$meta['type_name']}/{$meta['year']}.html";
            $htmlPath = __DIR__ . '/../Docs/_build/html/' . $predictedPath;
            if (file_exists($htmlPath)) {
                $htmlContent = file_get_contents($htmlPath);
                if (strpos($htmlContent, 'Aucun contenu disponible pour le moment') === false &&
                    strpos($htmlContent, 'Contenu en attente de contribution') === false) {
                    $isDuplicate = true; // Contains real content without DB entry!
                }
            }
        }
    }

    if ($isDuplicate) {
        echo "<script>alert('Erreur: Un document existant avec du contenu réel a déjà été validé pour cette matière, ce type et cette année.'); window.history.back();</script>";
        exit;
    }
} catch (\PDOException $e) {
    error_log("Upload duplicate check error: " . $e->getMessage());
    echo "<script>alert('Erreur système lors de la vérification.'); window.history.back();</script>";
    exit;
}

// --- File Upload or Raw Markdown ---
$hasMarkdown = isset($_POST['has_markdown']) && $_POST['has_markdown'] === 'on';
$rawMarkdown = isset($_POST['raw_markdown']) ? trim($_POST['raw_markdown']) : null;

$fileName = '';
$uniqueFileName = '';
$fileExtension = '';

if ($hasMarkdown && !empty($rawMarkdown)) {
    // Admin or user pasted raw markdown directly
    $fileName = 'markdown_direct.md';
    $uniqueFileName = 'markdown_direct_' . time() . '.md';
    
} else {
    // Regular File Upload
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== 0) {
        $errCode = $_FILES['document']['error'] ?? 0;
        $errMessage = "Erreur lors de l'upload du fichier (Code $errCode).";
        
        if ($errCode === 1 || $errCode === 2) {
            $errMessage = "Le fichier est trop lourd (limite serveur dépassée). Veuillez utiliser un fichier de moins de 2 Mo.";
        } elseif ($errCode === 4) {
            $errMessage = "Aucun fichier n'a été sélectionné.";
        }
        
        echo "<script>alert('" . addslashes($errMessage) . "'); window.history.back();</script>";
        exit;
    }

    $fileName    = $_FILES['document']['name'];
    $fileTmpName = $_FILES['document']['tmp_name'];
    $fileSize    = $_FILES['document']['size'];

    $allowedExtensions = ['pdf', 'docx'];
    $maxFileSize = 2 * 1024 * 1024; // 2 MB

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "<script>alert('Erreur: Seuls les fichiers PDF et DOCX sont autorisés.'); window.history.back();</script>";
        exit;
    }
    if ($fileSize > $maxFileSize) {
        echo "<script>alert('Erreur: Le fichier dépasse la taille maximale autorisée (2 Mo).'); window.history.back();</script>";
        exit;
    }

    // Create uploads directory
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate safe unique filename
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
    $uniqueFileName = $safeBaseName . '_' . time() . '.' . $fileExtension;
    $destination = $uploadDir . $uniqueFileName;

    if (!move_uploaded_file($fileTmpName, $destination)) {
        echo "<script>alert('Erreur système lors de l\\'enregistrement du fichier.'); window.history.back();</script>";
        exit;
    }
}

// --- Insert into DB ---
try {
    $stmt = $pdo->prepare(
        "INSERT INTO documents (title, description, user_id, subject_id, type_id, year_id, original_name, filename, status, raw_markdown) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
    );
    $stmt->execute([
        $title,
        $description,
        $_SESSION['user_id'] ?? null,
        $subjectId,
        $typeId,
        $yearId,
        $fileName,
        $uniqueFileName,
        empty($rawMarkdown) ? null : $rawMarkdown
    ]);

    echo "<script>alert('Document uploadé avec succès. En attente de validation.'); window.location.href='../profile.php';</script>";
    exit;

} catch (\PDOException $e) {
    error_log("Upload DB insert error: " . $e->getMessage());
    echo "<script>alert('Erreur système lors de l\\'enregistrement en base de données.'); window.history.back();</script>";
    exit;
}
?>
