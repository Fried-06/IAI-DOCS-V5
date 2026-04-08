<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../backend/db.php';
$pdo = getDB();

$docId = $_POST['id'] ?? '';
$userId = $_SESSION['user_id'];

if (!$docId) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

// Récupérer le chemin du fichier
$stmt = $pdo->prepare("SELECT file_path FROM studio_user_docs WHERE id = ? AND user_id = ?");
$stmt->execute([$docId, $userId]);
$doc = $stmt->fetch();

if ($doc) {
    // Supprimer le fichier physique (le chemin enregistré est "private_uploads/..." relatif à studio)
    $fullPath = __DIR__ . '/../' . $doc['file_path'];
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    
    // Supprimer de la BDD
    $del = $pdo->prepare("DELETE FROM studio_user_docs WHERE id = ? AND user_id = ?");
    $del->execute([$docId, $userId]);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Document introuvable ou non autorisé']);
}
?>
