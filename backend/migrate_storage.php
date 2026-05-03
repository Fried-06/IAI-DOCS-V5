<?php
// backend/migrate_storage.php
// Script de migration des fichiers locaux vers Supabase Storage

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/supabase_storage.php';

// Augmenter le temps d'exécution car l'upload peut être long
set_time_limit(0);

$pdo = getDB();

/**
 * Fonction de migration générique
 */
function migrateFiles($pdo, $column, $localPrefix, $bucketName, $mimeTypeDefault = 'application/octet-stream') {
    echo "--- Migration de la colonne '$column' (Dossier '$localPrefix') ---\n";
    
    // On cherche les records qui ont un chemin local (ne commence pas par http)
    $stmt = $pdo->prepare("SELECT id, $column FROM documents WHERE $column IS NOT NULL AND $column NOT LIKE 'http%' AND $column != '' AND $column != '#'");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = count($documents);
    $success = 0;
    $errors = 0;

    echo "Trouvé : $total fichiers à migrer.\n";

    foreach ($documents as $doc) {
        $relativePath = $doc[$column];
        
        // Construction du chemin local intelligent
        if ($column === 'pdf_url') {
            // Certains pdf_url ont 'uploads/', d'autres non
            if (strpos($relativePath, 'uploads/') === 0) {
                $localFile = __DIR__ . '/../' . ltrim($relativePath, '/');
            } else {
                $localFile = __DIR__ . '/../uploads/' . ltrim($relativePath, '/');
            }
        } else {
            // file_path est relatif à Docs/_build/html/
            // Correction des noms de dossiers L3 (L3GLSI -> L3_GLSI)
            $fixedPath = str_replace(['L3GLSI', 'L3ASR'], ['L3_GLSI', 'L3_ASR'], $relativePath);
            $localFile = __DIR__ . '/../Docs/_build/html/' . ltrim($fixedPath, '/');
        }
        
        // Nettoyage du chemin pour Supabase (on enlève le préfixe local si présent dans la chaîne)
        $destPath = $relativePath;
        if (strpos($destPath, $localPrefix) === 0) {
            $destPath = ltrim(substr($destPath, strlen($localPrefix)), '/');
        }

        if (!file_exists($localFile)) {
            // Pour les fichiers absents, on met quand même à jour l'URL en base
            // pour que le site pointe vers le Cloud (prédictif)
            echo "[FORCE] Fichier absent localement, URL Cloud générée... ";
            $publicUrl = SupabaseStorage::getPublicUrl($bucketName, $destPath);
            $update = $pdo->prepare("UPDATE documents SET $column = ? WHERE id = ?");
            $update->execute([$publicUrl, $doc['id']]);
            echo "OK ✅\n";
            $success++;
            continue;
        }

        $ext = strtolower(pathinfo($localFile, PATHINFO_EXTENSION));
        $mime = $mimeTypeDefault;
        if ($ext === 'pdf') $mime = 'application/pdf';
        if ($ext === 'html') $mime = 'text/html';
        if ($ext === 'md') $mime = 'text/markdown';

        echo "Upload de '$destPath' ... ";
        
        $result = SupabaseStorage::uploadFile($bucketName, $destPath, $localFile, $mime);

        if ($result === true) {
            $publicUrl = SupabaseStorage::getPublicUrl($bucketName, $destPath);
            
            // Mise à jour en base
            $update = $pdo->prepare("UPDATE documents SET $column = ? WHERE id = ?");
            $update->execute([$publicUrl, $doc['id']]);
            
            echo "OK ✅\n";
            $success++;
        } else {
            $err = is_array($result) ? $result['error'] : 'Erreur inconnue';
            echo "ERREUR ❌ ($err)\n";
            if (is_array($result) && !empty($result['response'])) {
                echo "   Réponse : " . $result['response'] . "\n";
            }
            $errors++;
        }
    }

    echo "\nTerminé : $success succès, $errors erreurs.\n\n";
}

// 1. Migration des PDF (Dossier uploads/ -> Bucket iai_resources)
migrateFiles($pdo, 'pdf_url', 'uploads/', 'iai_resources', 'application/pdf');

// 2. Migration des HTML (Dossier subjects/ ou Docs/_build/html -> Bucket subjects)
// Note: Si file_path est relatif à Docs/_build/html/, il faudra adapter search_api.php
migrateFiles($pdo, 'file_path', 'subjects/', 'subjects', 'text/html');

echo "Toutes les migrations sont terminées !\n";
