<?php
// backend/sync_full_docs.php — Synchronisation complète du rendu Sphinx vers Supabase
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/supabase_storage.php';

$sourceDir = __DIR__ . '/../Docs/_build/html';
$bucketName = 'subjects';

if (!is_dir($sourceDir)) {
    die("Erreur : Le dossier source Sphinx n'existe pas ($sourceDir).\n");
}

echo "--- Démarrage de la synchronisation complète vers Supabase ($bucketName) ---\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$success = 0;
$errors = 0;
$skipped = 0;

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $filePath = $file->getRealPath();
        $relativePath = ltrim(str_replace(realpath($sourceDir), '', $filePath), DIRECTORY_SEPARATOR);
        $destPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        
        // Détermination du type MIME
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        
        switch ($ext) {
            case 'html': $mime = 'text/html'; break;
            case 'css':  $mime = 'text/css'; break;
            case 'js':   $mime = 'application/javascript'; break;
            case 'png':  $mime = 'image/png'; break;
            case 'jpg':
            case 'jpeg': $mime = 'image/jpeg'; break;
            case 'gif':  $mime = 'image/gif'; break;
            case 'svg':  $mime = 'image/svg+xml'; break;
            case 'json': $mime = 'application/json'; break;
            case 'txt':  $mime = 'text/plain'; break;
        }

        echo "Upload de '$destPath' ($mime) ... ";
        
        $result = SupabaseStorage::uploadFile($bucketName, $destPath, $filePath, $mime);
        
        if ($result === true) {
            echo "OK ✅\n";
            $success++;
        } else {
            echo "ERREUR ❌\n";
            print_r($result);
            $errors++;
        }
    }
}

echo "\n--- Synchronisation terminée ---\n";
echo "Succès : $success | Erreurs : $errors\n";
?>
