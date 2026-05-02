<?php
// backend/upload.php — Document upload with MD5 duplicate detection and instant PDF availability
// Receives IDs (subject_id, type_id, year_id) from the dynamic form.

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contribute.html");
    exit;
}

$title       = trim($_POST['title'] ?? '');
$subjectId   = intval($_POST['subject_id'] ?? 0);
$typeId      = intval($_POST['type_id'] ?? 0);
$yearId      = intval($_POST['year_id'] ?? 0);
$description = trim($_POST['description'] ?? '');

// --- Post Size Check (Detect if file too large for server config) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo "<script>alert('Erreur: Le fichier est trop lourd pour la configuration du serveur (post_max_size).'); window.history.back();</script>";
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

// --- Prepare File or Raw Markdown ---
$hasMarkdown = isset($_POST['has_markdown']) && $_POST['has_markdown'] === 'on';
$rawMarkdown = isset($_POST['raw_markdown']) ? trim($_POST['raw_markdown']) : null;

$fileName = '';
$uniqueFileName = '';
$fileExtension = '';
$fileHash = '';
$pdfUrl = null;
$uploadDir = __DIR__ . '/../uploads/';

if ($hasMarkdown && !empty($rawMarkdown)) {
    // Admin or user pasted raw markdown directly
    $fileName = 'markdown_direct.md';
    $uniqueFileName = 'markdown_direct_' . time() . '.md';
    $fileHash = md5($rawMarkdown);
} else {
    // Regular File Upload
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== 0) {
        $errCode = $_FILES['document']['error'] ?? 0;
        $errMessage = "Erreur lors de l'upload du fichier (Code $errCode).";
        
        if ($errCode === 1 || $errCode === 2) {
            $errMessage = "Le fichier dépasse la limite upload_max_filesize du serveur.";
        } elseif ($errCode === 4) {
            $errMessage = "Aucun fichier n'a été sélectionné.";
        }
        
        echo "<script>alert('" . addslashes($errMessage) . "'); window.history.back();</script>";
        exit;
    }

    $fileName    = $_FILES['document']['name'];
    $fileTmpName = $_FILES['document']['tmp_name'];
    
    $allowedExtensions = ['pdf', 'docx'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "<script>alert('Erreur: Seuls les fichiers PDF et DOCX sont autorisés.'); window.history.back();</script>";
        exit;
    }

    // Calculate MD5 hash of the uploaded file
    $fileHash = md5_file($fileTmpName);
}

// --- Duplicate Detection (MD5 Hash) ---
try {
    $pdo = getDB();
    $dupStmt = $pdo->prepare("SELECT id FROM documents WHERE file_hash = ? LIMIT 1");
    $dupStmt->execute([$fileHash]);
    if ($dupStmt->fetch()) {
        echo "<script>alert('Erreur: Ce document exact (même contenu) existe déjà sur la plateforme.'); window.history.back();</script>";
        exit;
    }
} catch (\PDOException $e) {
    error_log("Upload hash check error: " . $e->getMessage());
    echo "<script>alert('Erreur système lors de la vérification des doublons.'); window.history.back();</script>";
    exit;
}

// --- Save File (if not raw markdown) ---
if (!$hasMarkdown) {
    // Get subject and year for folder structure
    $subjectSlug = 'divers';
    $yearStr = '0000';
    try {
        $pdo = getDB();
        $metaStmt = $pdo->prepare("SELECT s.name AS subject_name, y.year FROM subjects s CROSS JOIN years y WHERE s.id = ? AND y.id = ?");
        $metaStmt->execute([$subjectId, $yearId]);
        if ($meta = $metaStmt->fetch(PDO::FETCH_ASSOC)) {
            $subjectSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['subject_name']), '_'));
            $yearStr = $meta['year'];
        }
    } catch (\PDOException $e) {
        // Ignore, will use 'divers/0000'
    }

    $structuredDir = $uploadDir . $subjectSlug . '/' . $yearStr . '/';
    if (!is_dir($structuredDir)) {
        mkdir($structuredDir, 0755, true);
    }

    // Generate safe unique filename
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
    $uniqueFileName = $safeBaseName . '_' . time() . '.' . $fileExtension;
    $destination = $structuredDir . $uniqueFileName;

    if (!move_uploaded_file($_FILES['document']['tmp_name'], $destination)) {
        echo "<script>alert('Erreur système lors de l\\'enregistrement du fichier.'); window.history.back();</script>";
        exit;
    }
    
    // Set pdf_url (local path for now, easily changeable to Supabase Storage later)
    $pdfUrl = 'uploads/' . $subjectSlug . '/' . $yearStr . '/' . $uniqueFileName;
}

// --- Insert into DB ---
try {
    $stmt = $pdo->prepare(
        "INSERT INTO documents (title, description, user_id, subject_id, type_id, year_id, original_name, filename, status, raw_markdown, file_hash, pdf_url) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)"
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
        empty($rawMarkdown) ? null : $rawMarkdown,
        $fileHash,
        $pdfUrl
    ]);

    echo "<script>alert('Document uploadé avec succès. Le PDF est immédiatement disponible. Le HTML est en attente de génération.'); window.location.href='../profile.php';</script>";
    exit;

} catch (\PDOException $e) {
    error_log("Upload DB insert error: " . $e->getMessage());
    echo "<script>alert('Erreur système lors de l\\'enregistrement en base de données.'); window.history.back();</script>";
    exit;
}
?>
