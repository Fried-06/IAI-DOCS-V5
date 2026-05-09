<?php
// backend/sync_full_docs.php — Synchronisation Intelligente (Incrémentale) vers Supabase
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/supabase_storage.php';

$sourceDir = __DIR__ . '/../Docs/_build/html';
$bucketName = 'subjects';
$cacheFile = __DIR__ . '/sync_cache.json';

if (!is_dir($sourceDir)) {
    die("Erreur : Le dossier source Sphinx n'existe pas ($sourceDir).\n");
}

echo "--- Démarrage de la synchronisation intelligente vers Supabase ($bucketName) ---\n";

// Charger le cache précédent
$cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
$newCache = $cache;

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
        
        // Vérifier si le fichier a changé (via MD5)
        $currentMd5 = md5_file($filePath);
        if (isset($cache[$destPath]) && $cache[$destPath] === $currentMd5) {
            $skipped++;
            continue; // Le fichier n'a pas changé, on l'ignore
        }

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
            case 'woff': case 'woff2': case 'ttf': $mime = 'font/' . $ext; break;
        }

        echo "Upload de '$destPath' ($mime) ... ";
        
        $result = SupabaseStorage::uploadFile($bucketName, $destPath, $filePath, $mime);
        
        if ($result === true) {
            echo "OK ✅\n";
            $success++;
            $newCache[$destPath] = $currentMd5; // Mise à jour du cache
        } else {
            echo "ERREUR ❌\n";
            $errors++;
        }
    }
}

// Sauvegarder le nouveau cache
file_put_contents($cacheFile, json_encode($newCache, JSON_PRETTY_PRINT));

echo "\n--- Synchronisation terminée ---\n";
echo "Fichiers ignorés (inchangés) : $skipped\n";
echo "Nouveaux / Modifiés : $success ✅ | Erreurs : $errors ❌\n";
?>
