<?php
// backend/admin_edit.php — Interface to review and edit AI generated draft Markdown
session_start();
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    die("Accès refusé.");
}

require_once __DIR__ . '/db.php';

$docId = intval($_GET['id'] ?? 0);
if ($docId <= 0) {
    die("Invalid ID");
}

$pdo = getDB();
$stmt = $pdo->prepare(
    "SELECT d.*, 
            s.name AS subject_name, 
            sem.name AS semester_name, 
            l.name AS level_name,
            dt.name AS type_name, 
            y.year
     FROM documents d
     JOIN subjects s ON d.subject_id = s.id
     JOIN semesters sem ON s.semester_id = sem.id
     JOIN levels l ON sem.level_id = l.id
     JOIN document_types dt ON d.type_id = dt.id
     JOIN years y ON d.year_id = y.id
     WHERE d.id = ? AND d.status = 'pending'"
);
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    die("Document not found or already processed.");
}

$currentUserId = $_SESSION['user_id'] ?? 0;

// Lock check
$lockTimeoutMins = 15;
if (!empty($doc['locked_by']) && (int)$doc['locked_by'] !== $currentUserId) {
    $lockedTime = strtotime($doc['locked_at']);
    if ((time() - $lockedTime) < ($lockTimeoutMins * 60)) {
        die("Ce document est actuellement en cours d'édition par un autre administrateur (ID: {$doc['locked_by']}). Veuillez réessayer dans quelques minutes.");
    }
}

// Acquire lock
$pdo->prepare("UPDATE documents SET locked_by = ?, locked_at = NOW() WHERE id = ?")->execute([$currentUserId, $docId]);

$safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['title']);
$draftFile = __DIR__ . '/../drafts/' . $safeTitle . '.md';

$draftContent = "";
if (!empty($doc['raw_markdown'])) {
    $draftContent = $doc['raw_markdown'];
} elseif (file_exists($draftFile)) {
    $draftContent = file_get_contents($draftFile);
} else {
    die("Brouillon introuvable. Veuillez d'abord utiliser Docling ou l'IA.");
}

