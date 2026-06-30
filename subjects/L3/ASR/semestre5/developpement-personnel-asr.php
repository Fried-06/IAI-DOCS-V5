<?php
session_start();
require_once __DIR__ . '/../../../../backend/beta_check.php';
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../../../backend/db.php';
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/subjects/'));

// Fetch documents for this subject from the DB
$documents = [];
$totalDocs = 0;
try {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        "SELECT d.id, d.title, d.file_path, d.pdf_url, d.status,
                s.name AS subject_name,
                sem.name AS semester_name,
                l.name AS level_name,
                dt.name AS type_name,
                y.year,
                u.name AS user_name
         FROM documents d
         JOIN subjects s ON d.subject_id = s.id
         JOIN semesters sem ON s.semester_id = sem.id
         JOIN levels l ON sem.level_id = l.id
         JOIN document_types dt ON d.type_id = dt.id
         JOIN years y ON d.year_id = y.id
         LEFT JOIN users u ON d.user_id = u.id
         WHERE d.status = 'approved'
           AND LOWER(dt.name) NOT IN ('cours', 'exercice')
           AND LOWER(l.name) = LOWER(:level)
           AND LOWER(sem.name) = LOWER(:semester)
           AND LOWER(s.name) = LOWER(:subject)
         ORDER BY y.year DESC, dt.name"
     );
    $stmt->execute([
        ':level'    => 'L3 ASR',
        ':semester' => 'Semestre 5',
        ':subject'  => 'Management_de_Projet'
    ]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalDocs = count($records);

    foreach ($records as $rec) {
        // PDF Link
        if (!empty($rec['pdf_url'])) {
            $pdfLink = (strpos($rec['pdf_url'], 'http') === 0)
                ? $rec['pdf_url']
                : '/' . ltrim($rec['pdf_url'], '/');
        } else {
            $pdfLink = '#';
        }
        // HTML Viewer Link
        $htmlLink = '#';
        if (!empty($rec['file_path']) || !empty($rec['pdf_url'])) {
            $htmlLink = 'viewer/' . $rec['id'];
        }
        $documents[] = [
            'id'       => $rec['id'],
            'title'    => $rec['title'],
            'type'     => $rec['type_name'],
            'year'     => $rec['year'],
            'pdfLink'  => $pdfLink,
            'htmlLink' => $htmlLink,
            'hasHtml'  => ($htmlLink !== '#'),
            'user'     => $rec['user_name'] ?? 'Anonyme'
        ];
    }
} catch (Exception $e) {
    error_log("Subject page DB error: " . $e->getMessage());
}

