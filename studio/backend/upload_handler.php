<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
    exit;
}

require_once __DIR__ . '/../../backend/db.php';
$pdo = getDB();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['private_doc'])) {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
    exit;
}

$file = $_FILES['private_doc'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Erreur lors du transfert.']);
    exit;
}

// Validation
$allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
$originalName = basename($file['name']);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExts)) {
    echo json_encode(['success' => false, 'error' => 'Format non supporté.']);
    exit;
}

if ($file['size'] > 15 * 1024 * 1024) { // 15MB limit
    echo json_encode(['success' => false, 'error' => 'Fichier trop lourd (max 15Mo).']);
    exit;
}

// Secure Storage
$uploadDir = __DIR__ . '/../private_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$storageName = uniqid('stu_') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$targetPath = $uploadDir . $storageName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Save to isolated DB
    try {
        $stmt = $pdo->prepare("INSERT INTO studio_user_docs (user_id, original_name, storage_name, file_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $originalName, $storageName, $ext]);
        
        echo json_encode(['success' => true, 'message' => 'Document protégé et prêt pour l\'IA.']);
    } catch (\PDOException $e) {
        unlink($targetPath); // Rollback file if DB fails
        echo json_encode(['success' => false, 'error' => 'Erreur BDD.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible de sauvegarder le fichier.']);
}
?>