$pdfUrl = empty($doc['filename']) || $doc['filename'] === 'markdown_direct.md' ? null : '../uploads/' . htmlspecialchars($doc['filename']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Editer le brouillon - IAI DOCS</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #040c18; color: #c8ddf2; display: flex; flex-direction: column; height: 100vh; }
        
        .header { background: #07111f; padding: 15px 20px; border-bottom: 1px solid #1e3558; display: flex; justify-content: space-between; align-items: center; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .header h1 { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: #fff; letter-spacing: 0.05em; margin-bottom: 0; line-height: 1; }
        .header .doc-info { color: #00e5c4; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; margin-top: 4px; }
        
        .actions { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; cursor: pointer; border-radius: 6px; font-weight: 500; font-size: 0.85rem; font-family: 'JetBrains Mono', monospace; text-transform: uppercase; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-secondary { background: transparent; color: #c8ddf2; border: 1px solid #1e3558; } .btn-secondary:hover { border-color: #00e5c4; color: #00e5c4; }
        .btn-primary { background: rgba(0,229,196,0.1); color: #00e5c4; border: 1px solid #00e5c4; } .btn-primary:hover { background: #00e5c4; color: #0a1628; box-shadow: 0 0 15px rgba(0,229,196,0.3); }
        .btn-success { background: linear-gradient(135deg, #00e5c4, #3b82f6); color: #fff; font-weight: 700; border: none; } .btn-success:hover { box-shadow: 0 5px 15px rgba(0,229,196,0.4); transform: translateY(-2px); }
        .btn-regen { background: rgba(168,85,247,0.1); color: #a855f7; border: 1px solid #a855f7; } .btn-regen:hover { background: #a855f7; color: white; box-shadow: 0 0 10px rgba(168,85,247,0.4); }
        
        .container { display: flex; flex: 1; overflow: hidden; padding: 15px; gap: 15px; }
        .panel { flex: 1; background: #07111f; border-radius: 12px; border: 1px solid #1e3558; display: flex; flex-direction: column; overflow: hidden; box-shadow: inset 0 0 20px rgba(0,0,0,0.2); }
        
        .panel-header { background: #0a1628; padding: 12px 15px; border-bottom: 1px solid #1e3558; font-weight: 600; font-size: 0.85rem; font-family: 'JetBrains Mono', monospace; color: #a855f7; display: flex; align-items: center; gap: 8px; }
        
        .pdf-frame { flex: 1; width: 100%; border: none; background: #fff; }
        .editor-container { flex: 1; display: flex; flex-direction: column; background: #040c18; }
        
        textarea { flex: 1; width: 100%; border: none; background: transparent; padding: 20px; font-family: 'JetBrains Mono', monospace; font-size: 0.95rem; resize: none; line-height: 1.6; color: #e2e8f0; outline: none; }
        textarea::-webkit-scrollbar { width: 8px; }
        textarea::-webkit-scrollbar-track { background: #040c18; }
        textarea::-webkit-scrollbar-thumb { background: #1e3558; border-radius: 4px; }
        textarea::-webkit-scrollbar-thumb:hover { background: #00e5c4; }
    </style>
</head>
<body>
    <form method="POST" action="admin_action.php" id="editForm" style="display:contents;">
        <input type="hidden" name="id" value="<?= $docId ?>">
        <input type="hidden" name="action" id="actionInput" value="save_draft">
        
        <div class="header">
            <div>
                <h1>📝 Éditeur de document IA</h1>
                <div class="doc-info">
                    <?= htmlspecialchars($doc['title']) ?> | <?= htmlspecialchars($doc['subject_name']) ?> | <?= htmlspecialchars($doc['type_name']) ?> <?= htmlspecialchars($doc['year']) ?>
                </div>
            </div>
            <div class="actions">
                <button type="button" class="btn btn-secondary" onclick="submitForm('cancel')" title="Annuler et libérer le document">Annuler</button>
                <button type="button" class="btn btn-regen" onclick="if(confirm('Régénérer le markdown avec IA Vision ? Le brouillon actuel sera écrasé.')) regenerateAI()" title="Relancer Gemini Vision Direct">🧠 Régénérer IA</button>
                <button type="button" class="btn btn-primary" onclick="submitForm('save_draft')">💾 Enregistrer Brouillon</button>
                <button type="button" class="btn btn-success" onclick="if(confirm('Êtes-vous sûr de vouloir publier ceci ?')) submitForm('publish')">🚀 Valider et Publier</button>
            </div>
        </div>

        <div class="container">
            <div class="panel">
                <div class="panel-header">📄 Document Original</div>
                <?php if ($pdfUrl): ?>
                    <iframe src="<?= $pdfUrl ?>" class="pdf-frame"></iframe>
                <?php else: ?>
                    <div style="padding: 20px; color: #4a6a8a; text-align: center; height: 100%; display: flex; align-items: center; justify-content: center; font-family: 'JetBrains Mono', monospace;">
                        Aucun PDF. (Markdown transmis directement par l'utilisateur)
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="panel">
                <div class="panel-header">✍️ Markdown Généré</div>
                <div class="editor-container">
                    <textarea name="markdown_content" spellcheck="false"><?= htmlspecialchars($draftContent) ?></textarea>
                </div>
            </div>
        </div>
    </form>
    
    <script>
        function submitForm(actionValue) {
            document.getElementById('actionInput').value = actionValue;
            var form = document.getElementById('editForm');
            if(actionValue === 'publish') {
                var btn = document.querySelector('.btn-success');
                btn.innerText = 'Publication en cours...';
                btn.disabled = true;
            } else if (actionValue === 'save_draft') {
                var btn = document.querySelector('.btn-primary');
                btn.innerText = 'Enregistrement...';
                btn.disabled = true;
            }
            form.submit();
        }

        function regenerateAI() {
            // Create a hidden form that submits generate_ai action
            var regenForm = document.createElement('form');
            regenForm.method = 'POST';
            regenForm.action = 'admin_action.php';
            
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = '<?= $docId ?>';
            regenForm.appendChild(idInput);
            
            var actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'generate_ai';
            regenForm.appendChild(actionInput);
            
            document.body.appendChild(regenForm);
            
            var btn = document.querySelector('.btn-regen');
            btn.innerText = 'Régénération IA...';
            btn.disabled = true;
            
            regenForm.submit();
        }
    </script>
</body>
</html>
