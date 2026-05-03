<?php
// backend/sphinx_worker.php
// Ce script doit être lancé régulièrement (toutes les minutes via cron ou Tâche Planifiée Windows)
require_once __DIR__ . '/db.php';

$pdo = getDB();

try {
    // Selectionner les documents en file d'attente
    $stmt = $pdo->query("SELECT id, title, admin_id FROM documents WHERE worker_status = 'pending'");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($docs)) {
        echo "[" . date('H:i:s') . "] Aucun document en attente.\n";
        exit;
    }

    $docIds = array_column($docs, 'id');
    $placeholders = implode(',', array_fill(0, count($docIds), '?'));

    // Marquer comme 'building'
    $updateStmt = $pdo->prepare("UPDATE documents SET worker_status = 'building' WHERE id IN ($placeholders)");
    $updateStmt->execute($docIds);

    echo "[" . date('H:i:s') . "] Lancement de Sphinx pour " . count($docs) . " documents...\n";
    
    // Lancer Sphinx
    $buildScript = __DIR__ . '/build_docs.py';
    $cmd = "python " . escapeshellarg($buildScript) . " 2>&1";
    $output = shell_exec($cmd);

    // Vérifier globalement le succès
    $success = (strpos($output, 'Sphinx build successful') !== false) || (strpos($output, 'build succeeded') !== false);

    if ($success) {
        echo "[" . date('H:i:s') . "] BUILD SUCCESS. Début de l'upload vers Supabase...\n";
        
        require_once __DIR__ . '/supabase_storage.php';

        foreach ($docs as $doc) {
            // Récupérer les détails pour construire le chemin
            $q = $pdo->prepare("
                SELECT l.name as level_name, s.name as semester_name, sub.name as subject_name, t.name as type_name, y.year 
                FROM documents d
                JOIN subjects sub ON d.subject_id = sub.id
                JOIN semesters s ON sub.semester_id = s.id
                JOIN levels l ON s.level_id = l.id
                JOIN document_types t ON d.type_id = t.id
                JOIN years y ON d.year_id = y.id
                WHERE d.id = ?
            ");
            $q->execute([$doc['id']]);
            $meta = $q->fetch(PDO::FETCH_ASSOC);

            if ($meta) {
                // Construction du chemin local (identique à la logique Sphinx)
                $level = str_replace(' ', '_', $meta['level_name']);
                $semester = str_replace(' ', '', $meta['semester_name']);
                $subject = str_replace([' ', '/'], ['_', '_'], $meta['subject_name']);
                $type = $meta['type_name'];
                $year = $meta['year'];

                $relativePath = "$level/$semester/$subject/$type/$year.html";
                $localHtml = __DIR__ . "/../Docs/_build/html/$relativePath";

                if (file_exists($localHtml)) {
                    echo "   Upload de $relativePath ... ";
                    $upload = SupabaseStorage::uploadFile('subjects', $relativePath, $localHtml, 'text/html');
                    
                    if ($upload === true) {
                        $publicUrl = SupabaseStorage::getPublicUrl('subjects', $relativePath);
                        $pdo->prepare("UPDATE documents SET file_path = ?, worker_status = 'success', worker_error = NULL WHERE id = ?")
                            ->execute([$publicUrl, $doc['id']]);
                        echo "OK ✅\n";
                    } else {
                        echo "ERREUR ❌\n";
                        $pdo->prepare("UPDATE documents SET worker_status = 'error', worker_error = 'Upload failed' WHERE id = ?")
                            ->execute([$doc['id']]);
                    }
                } else {
                    echo "   [SKIP] Fichier introuvable : $localHtml\n";
                }
            }

            // Notifier l'admin
            if ($doc['admin_id']) {
                $pdo->prepare("INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'system', ?, ?)")->execute([
                    $doc['admin_id'], $doc['id'], 'Sphinx Terminé', 'Le document "' . $doc['title'] . '" est maintenant disponible sur le Cloud.'
                ]);
            }
        }
    } else {
        // Enregistrer l'erreur
        $errorStmt = $pdo->prepare("UPDATE documents SET worker_status = 'error', worker_error = ? WHERE id IN ($placeholders)");
        $params = $docIds;
        array_unshift($params, $output); // Placer le message d'erreur en 1er parametre
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
