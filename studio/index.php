<?php
session_start();
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.html");
    exit;
}
require_once __DIR__ . '/../backend/db.php';
$pdo = getDB();

$userId = $_SESSION['user_id'];
$initials = substr($_SESSION['user_name'], 0, 2);

// Fetch user's basket (workspace sessions)
$stmt = $pdo->prepare("SELECT * FROM studio_sessions WHERE user_id = ?");
$stmt->execute([$userId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user private docs
$stmtDocs = $pdo->prepare("SELECT * FROM studio_user_docs WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmtDocs->execute([$userId]);
$privateDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IAI-DOCS — AI Study Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/studio.css">
    
    <!-- External Libs for parsing outputs -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>

    <!-- KaTeX for mathematical formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
</head>
<body class="app-body">

    <!-- Navbar / App Header (Minimal) -->
    <nav class="app-header">
        <div class="app-header-left">
            <a href="../index.html" class="btn-icon" title="Retour à l'accueil" style="margin-right: 1rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="app-logo">
                <div class="logo-orb"></div>
                <span class="logo-text">IAI-DOCS <span class="logo-highlight">Studio</span></span>
            </div>
            <div class="header-breadcrumb">
                <span class="separator">/</span>
                <span class="current-path">Nouvelle session</span>
            </div>
        </div>
        <div class="app-header-right">
            <button class="btn btn-secondary" id="btnExport">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8m-4-6l-4-4-4 4m4-4v13"/></svg>
                Exporter
            </button>
            <button class="btn btn-primary" id="btnShare">Partager</button>
            <div class="user-avatar"><?= strtoupper($initials) ?></div>
        </div>
    </nav>

    <!-- Main Layout Grid -->
    <div class="app-layout">
        
        <!-- =======================
             PANEL 1: SOURCES (LEFT)
             ======================= -->
        <aside class="panel panel-left panel-sources">
            <div class="panel-header">
                <h2>Ressources</h2>
                <div class="header-actions">
                    <button class="btn-icon" title="Filtrer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                </div>
            </div>

            <!-- Upload / Add Button -->
            <div class="p-4 pt-2">
                <button class="btn btn-add-source w-full" id="btnUploadModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter des fichiers personnels
                </button>
            </div>

            <!-- Global Search Box -->
            <div class="search-container p-4 pt-0">
                <div class="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="officialSearch" placeholder="Recherche (@ pour Docs IAI)..." autocomplete="off">
                </div>
                <div id="searchResults" class="search-dropdown"></div>
            </div>

            <div class="source-list-header px-4 pb-2">
                <span class="label">Documents Actifs</span>
                <span class="count badge" id="basketCount">0</span>
            </div>

            <div class="source-list" id="basketContainer">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p>Aucune ressource.</p>
                    <span>Importez des PDF ou cherchez @cours.</span>
                </div>
            </div>

            <div class="panel-footer insights-card">
                <div class="insights-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-accent-blue"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <span>Insights Globaux</span>
                </div>
                <div class="insights-body">
                    <div class="insight-item"><span class="val">0</span> <span class="lbl">Pages</span></div>
                    <div class="insight-item"><span class="val">~</span> <span class="lbl">Niveau</span></div>
                </div>
            </div>
        </aside>

        <!-- =======================
             PANEL 2: WORKSPACE (MID)
             ======================= -->
        <main class="panel panel-center panel-workspace">
            <div class="workspace-scroll-area" id="chatArea">
                
                <!-- Initial Welcome State -->
                <div class="workspace-welcome" id="welcomeState">
                    <div class="welcome-orb"></div>
                    <h1>Prêt à commencer, <?= htmlspecialchars($_SESSION['user_name']) ?> ?</h1>
                    <p>Votre intelligence documentaire est prête. Sélectionnez des documents et lancez l'IA.</p>
                    
                    <div class="smart-summary-card">
                        <div class="card-glow"></div>
                        <div class="card-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Résumé Automatique En Attente
                        </div>
                        <div class="card-body">
                            Insérez des ressources dans le panneau de gauche pour générer un aperçu intelligent. Le modèle extraira automatiquement les <strong>Mots Clés</strong> et préparera le contexte.
                        </div>
                    </div>
                </div>

                <!-- Chat history dynamically added here -->
            </div>

            <!-- Intelligent Input Area -->
            <div class="workspace-input-container">
                <div class="quick-prompts" id="quickPrompts">
                    <div class="quick-pill">Résume-moi les concepts</div>
                    <div class="quick-pill">Génère un QCM difficile</div>
                    <div class="quick-pill">Dessine une mindmap</div>
                </div>
                <div class="input-glow-box">
                    <button class="btn-attach" id="btnAttachInput" title="Attacher un document (PDF, Word, etc.)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                    </button>
                    <textarea id="aiInput" placeholder="Demandez quelque chose au sujet de vos documents..." rows="1"></textarea>
                    <button class="btn-send" id="btnSend" title="Envoyer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
                <div class="input-meta">
                    Modèle propulsé par Gemini 1.5 Pro • Contexte actif
                </div>
            </div>
        </main>

        <!-- =======================
             PANEL 3: AI STUDIO (RIGHT)
             ======================= -->
        <aside class="panel panel-right panel-studio">
            <div class="panel-header">
                <h2>AI Studio <span class="status-dot"></span></h2>
                <div class="header-actions">
                    <button class="btn-icon" id="btnSettings" title="Paramètres d'apparence"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg></button>
                </div>
            </div>

            <div class="studio-scroll-area">
                
                <div class="studio-section">
                    <h3>Compréhension</h3>
                    <div class="studio-grid">
                        <div class="tool-card accent-primary" data-action="audio">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1v-5h3v3z"/><path d="M3 19a2 2 0 002 2h1v-5H3v3z"/></svg>
                            <div class="tool-info">
                                <h4>Résumé Audio</h4>
                                <span>Podcast interactif</span>
                            </div>
                        </div>
                        <div class="tool-card" data-action="resume">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                            <div class="tool-info">
                                <h4>Résumer</h4>
                                <span>Synthèse rapide</span>
                            </div>
                        </div>
                        <div class="tool-card" data-action="concepts">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            <div class="tool-info">
                                <h4>Concepts</h4>
                                <span>Lexique et points</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="studio-section">
                    <h3>Apprentissage</h3>
                    <div class="studio-grid">
                        <div class="tool-card feature-quiz" data-action="quiz">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <div class="tool-info">
                                <h4>Mode Quiz</h4>
                                <span>Tester son niveau</span>
                            </div>
                        </div>
                        <div class="tool-card" data-action="flashcards">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            <div class="tool-info">
                                <h4>Fiches (Cards)</h4>
                                <span>Mémorisation</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="studio-section">
                    <h3>Structuration Spatiale</h3>
                    <div class="studio-grid">
                        <div class="tool-card accent-primary" data-action="mindmap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><line x1="3" y1="12" x2="9" y2="12"/><line x1="15" y1="12" x2="21" y2="12"/><line x1="12" y1="3" x2="12" y2="9"/><line x1="12" y1="15" x2="12" y2="21"/></svg>
                            <div class="tool-info">
                                <h4>Générer Carte Mentale</h4>
                                <span>Cartographier les concepts du document</span>
                            </div>
                        </div>
                        <div class="tool-card" data-action="infographie">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><path d="M9 21V9"/></svg>
                            <div class="tool-info">
                                <h4>Infographie</h4>
                                <span>Visualisation claire</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="studio-section">
                    <h3>Niveau Avancé</h3>
                    <div class="studio-grid">
                        <div class="tool-card danger-card" data-action="traps">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <div class="tool-info">
                                <h4>Pièges Examen</h4>
                                <span>Avertissements</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="notebook-memory p-4">
                <div class="memory-card">
                    <div class="mem-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-accent-secondary"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                        Mémoire Active
                    </div>
                    <p>Vos choix sont mémorisés pour la session. Gemini est prêt.</p>
                </div>
            </div>
        </aside>

    </div>

    <!-- Upload Modal -->
    <div class="modal-overlay" id="uploadModalOverlay">
        <div class="modal-box">
            <button class="modal-close" id="uploadModalClose">&times;</button>
            <div class="modal-header">
                <h3>Importer des fichiers</h3>
                <p>Protégés localement, non utilisés pour l'entraînement.</p>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="upload-dropzone">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p>Glissez-déposez ou <strong>cliquez</strong> pour choisir.</p>
                        <span class="text-xs">PDF, DOCX, PPTX (Max 50MB)</span>
                        <input type="file" name="private_doc" id="fileInput" accept=".pdf,.doc,.docx,.ppt,.pptx" required class="hidden-input">
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4">Transférer au Workspace</button>
                    <div id="uploadStatus" class="mt-2 text-center text-sm"></div>
                </form>

                <div class="personal-docs-list mt-6">
                    <h4 class="text-sm font-semibold mb-3">Fichiers Personnels Déjà Transférés</h4>
                    <?php if (empty($privateDocs)): ?>
                        <p class="text-xs text-[#64748b] text-center p-3">Vide.</p>
                    <?php else: ?>
                        <ul class="doc-list-small">
                            <?php foreach ($privateDocs as $doc): ?>
                            <li id="pdoc-<?= $doc['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="doc-icon"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                <span class="doc-name"><?= htmlspecialchars($doc['original_name']) ?></span>
                                <div style="display:flex;gap:0.25rem;">
                                    <button type="button" class="btn-icon add-to-basket" data-id="<?= $doc['id'] ?>" data-type="private" title="Ajouter au Panier"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                                    <button type="button" class="btn-icon text-accent-danger delete-private-doc" data-id="<?= $doc['id'] ?>" title="Supprimer définitivement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg></button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Logic -->
    <script src="js/studio.js"></script>
</body>
</html>
