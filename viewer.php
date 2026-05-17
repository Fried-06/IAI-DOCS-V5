<?php
// viewer.php — Lecteur de documents Premium avec Sidebar Dynamique SQL & Recherche
$url = $_GET['url'] ?? '';

if (empty($url)) {
    die("Lien du document manquant.");
}

// Détection du thème premium
$theme = $_COOKIE['premium_doc_theme'] ?? $_GET['theme'] ?? 'cosmic';

// LA TRICHE : On transforme :// en / pour que le proxy le lise comme un chemin
$maskedUrl = str_replace('://', '/', $url);
$proxyUrl = "proxy.php/" . $maskedUrl;

// Intégration du thème dans l'URL du proxy
if (!str_contains($proxyUrl, '?')) {
    $proxyUrl .= "?theme=" . urlencode($theme);
} else {
    $proxyUrl .= "&theme=" . urlencode($theme);
}

// Connexion Base de Données pour la Sidebar Dynamique
require_once __DIR__ . '/backend/db.php';
$pdo = getDB();

// 1. Identifier le document actif dans la base de données de manière ultra-précise
$activeDoc = null;
try {
    // Essayer d'abord un match exact très propre
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE file_path = ? OR pdf_url = ? LIMIT 1");
    $stmt->execute([$url, $url]);
    $activeDoc = $stmt->fetch();

    if (!$activeDoc) {
        // Match approximatif de sécurité en extrayant le segment de niveau (ex. L2/Semestre3/...) pour éviter les collisions d'années
        $pathSegment = "";
        if (preg_match('/(L\d+.*)/i', $url, $matches)) {
            $pathSegment = $matches[1];
        }
        
        if (!empty($pathSegment)) {
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE file_path LIKE ? OR pdf_url LIKE ? LIMIT 1");
            $stmt->execute(["%" . $pathSegment, "%" . $pathSegment]);
            $activeDoc = $stmt->fetch();
        }
    }
} catch (Exception $e) {
    // Ignore error
}

$activeTitle = $activeDoc ? $activeDoc['title'] : basename($url);

