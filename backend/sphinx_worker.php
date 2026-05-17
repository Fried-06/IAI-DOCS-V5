<?php
// backend/sphinx_worker.php
// Ce script doit être lancé régulièrement (toutes les minutes via cron ou Tâche Planifiée Windows)
require_once __DIR__ . '/db.php';

$pdo = getDB();

try {
    // Selectionner les documents en file d'attente
    $stmt = $pdo->query("SELECT id, title, admin_id FROM documents WHERE status = 'approved' AND worker_status = 'pending'");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($docs)) {
        echo "[" . date('H:i:s') . "] Aucun document en attente.\n";
        exit;
    }

    $docIds = array_column($docs, 'id');
    $placeholders = implode(',', array_fill(0, count($docIds), '?'));

    // Marquer les documents en cours de compilation ('building')
    $updateStmt = $pdo->prepare("UPDATE documents SET worker_status = 'building' WHERE id IN ($placeholders)");
    $updateStmt->execute($docIds);

    echo "[" . date('H:i:s') . "] Lancement de Sphinx pour " . count($docs) . " document(s) spécifique(s) (IDs: " . implode(', ', $docIds) . ")...\n";
    
    // Lancer Sphinx avec la liste des IDs à compiler
    $buildScript = __DIR__ . '/build_docs.py';
    $cmd = "python " . escapeshellarg($buildScript) . " " . implode(' ', array_map('intval', $docIds)) . " 2>&1";
    $output = shell_exec($cmd);

    // Vérifier globalement le succès de la compilation
    $success = (strpos($output, 'Sphinx build successful') !== false) || (strpos($output, 'build succeeded') !== false);

    if ($success) {
        echo "[" . date('H:i:s') . "] BUILD SUCCESS. Mise à jour des liens Cloud...\n";
        
        require_once __DIR__ . '/supabase_storage.php';

        foreach ($docs as $doc) {
            // Récupérer le file_path déjà correctement slugifié lors de la publication
            $q = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
            $q->execute([$doc['id']]);
            $dbDoc = $q->fetch(PDO::FETCH_ASSOC);

            if ($dbDoc && !empty($dbDoc['file_path'])) {
                $relativePath = $dbDoc['file_path'];
                
                // Si c'est déjà une URL absolue, on extrait la partie relative à partir de 'subjects/'
                if (str_contains($relativePath, 'http')) {
                    $parsedUrl = parse_url($relativePath, PHP_URL_PATH);
                    if (str_contains($parsedUrl, 'subjects/')) {
                        $relativePath = explode('subjects/', $parsedUrl)[1];
                    } else {
                        $relativePath = ltrim($parsedUrl, '/');
                    }
                }
                
                // S'assurer d'utiliser l'extension HTML
                $relativePath = str_replace('.md', '.html', $relativePath);
                
                // Récupérer l'URL cloud finale publique
                $publicUrl = SupabaseStorage::getPublicUrl('subjects', $relativePath);
                
                // Enregistrer l'URL finale et passer le statut à 'success'
                $pdo->prepare("UPDATE documents SET file_path = ?, worker_status = 'success', worker_error = NULL WHERE id = ?")
                    ->execute([$publicUrl, $doc['id']]);
            }

            // Notifier l'admin de la réussite
            if ($doc['admin_id']) {
                $pdo->prepare("INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'system', ?, ?)")->execute([
                    $doc['admin_id'], $doc['id'], 'Sphinx Terminé', 'Le document "' . $doc['title'] . '" est maintenant disponible sur le Cloud.'
                ]);
            }
        }
    } else {
        // Enregistrer l'erreur et notifier
        $errorStmt = $pdo->prepare("UPDATE documents SET worker_status = 'error', worker_error = ? WHERE id IN ($placeholders)");
        $params = $docIds;
        array_unshift($params, $output); // Mettre la trace d'erreur en 1er paramètre
        $errorStmt->execute($params);
        echo "[" . date('H:i:s') . "] BUILD ERROR.\n";
        
        foreach ($docs as $doc) {
            if ($doc['admin_id']) {
                $pdo->prepare("INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'error', ?, ?)")->execute([
                    $doc['admin_id'], $doc['id'], 'Erreur Sphinx', 'Erreur lors de la compilation du document "' . $doc['title'] . '".'
                ]);
            }
        }
    }
} catch (Exception $e) {
    echo "Erreur fatale: " . $e->getMessage() . "\n";
}
?>
