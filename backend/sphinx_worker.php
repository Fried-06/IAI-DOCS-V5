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
        $successStmt = $pdo->prepare("UPDATE documents SET worker_status = 'success', worker_error = NULL WHERE id IN ($placeholders)");
        $successStmt->execute($docIds);
        echo "[" . date('H:i:s') . "] BUILD SUCCESS.\n";
        
        // Notifier les administrateurs
        foreach ($docs as $doc) {
            if ($doc['admin_id']) {
                $pdo->prepare("INSERT INTO notifications (user_id, document_id, type, title, message) VALUES (?, ?, 'system', ?, ?)")->execute([
                    $doc['admin_id'], $doc['id'], 'Sphinx Terminé', 'Le document "' . $doc['title'] . '" a été compilé en arrière-plan avec succès.'
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
