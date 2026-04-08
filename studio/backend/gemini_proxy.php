<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
    exit;
}

require_once __DIR__ . '/../../backend/db.php';
$pdo = getDB();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Données invalides: action manquante.']);
    exit;
}

// For non-chat actions, documents are required
if ($data['action'] !== 'chat' && empty($data['basket'])) {
    echo json_encode(['success' => false, 'error' => 'Veuillez ajouter des documents au panier pour cette action.']);
    exit;
}

$action = $data['action'];
$basket = $data['basket'];

$filesToProcess = [];

// Resolve paths based on basket items
foreach ($basket as $item) {
    if ($item['type'] === 'private') {
        $stmt = $pdo->prepare("SELECT storage_name FROM studio_user_docs WHERE id = ? AND user_id = ?");
        $stmt->execute([$item['id'], $_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $path = __DIR__ . '/../private_uploads/' . $row['storage_name'];
            if (file_exists($path)) {
                $filesToProcess[] = ['path' => $path];
            }
        }
    } else if ($item['type'] === 'official') {
        // Find document path in global table
        // We know that for official docs, the master file is the Markdown in Docs/ folder
        // The file_path in DB points to '_build/html/.../2026.html'. We need to convert it back to the .md source.
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->execute([$item['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['file_path']) {
            // Rel path is like: L1/Semestre1/Algorithmique/partiel/2026.html
            $mdPath = str_replace('.html', '.md', $row['file_path']);
            $fullPath = realpath(__DIR__ . '/../../Docs/' . $mdPath);
            if ($fullPath && file_exists($fullPath)) {
                $filesToProcess[] = ['path' => $fullPath];
            }
        }
    }
}

// For chat without docs, we still proceed (free conversation)
if (empty($filesToProcess) && $action !== 'chat') {
    echo json_encode(['success' => false, 'error' => 'Aucun document valide trouvé.']);
    exit;
}

// Call Python — pass JSON via stdin to avoid Windows shell escaping issues
$prompt = $data['prompt'] ?? '';
$payload = json_encode(['action' => $action, 'files' => $filesToProcess, 'prompt' => $prompt]);
$scriptPath = __DIR__ . '/gemini_multimodal.py';
$cmd = "python " . escapeshellarg($scriptPath);

set_time_limit(300); // Allow long runs

$descriptors = [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
    2 => ['pipe', 'w'], // stderr
];

$process = proc_open($cmd, $descriptors, $pipes);

if (is_resource($process)) {
    // Write JSON payload to stdin
    fwrite($pipes[0], $payload);
    fclose($pipes[0]);
    
    // Read stdout
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    
    // Read stderr (for debugging)
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    
    proc_close($process);
    
    // Attempt to parse json from python output
    preg_match('/\{[\s\S]*\}/', $output, $matches);
    if (!empty($matches)) {
        echo $matches[0];
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur critique du proxy AI.', 'details' => $output . ' | ' . $stderr]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible de lancer le processus Python.']);
}
?>