// Group by year
$byYear = [];
foreach ($documents as $doc) {
    $byYear[$doc['year']][] = $doc;
}
krsort($byYear);
$years = array_keys($byYear);
$firstYear = !empty($years) ? $years[0] : date('Y');
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../../../assets/IAI-DOCS-WHITE.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management de Projet - Ressources IAI</title>
    <meta name="description" content="Ressources de la matiere Management de Projet pour Licence 3 ASR - Semestre 5">
    <base href="<?= $base_url ?>/">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
    <nav class="navbar">
        <div class="container nav-container">
            <a href="Accueil" class="logo" style="padding:0;display:flex;align-items:center;">
                <img src="assets/IAI-NEW-LOGO.png" alt="Logo IAI" style="height:200px;width:auto;object-fit:contain;">
            </a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="Accueil" class="nav-item">Accueil</a></li>
                    <li><a href="Examens" class="nav-item">Examens</a></li>
                    <li><a href="Rechercher" class="nav-item">Rechercher</a></li>
                    <li><a href="Contribuer" class="nav-item">Contribuer</a></li>
                </ul>
                <div class="nav-actions">
                    <button class="theme-toggle" id="theme-toggle" title="Basculer le theme">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    <a href="Connexion" class="btn btn-outline auth-login-btn" style="padding:0.5rem 1rem;border:none;">Connexion</a>
                    <a href="Connexion" class="btn btn-primary auth-register-btn" style="padding:0.5rem 1rem;">S'inscrire</a>
                    <a href="Profil" class="btn btn-outline" id="btn-profil" style="display:none;">Profil</a>
                </div>
            </div>
            <button class="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>

    <main class="container" style="padding: 4rem 0;">
        <div class="subject-header">
            <h1 style="color: var(--primary); margin-bottom: 0.5rem;">Management de Projet</h1>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">Licence 3 ASR - Semestre 5</p>
            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <span style="background:var(--primary-light);color:#000;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.8rem;font-weight:600;">
                    <?= $totalDocs ?> document<?= $totalDocs > 1 ? 's' : '' ?> disponible<?= $totalDocs > 1 ? 's' : '' ?>
                </span>
                <a href="javascript:history.back()" style="color:var(--text-muted);font-size:0.85rem;margin-right:1rem;text-decoration:none;">&#8592; Retour</a>
                <a href="Rechercher" style="color:var(--text-muted);font-size:0.85rem;">&#128269; Rechercher d'autres ressources</a>
            </div>
        </div>

        <?php if (empty($documents)): ?>
        <div style="text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document disponible pour le moment</p>
            <p style="font-size:0.9rem;">Soyez le premier a contribuer pour cette matiere !</p>
            <a href="Contribuer" class="btn btn-primary" style="margin-top:1.5rem;display:inline-block;">Contribuer</a>
        </div>
        <?php else: ?>        <div class="subject-layout">
            <!-- Sidebar: Filters -->
            <aside class="sidebar">
                <div class="sidebar-title">Filtres</div>
                
                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em; margin-top:1rem;">Type de document</h4>
                <div class="type-filters" style="margin-bottom: 2rem;">
                    <button class="year-btn active" data-type="all">Tous les types</button>
                    <?php 
                    $typesPresent = [];
                    foreach($documents as $d) { $typesPresent[$d['type']] = true; }
                    foreach(array_keys($typesPresent) as $type): 
                    ?>
                    <button class="year-btn" data-type="<?= htmlspecialchars($type) ?>">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type))) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em;">Annee Academique</h4>
                <div class="year-filters">
                    <button class="year-btn active" data-year="all">Toutes les annees</button>
                    <?php foreach ($years as $yr): ?>
                    <button class="year-btn" data-year="<?= $yr ?>">Annee <?= $yr ?></button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Content Panels -->
            <div class="subject-content">
                <div class="resource-grid" id="docs-grid">
                    <?php foreach ($documents as $doc): ?>
                    <div class="resource-card doc-item" data-year="<?= $doc['year'] ?>" data-type="<?= htmlspecialchars($doc['type']) ?>" style="display:flex; flex-direction:column; text-align:left;">
                        <h3 style="margin-bottom:0.5rem;color:var(--text-main);font-size:0.95rem;"><?= htmlspecialchars($doc['title']) ?></h3>
                        <div style="margin-bottom:0.5rem;">
                            <span style="font-size:0.75rem; padding:0.2rem 0.5rem; background:var(--primary-light); color:#000; border-radius:4px; font-weight:600; text-transform:capitalize; margin-right:0.5rem;">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['type'])) ?>
                            </span>
                            <span style="font-size:0.75rem; padding:0.2rem 0.5rem; background:var(--bg3); color:var(--text); border-radius:4px; font-weight:600;">
                                <?= $doc['year'] ?>
                            </span>
                        </div>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem; flex-grow:1;">
                            Par <?= htmlspecialchars($doc['user'] ? $doc['user'] : 'Anonyme') ?>
                        </p>
                        <div style="display:flex;gap:0.5rem;flex-direction:column;margin-top:auto;">
                            <?php if ($doc['hasHtml']): ?>
                            <a href="<?= htmlspecialchars($doc['htmlLink']) ?>"
                               class="btn btn-primary"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;">
                                &#128065; Voir HTML
                            </a>
                            <?php endif; ?>
                            <?php if ($doc['pdfLink'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($doc['pdfLink']) ?>"
                               class="btn btn-outline"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;"
                               target="_blank" rel="noopener">
                                &#128196; Voir PDF
                            </a>
                            <?php endif; ?>
                            <?php if (!$doc['hasHtml'] && $doc['pdfLink'] === '#'): ?>
                            <span style="color:var(--text-muted);font-size:0.8rem;text-align:center;">Document indisponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="no-docs-message" style="display:none; text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document ne correspond a vos criteres</p>
                    <p style="font-size:0.9rem;">Essayez de modifier vos filtres.</p>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        let currentType = 'all';
        let currentYear = 'all';

        function filterDocs() {
            document.querySelectorAll('.type-filters .year-btn').forEach(btn => {
                btn.onclick = function() {
                    document.querySelectorAll('.type-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentType = this.getAttribute('data-type');
                    applyFilters();
                };
            });
            document.querySelectorAll('.year-filters .year-btn').forEach(btn => {
                btn.onclick = function() {
                    document.querySelectorAll('.year-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentYear = this.getAttribute('data-year');
                    applyFilters();
                };
            });
        }
        
        function applyFilters() {
            let visibleCount = 0;
            document.querySelectorAll('.doc-item').forEach(item => {
                const matchType = (currentType === 'all' || item.getAttribute('data-type') === currentType);
                const matchYear = (currentYear === 'all' || item.getAttribute('data-year') === currentYear);
                if (matchType && matchYear) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            document.getElementById('no-docs-message').style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        // Initialiser
        filterDocs();
    </script>
    <script src="js/main.js?v=4"></script>
</body>
</html>