// 2. Récupérer toute la structure hiérarchique des documents approuvés
$tree = [];
try {
    $treeQuery = "
        SELECT 
            d.id AS doc_id,
            d.title AS doc_title,
            d.file_path AS doc_file_path,
            d.pdf_url AS doc_pdf_url,
            d.worker_status AS doc_worker_status,
            sub.id AS subject_id,
            sub.name AS subject_name,
            sem.id AS semester_id,
            sem.name AS semester_name,
            l.id AS level_id,
            l.name AS level_name
        FROM documents d
        JOIN subjects sub ON d.subject_id = sub.id
        JOIN semesters sem ON sub.semester_id = sem.id
        JOIN levels l ON sem.level_id = l.id
        WHERE d.status = 'approved'
        ORDER BY l.name, sem.name, sub.name, d.title
    ";
    $allDocs = $pdo->query($treeQuery)->fetchAll();

    foreach ($allDocs as $row) {
        $lId = $row['level_id'];
        $semId = $row['semester_id'];
        $subId = $row['subject_id'];
        
        if (!isset($tree[$lId])) {
            $tree[$lId] = [
                'name' => $row['level_name'],
                'semesters' => []
            ];
        }
        
        if (!isset($tree[$lId]['semesters'][$semId])) {
            $tree[$lId]['semesters'][$semId] = [
                'name' => $row['semester_name'],
                'subjects' => []
            ];
        }
        
        if (!isset($tree[$lId]['semesters'][$semId]['subjects'][$subId])) {
            $tree[$lId]['semesters'][$semId]['subjects'][$subId] = [
                'name' => $row['subject_name'],
                'documents' => []
            ];
        }
        
        $targetUrl = "";
        if ($row['doc_worker_status'] === 'success' && !empty($row['doc_file_path'])) {
            $targetUrl = $row['doc_file_path'];
        } else {
            $targetUrl = $row['doc_pdf_url'];
        }
        
        $tree[$lId]['semesters'][$semId]['subjects'][$subId]['documents'][] = [
            'id' => $row['doc_id'],
            'title' => $row['doc_title'],
            'url' => $targetUrl,
            'is_html' => ($row['doc_worker_status'] === 'success' && !empty($row['doc_file_path']))
        ];
    }
} catch (Exception $e) {
    // Si la DB échoue, on continue avec un arbre vide
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($activeTitle) ?> - IAI DOCS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bibliothèques de rendu Riche -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        :root { 
            --primary: #00e5c4; 
            --bg: #040c18; 
            --card-bg: rgba(13, 27, 45, 0.7); 
            --border: rgba(255, 255, 255, 0.08); 
            --sidebar-width: 320px;
        }
        body, html { margin: 0; padding: 0; height: 100%; background: var(--bg); color: #fff; font-family: 'Outfit', sans-serif; overflow: hidden; }
        
        /* Conteneur Principal Flexible */
        .app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            position: relative;
        }

        /* --- Sidebar Premium --- */
        .viewer-sidebar {
            width: var(--sidebar-width);
            background: rgba(13, 27, 45, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            z-index: 100;
            height: 100%;
        }
        .viewer-sidebar.collapsed {
            width: 0 !important;
            border-right: none !important;
            overflow: hidden !important;
            padding: 0 !important;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-logo img {
            height: 55px;
            width: auto;
            object-fit: contain;
        }

        /* Barre de Recherche Sidebar */
        .sidebar-search-box {
            position: relative;
        }
        .sidebar-search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 15px 10px 35px;
            color: #fff;
            font-size: 0.85rem;
            outline: none;
            transition: all 0.3s;
            font-family: inherit;
            box-sizing: border-box;
        }
        .sidebar-search-input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 10px rgba(0, 229, 196, 0.2);
        }
        .sidebar-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            stroke: rgba(255, 255, 255, 0.4);
            pointer-events: none;
        }

        /* Zone de Navigation de la Sidebar */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(0, 229, 196, 0.15); border-radius: 10px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(0, 229, 196, 0.4); }

        /* Styles de l'Arborescence SQL */
        .tree-level { margin-bottom: 8px; }
        .tree-level-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 12px; background: rgba(255, 255, 255, 0.02);
            border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.88rem;
            color: #fff; border: 1px solid transparent; transition: all 0.25s;
        }
        .tree-level-header:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(0, 229, 196, 0.2);
        }
        .tree-level-content { display: none; padding-left: 8px; margin-top: 4px; }
        .tree-level.expanded > .tree-level-content { display: block; }

        .tree-semester { margin: 4px 0; }
        .tree-semester-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 10px; cursor: pointer; font-size: 0.82rem; font-weight: 500;
            color: #94a3b8; transition: color 0.25s;
        }
        .tree-semester-header:hover { color: #fff; }
        .tree-semester-content { display: none; padding-left: 10px; border-left: 1px dashed rgba(255, 255, 255, 0.1); }
        .tree-semester.expanded > .tree-semester-content { display: block; }

        .tree-subject { margin: 3px 0; }
        .tree-subject-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 6px 10px; cursor: pointer; font-size: 0.78rem;
            color: #cbd5e1; transition: color 0.25s;
        }
        .tree-subject-header:hover { color: var(--primary); }
        .tree-subject-content { display: none; padding-left: 8px; }
        .tree-subject.expanded > .tree-subject-content { display: block; }

        .tree-doc-link {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 10px; color: #94a3b8; text-decoration: none;
            font-size: 0.75rem; border-radius: 4px; transition: all 0.25s;
            margin: 2px 0; line-height: 1.3;
        }
        .tree-doc-link:hover {
            background: rgba(0, 229, 196, 0.06);
            color: var(--primary);
        }
        .tree-doc-link.active {
            background: rgba(0, 229, 196, 0.12);
            color: var(--primary);
            font-weight: 600;
            border-left: 3px solid var(--primary);
        }

        .tree-arrow {
            width: 12px; height: 12px; transition: transform 0.25s;
            fill: none; stroke: currentColor; stroke-width: 2.5;
            stroke-linecap: round; stroke-linejoin: round;
            opacity: 0.6;
        }
        .expanded > * > .tree-arrow {
            transform: rotate(90deg);
            opacity: 1;
        }

        /* --- Zone Principale Droite --- */
        .viewer-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        /* Barre d'outils supérieure */
        .toolbar {
            height: 60px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: relative;
            z-index: 90;
        }
        
        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
            max-width: 50%;
        }

        /* Bouton Hamburger de la Sidebar */
        .sidebar-toggle-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .sidebar-toggle-btn:hover {
            background: var(--primary);
            color: var(--bg);
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 229, 196, 0.3);
        }
        
        .doc-title-display {
            font-weight: 500;
            font-size: 0.95rem;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid var(--border); 
            color: #fff; 
            padding: 8px 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 0.9rem; 
            transition: all 0.3s; 
        }
        .btn:hover { background: var(--primary); color: var(--bg); border-color: var(--primary); }
        
        .viewer-container { flex: 1; position: relative; background: #fff; }
        #doc-frame { width: 100%; height: 100%; border: none; background: #fff; }

        /* --- Interface Tuteur IA --- */
        .ai-chat-trigger {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px;
            background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 10px 30px rgba(0, 229, 196, 0.4); z-index: 1000; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .ai-chat-trigger:hover { transform: scale(1.1) rotate(5deg); }
        .ai-chat-trigger svg { width: 30px; height: 30px; fill: var(--bg); }

        .chat-drawer {
            position: fixed; top: 0; right: -400px; width: 400px; height: 100%;
            background: rgba(4, 12, 24, 0.95); backdrop-filter: blur(20px);
            border-left: 1px solid var(--border); z-index: 1001; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; box-shadow: -10px 0 50px rgba(0,0,0,0.5);
        }
        .chat-drawer.open { right: 0; }
        .chat-drawer.expanded { width: 800px; max-width: 95%; }

        .chat-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 15px; }
        .chat-header-actions { display: flex; align-items: center; gap: 15px; }
        .chat-header h3 { margin: 0; font-size: 1.1rem; color: var(--primary); display: flex; align-items: center; gap: 10px; flex: 1; }
        
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; }
        .message { max-width: 85%; padding: 12px 16px; border-radius: 15px; font-size: 0.95rem; line-height: 1.5; overflow-wrap: break-word; }
        .message.ai { background: rgba(255,255,255,0.05); align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid var(--border); }
        .message.user { background: var(--primary); color: var(--bg); align-self: flex-end; border-bottom-right-radius: 2px; font-weight: 500; }
        
        .message table { border-collapse: collapse; margin: 10px 0; width: 100%; font-size: 0.85rem; }
        .message th, .message td { border: 1px solid var(--border); padding: 8px; text-align: left; }
        .message th { background: rgba(255,255,255,0.1); color: var(--primary); }
        .message code { background: rgba(0,0,0,0.3); padding: 2px 5px; border-radius: 4px; font-family: monospace; }
        .message pre { background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px; overflow-x: auto; }
        .message blockquote { border-left: 3px solid var(--primary); margin: 10px 0; padding-left: 10px; font-style: italic; opacity: 0.8; }

        .chat-input-area { padding: 20px; border-top: 1px solid var(--border); display: flex; gap: 10px; }
        .chat-input { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 10px; padding: 12px; color: #fff; font-family: inherit; outline: none; transition: border-color 0.3s; }
        .chat-input:focus { border-color: var(--primary); }
        
        .web-toggle { background: transparent; border: 1px solid var(--border); width: 45px; height: 45px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; opacity: 0.6; }
        .web-toggle.active { background: rgba(66, 133, 244, 0.2); border-color: #4285F4; opacity: 1; box-shadow: 0 0 10px rgba(66, 133, 244, 0.4); }
        .web-toggle svg { width: 20px; height: 20px; stroke: #fff; }
        .web-toggle.active svg { stroke: #4285F4; }

        .send-btn { background: var(--primary); border: none; width: 45px; height: 45px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
        .send-btn:hover { transform: scale(1.05); }
        .send-btn svg { width: 20px; height: 20px; fill: var(--bg); }

        .close-chat { cursor: pointer; color: #fff; opacity: 0.5; transition: opacity 0.3s; }
        .close-chat:hover { opacity: 1; }

        .typing-indicator { display: flex; gap: 5px; padding: 10px; display: none; }
        .dot { width: 6px; height: 6px; background: var(--primary); border-radius: 50%; animation: bounce 1.4s infinite ease-in-out; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }

        /* Styles Premium du Sélecteur de Thème */
        .theme-select-premium {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 0.7rem;
            outline: none;
            transition: all 0.3s;
            font-family: inherit;
        }
        .theme-select-premium:hover, .theme-select-premium:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 10px rgba(0, 229, 196, 0.2);
        }
        .theme-select-premium option {
            background: #040c18;
            color: #fff;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        
        <!-- SIDEBAR GAUCHE DYNAMIQUE (SQL-driven) -->
        <aside class="viewer-sidebar" id="viewer-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="assets/iai_docs_moderne.png" alt="Logo IAI-TOGO">
                </div>
                <div class="sidebar-search-box">
                    <svg class="sidebar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" class="sidebar-search-input" id="sidebar-search" placeholder="Rechercher une épreuve...">
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <?php if (empty($tree)): ?>
                    <p style="font-size: 0.85rem; color: #94a3b8; text-align: center; padding-top: 20px;">Aucune navigation disponible.</p>
                <?php else: ?>
                    <?php foreach ($tree as $lvlId => $level): ?>
                        <div class="tree-level" data-id="<?= $lvlId ?>">
                            <div class="tree-level-header">
                                <span><?= htmlspecialchars((string)$level['name']) ?></span>
                                <svg class="tree-arrow" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </div>
                            <div class="tree-level-content">
                                <?php foreach ($level['semesters'] as $semId => $semester): ?>
                                    <div class="tree-semester" data-id="<?= $semId ?>">
                                        <div class="tree-semester-header">
                                            <span><?= htmlspecialchars((string)$semester['name']) ?></span>
                                            <svg class="tree-arrow" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                        <div class="tree-semester-content">
                                            <?php foreach ($semester['subjects'] as $subId => $subject): ?>
                                                <div class="tree-subject" data-id="<?= $subId ?>">
                                                    <div class="tree-subject-header">
                                                        <span><?= htmlspecialchars((string)$subject['name']) ?></span>
                                                        <svg class="tree-arrow" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                                    </div>
                                                    <div class="tree-subject-content">
                                                        <?php foreach ($subject['documents'] as $doc): ?>
                                                            <?php 
                                                            $isActive = ($activeDoc && $doc['id'] == $activeDoc['id']) || ($url === $doc['url']);
                                                            ?>
                                                            <a href="#" 
                                                               class="tree-doc-link <?= $isActive ? 'active' : '' ?>" 
                                                               data-url="<?= htmlspecialchars((string)$doc['url']) ?>" 
                                                               data-title="<?= htmlspecialchars((string)$doc['title']) ?>"
                                                               onclick="loadDocument(this); return false;">
                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                                <span><?= htmlspecialchars((string)$doc['title']) ?></span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- CONTENU DROITE (Visualiseur principal) -->
        <main class="viewer-main">
            <div class="toolbar">
                <div class="toolbar-left">
                    <button class="sidebar-toggle-btn" id="sidebar-toggle" title="Afficher/Masquer la navigation">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <span class="doc-title-display" id="active-doc-title"><?= htmlspecialchars((string)$activeTitle) ?></span>
                </div>
                
                <div class="toolbar-right" style="display: flex; align-items: center;">
                    <select class="theme-select-premium" id="themeSelect" title="Choisir le thème de lecture">
                        <option value="cosmic">🌌 Cosmic Space</option>
                        <option value="sepia">📖 Warm Sepia</option>
                        <option value="cyberpunk">⚡ Cyberpunk</option>
                        <option value="light">☀️ Light Premium</option>
                    </select>
                    <button class="btn" onclick="document.getElementById('doc-frame').requestFullscreen()" style="margin-right: 0.5rem;">⛶ Plein écran</button>
                    <a href="exams.php" class="btn">Quitter</a>
                </div>
            </div>

            <div class="viewer-container">
                <iframe id="doc-frame" src="<?= $proxyUrl ?>"></iframe>
            </div>
        </main>

        <!-- Interface Tuteur IA -->
        <div class="ai-chat-trigger" id="ai-trigger" title="Demander au Tuteur IA">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.61.38 3.12 1.05 4.47l-1.01 3.65c-.14.52.34 1.01.86.86l3.65-1.01C7.88 20.62 9.39 21 12 21c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 16c-1.46 0-2.84-.36-4.05-1l-.43-.22-2.18.6 1.05-3.81L6 13.05C5.36 11.84 5 10.46 5 9c0-3.86 3.14-7 7-7s7 3.14 7 7-3.14 7-7 7z"/></svg>
        </div>

        <div class="chat-drawer" id="chat-drawer">
            <div class="chat-header">
                <h3>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Tuteur IA
                </h3>
                <div class="chat-header-actions">
                    <div class="close-chat" id="expand-chat" title="Agrandir / Réduire">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                    </div>
                    <div class="close-chat" id="close-chat" title="Fermer">✕</div>
                </div>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="message ai">
                    Bonjour ! Je suis votre tuteur IAI. Posez-moi vos questions sur ce document, ou activez la recherche web 🌐 pour des questions d'actualité.
                </div>
            </div>
            <div class="typing-indicator" id="typing-indicator">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            </div>
            <div class="chat-input-area">
                <button class="web-toggle" id="web-toggle" title="Activer la recherche Google">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                </button>
                <input type="text" class="chat-input" id="chat-input" placeholder="Posez votre question...">
                <button class="send-btn" id="send-btn">
                    <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
        </div>

    </div>

    <script>
        const drawer = document.getElementById('chat-drawer');
        const trigger = document.getElementById('ai-trigger');
        const closeBtn = document.getElementById('close-chat');
        const expandBtn = document.getElementById('expand-chat');
        const webToggle = document.getElementById('web-toggle');
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');
        const messagesContainer = document.getElementById('chat-messages');
        const typingIndicator = document.getElementById('typing-indicator');
        const iframe = document.getElementById('doc-frame');
        
        // Eléments de la Sidebar et Recherche
        const sidebar = document.getElementById('viewer-sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarSearch = document.getElementById('sidebar-search');

        let isWebSearchEnabled = false;

        // Gestion Affichage/Masquage de la Sidebar
        sidebarToggle.onclick = () => {
            sidebar.classList.toggle('collapsed');
        };

        // Gestion de l'Arborescence Accordéon
        document.querySelectorAll('.tree-level-header').forEach(header => {
            header.onclick = () => header.parentElement.classList.toggle('expanded');
        });
        document.querySelectorAll('.tree-semester-header').forEach(header => {
            header.onclick = () => header.parentElement.classList.toggle('expanded');
        });
        document.querySelectorAll('.tree-subject-header').forEach(header => {
            header.onclick = () => header.parentElement.classList.toggle('expanded');
        });

        // Déplier automatiquement le nœud actif au chargement
        function expandActivePath() {
            const activeDoc = document.querySelector('.tree-doc-link.active');
            if (activeDoc) {
                activeDoc.scrollIntoView({ block: 'center', behavior: 'smooth' });
                
                let parentSubject = activeDoc.closest('.tree-subject');
                if (parentSubject) {
                    parentSubject.classList.add('expanded');
                    let parentSemester = parentSubject.closest('.tree-semester');
                    if (parentSemester) {
                        parentSemester.classList.add('expanded');
                        let parentLevel = parentSemester.closest('.tree-level');
                        if (parentLevel) {
                            parentLevel.classList.add('expanded');
                        }
                    }
                }
            }
        }
        expandActivePath();

        // Filtrage intelligent multi-mots (Tokens) en temps réel dans la Sidebar
        sidebarSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const isSearching = query.length > 0;
            
            const levels = document.querySelectorAll('.tree-level');
            const semesters = document.querySelectorAll('.tree-semester');
            const subjects = document.querySelectorAll('.tree-subject');
            const docs = document.querySelectorAll('.tree-doc-link');
            
            if (!isSearching) {
                // Reset à l'état normal (on ferme tout et on ré-expand le document actif)
                levels.forEach(el => {
                    el.classList.remove('expanded');
                    el.style.display = 'block';
                });
                semesters.forEach(el => {
                    el.classList.remove('expanded');
                    el.style.display = 'block';
                });
                subjects.forEach(el => {
                    el.classList.remove('expanded');
                    el.style.display = 'block';
                });
                docs.forEach(el => el.style.display = 'flex');
                
                expandActivePath();
                return;
            }
            
            // Découper la recherche en mots individuels (Tokens) en ignorant les tirets/underscores
            const cleanedQuery = query.replace(/[_-]/g, ' ');
            const tokens = cleanedQuery.split(/\s+/).filter(t => t.length > 0);
            
            // Masquer tout par défaut et n'afficher que les résultats correspondants
            docs.forEach(doc => {
                const text = doc.getAttribute('data-title').toLowerCase().replace(/[_-]/g, ' ');
                // Vérifier si TOUS les mots saisis (tokens) sont présents dans le titre
                const matches = tokens.every(token => text.includes(token));
                
                if (matches) {
                    doc.style.display = 'flex';
                    
                    let parentSubject = doc.closest('.tree-subject');
                    if (parentSubject) {
                        parentSubject.style.display = 'block';
                        parentSubject.classList.add('expanded');
                        
                        let parentSemester = parentSubject.closest('.tree-semester');
                        if (parentSemester) {
                            parentSemester.style.display = 'block';
                            parentSemester.classList.add('expanded');
                            
                            let parentLevel = parentSemester.closest('.tree-level');
                            if (parentLevel) {
                                parentLevel.style.display = 'block';
                                parentLevel.classList.add('expanded');
                            }
                        }
                    }
                } else {
                    doc.style.display = 'none';
                }
            });
            
            // Masquer les catégories qui n'ont aucun enfant visible
            subjects.forEach(sub => {
                const visibleDocs = sub.querySelectorAll('.tree-doc-link[style="display: flex;"]');
                sub.style.display = (visibleDocs.length === 0) ? 'none' : 'block';
            });
            
            semesters.forEach(sem => {
                const visibleSubjects = sem.querySelectorAll('.tree-subject[style="display: block;"]');
                sem.style.display = (visibleSubjects.length === 0) ? 'none' : 'block';
            });
            
            levels.forEach(lvl => {
                const visibleSemesters = lvl.querySelectorAll('.tree-semester[style="display: block;"]');
                lvl.style.display = (visibleSemesters.length === 0) ? 'none' : 'block';
            });
        });

        // Chargement d'un document en mode Single-Page App (SPA)
        function loadDocument(element) {
            const url = element.getAttribute('data-url');
            const title = element.getAttribute('data-title');
            
            // Mettre en surbrillance
            document.querySelectorAll('.tree-doc-link').forEach(link => link.classList.remove('active'));
            element.classList.add('active');
            
            // Mettre à jour le titre affiché
            document.getElementById('active-doc-title').textContent = title;
            document.title = title + " - IAI DOCS";
            
            // Mettre à jour l'Iframe
            let finalUrl = "";
            const currentTheme = document.getElementById('themeSelect').value;
            
            // Déterminer s'il s'agit d'un HTML (Sphinx) ou d'un PDF brut
            if (url.includes('.html') || url.includes('/subjects/')) {
                const masked = url.replace('://', '/');
                finalUrl = "proxy.php/" + masked + "?theme=" + encodeURIComponent(currentTheme);
            } else {
                finalUrl = url;
            }
            
            iframe.src = finalUrl;
            
            // Pousser le nouvel état dans l'historique du navigateur
            history.pushState(null, '', '?url=' + encodeURIComponent(url));
        }

        // Écouter les retours arrière/avant du navigateur
        window.onpopstate = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const targetUrl = urlParams.get('url');
            if (targetUrl) {
                const matchingLink = document.querySelector(`.tree-doc-link[data-url="${targetUrl}"]`);
                if (matchingLink) {
                    loadDocument(matchingLink);
                } else {
                    window.location.reload();
                }
            }
        };

        // --- Gestion Premium Tuteur IA ---
        webToggle.onclick = () => {
            isWebSearchEnabled = !isWebSearchEnabled;
            webToggle.classList.toggle('active');
        };

        trigger.onclick = () => drawer.classList.add('open');
        closeBtn.onclick = () => drawer.classList.remove('open', 'expanded');
        expandBtn.onclick = () => drawer.classList.toggle('expanded');

        async function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;

            appendMessage('user', text);
            chatInput.value = '';
            
            let docContext = "";
            try {
                docContext = iframe.contentDocument.body.innerText;
            } catch(e) { console.error("Erreur lecture contexte:", e); }

            typingIndicator.style.display = 'flex';
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            try {
                const response = await fetch('backend/ai_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        message: text,
                        context: docContext,
                        webSearch: isWebSearchEnabled
                    })
                });
                const data = await response.json();
                appendMessage('ai', data.reply);
            } catch (err) {
                appendMessage('ai', "Désolé, j'ai rencontré une erreur de connexion.");
            } finally {
                typingIndicator.style.display = 'none';
            }
        }

        function appendMessage(role, text) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${role}`;
            
            if (role === 'ai') {
                let html = marked.parse(text);
                
                html = html.replace(/\$\$(.*?)\$\$/gs, (match, p1) => {
                    try { return katex.renderToString(p1, { displayMode: true }); } catch(e) { return match; }
                });
                html = html.replace(/\$(.*?)\$/g, (match, p1) => {
                    try { return katex.renderToString(p1, { displayMode: false }); } catch(e) { return match; }
                });
                
                msgDiv.innerHTML = html;
                
                setTimeout(() => {
                    mermaid.init(undefined, msgDiv.querySelectorAll('.language-mermaid'));
                }, 100);
            } else {
                msgDiv.innerText = text;
            }
            
            messagesContainer.appendChild(msgDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        sendBtn.onclick = sendMessage;
        chatInput.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

        window.addEventListener('message', (e) => {
            if (e.data.type === 'explain_text') {
                drawer.classList.add('open');
                chatInput.value = "Peux-tu m'expliquer ce passage : '" + e.data.text + "' ?";
                sendMessage();
            }
        });

        // --- Gestion Premium des Thèmes ---
        const activeTheme = "<?= $theme ?>";
        const themeSelect = document.getElementById('themeSelect');
        themeSelect.value = activeTheme;

        themeSelect.addEventListener('change', (e) => {
            const newTheme = e.target.value;
            document.cookie = "premium_doc_theme=" + newTheme + ";path=/;max-age=31536000";
            
            const currentSrc = iframe.src;
            try {
                const urlObj = new URL(currentSrc);
                urlObj.searchParams.set('theme', newTheme);
                iframe.src = urlObj.toString();
            } catch(err) {
                if (currentSrc.includes('?')) {
                    if (currentSrc.includes('theme=')) {
                        iframe.src = currentSrc.replace(/theme=[^&]+/, 'theme=' + newTheme);
                    } else {
                        iframe.src = currentSrc + '&theme=' + newTheme;
                    }
                } else {
                    iframe.src = currentSrc + '?theme=' + newTheme;
                }
            }
        });
    </script>
</body>
</html>
