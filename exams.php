<?php

// exams.php â Dynamic Exams Page scanning _build/html/ for real documents

session_start();
header('Content-Type: text/html; charset=utf-8');




// Level labels for filter pills
$levelLabels = [
    'L1' => 'Licence 1',
    'L2' => 'Licence 2',
    'L3 GLSI' => 'Licence 3 GLSI',
    'L3 ASR' => 'Licence 3 ASR'
];

// Base URL prefix for document links
$docsBaseUrl = '/Docs/_build/html/';

// Fetch ALL approved documents from the relational DB
require_once __DIR__ . '/backend/db.php';
$exams = [];

try {
    $pdo = getDB();
    $stmt = $pdo->query(
        "SELECT d.title, d.file_path,
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
         WHERE d.status = 'approved' AND dt.name IN ('partiel', 'corrige_partiel')
         ORDER BY l.name, sem.name, s.name, y.year DESC"
    );
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($records as $rec) {
        $link = '#';
        if (!empty($rec['file_path'])) {
            $link = $docsBaseUrl . $rec['file_path'];
        }
        $exams[] = [
            'title' => $rec['title'],
            'level' => $rec['level_name'],
            'semester' => $rec['semester_name'],
            'subject' => $rec['subject_name'],
            'type' => $rec['type_name'],
            'year' => $rec['year'],
            'link' => $link,
            'user' => $rec['user_name'] ?? 'Anonyme'
        ];
    }
} catch (\PDOException $e) {
    error_log("Exams DB Error: " . $e->getMessage());
}

// Group by level
$grouped = [];
foreach ($exams as $exam) {
    $level = $exam['level'];
    if (!isset($grouped[$level])) $grouped[$level] = [];
    $grouped[$level][] = $exam;
}

// Sort levels
$levelOrder = ['L1' => 1, 'L2' => 2, 'L3 GLSI' => 3, 'L3 ASR' => 4];
uksort($grouped, function($a, $b) use ($levelOrder) {
    return ($levelOrder[$a] ?? 99) <=> ($levelOrder[$b] ?? 99);
});

$totalExams = count($exams);



// Collect unique semesters for filter

$allSemesters = array_unique(array_filter(array_column($exams, 'semester')));

sort($allSemesters);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Banque d'Examens - Ressources IAI</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    <style>

        .exams-hero {

            padding: 4rem 0;

            background: linear-gradient(135deg, #040c18 0%, #0b1930 50%, #0a1628 100%);

            color: white; text-align: center;

            position: relative; overflow: hidden;

        }

        .exams-hero::before {

            content:''; position:absolute; inset:0;

            background-image:

                linear-gradient(rgba(0,229,196,.03) 1px,transparent 1px),

                linear-gradient(90deg,rgba(0,229,196,.03) 1px,transparent 1px);

            background-size:50px 50px; pointer-events:none;

        }

        .exams-hero h1 {

            font-family: 'Bebas Neue', sans-serif;

            font-size: clamp(2rem,5vw,3.5rem); letter-spacing: 0.04em;

            position: relative; z-index: 1;

        }

        .exams-hero p {

            font-family: 'JetBrains Mono', monospace;

            font-size: 0.85rem; color: #4a6a8a;

            position: relative; z-index: 1;

            max-width: 600px; margin: 0.75rem auto 0;

        }

        .exams-hero .exam-count {

            display: inline-block; margin-top: 1rem;

            padding: 0.4rem 1rem; border-radius: 2rem;

            background: rgba(0,229,196,0.1); border: 1px solid rgba(0,229,196,0.2);

            font-family: 'JetBrains Mono', monospace;

            font-size: 0.8rem; color: #00e5c4;

            position: relative; z-index: 1;

        }



        /* Filters */

        .filter-bar {

            display: flex; gap: 0.75rem; flex-wrap: wrap;

            justify-content: center;

            padding: 1.5rem 0; margin-bottom: 1rem;

        }

        .filter-pill {

            padding: 0.5rem 1.2rem; border-radius: 2rem;

            background: #07111f; border: 1px solid #1e3558;

            color: #c8ddf2; font-size: 0.85rem;

            cursor: pointer; transition: all 0.3s;

            font-family: 'JetBrains Mono', monospace;

        }

        .filter-pill:hover, .filter-pill.active {

            background: rgba(0,229,196,0.15);

            border-color: #00e5c4; color: #00e5c4;

        }



        /* Level sections */

        .level-section { margin-bottom: 3rem; }

        .level-section-title {

            font-family: 'Bebas Neue', sans-serif;

            font-size: 1.8rem; color: #fff;

            letter-spacing: 0.04em;

            padding-bottom: 0.75rem;

            border-bottom: 1px solid #152540;

            margin-bottom: 1.5rem;

            display: flex; align-items: center; gap: 0.75rem;

        }

        .level-section-title .level-badge {

            padding: 0.3rem 0.8rem; border-radius: 0.4rem;

            font-size: 0.7rem; font-family: 'JetBrains Mono', monospace;

            text-transform: uppercase; letter-spacing: 0.08em;

        }

        .level-badge-l1 { background: rgba(0,229,196,0.15); color: #00e5c4; }

        .level-badge-l2 { background: rgba(168,85,247,0.15); color: #a855f7; }

        .level-badge-l3g { background: rgba(59,130,246,0.15); color: #3b82f6; }

        .level-badge-l3a { background: rgba(255,183,3,0.15); color: #ffb703; }



        .subject-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));

            gap: 1rem;

        }

        .subject-card {

            background: #07111f; border: 1px solid #152540;

            border-radius: 10px; padding: 1.25rem;

            transition: all 0.3s; cursor: pointer;

            text-decoration: none; display: block;

        }

        .subject-card:hover {

            border-color: #00e5c4;

            transform: translateY(-3px);

            box-shadow: 0 8px 25px rgba(0,0,0,0.3), 0 0 15px rgba(0,229,196,0.1);

        }

        .subject-card-title {

            font-weight: 600; color: #c8ddf2;

            font-size: 0.95rem; margin-bottom: 0.4rem;

        }

        .subject-card-meta {

            font-size: 0.75rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

        }

        .subject-card-arrow {

            display: flex; align-items: center; gap: 0.3rem;

            margin-top: 0.75rem;

            font-size: 0.8rem; color: #00e5c4;

            font-family: 'JetBrains Mono', monospace;

        }

    </style>

</head>

<body class="page-fade-in">

    <nav class="navbar">

        <div class="container nav-container">

            <a href="index.html" class="logo" style="padding: 0; display: flex; align-items: center;">

                <img src="assets/logoiai.png" alt="Logo IAI" style="height: 200px; width: auto; object-fit: contain;">

            </a>

            <div class="nav-links">

                <ul class="nav-menu">

                    <li><a href="index.html" class="nav-item">Accueil</a></li>

                    <li><a href="exams.php" class="nav-item">Examens</a></li>

                    <li><a href="search.php" class="nav-item">Rechercher</a></li>

                    <li><a href="contribute.html" class="nav-item">Contribuer</a></li>

                </ul>

                <div class="nav-actions">

                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème" style="margin-right: 0.5rem;">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>

                    </button>

                    <a href="login.html" class="btn btn-outline auth-login-btn" style="padding: 0.5rem 1rem; border: none;">Connexion</a>

                    <a href="login.html" class="btn btn-primary auth-register-btn" style="padding: 0.5rem 1rem;">S'inscrire</a>

                    <a href="profile.php" class="btn btn-outline" id="btn-profil" style="display: none;">Profil</a>

                </div>

            </div>

            <button class="mobile-menu-btn">

                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>

            </button>

        </div>

    </nav>



    <header class="exams-hero">

        <div class="container">

            <h1>Banque Centrale des <span style="color:#00e5c4;">Examens</span></h1>

            <p>Parcourez tous les documents classés par niveau et semestre, directement générés depuis nos ressources Sphinx.</p>

            <div class="exam-count">ð <?= $totalExams ?> documents disponibles</div>

        </div>

    </header>



    <main class="container" style="padding: 2rem 0 4rem;">

        <!-- Filter Bar -->

        <div class="filter-bar">

            <button class="filter-pill active" data-level="all">Tous</button>

            <?php foreach ($levelLabels as $key => $label): ?>

                <?php if (isset($grouped[$key])): ?>

                <button class="filter-pill" data-level="<?= $key ?>"><?= $label ?> (<?= count($grouped[$key]) ?>)</button>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>



        <!-- Exam sections by level -->

        <?php foreach ($grouped as $level => $items): ?>

        <div class="level-section" data-level-section="<?= $level ?>">

            <h2 class="level-section-title">

                <?= $levelLabels[$level] ?? $level ?>

                <?php

                    $badgeClass = 'level-badge-l1';

                    if ($level === 'L2') $badgeClass = 'level-badge-l2';

                    elseif ($level === 'L3_GLSI') $badgeClass = 'level-badge-l3g';

                    elseif ($level === 'L3_ASR') $badgeClass = 'level-badge-l3a';

                ?>

                <span class="level-badge <?= $badgeClass ?>"><?= count($items) ?> ressources</span>

            </h2>

            <div class="subject-grid">

                <?php foreach ($items as $exam): ?>

                <a href="<?= htmlspecialchars($exam['link']) ?>" class="subject-card" data-semester="<?= $exam['semester'] ?>">

                    <div class="subject-card-title"><?= htmlspecialchars($exam['title']) ?></div>

                    <div class="subject-card-meta">

                        <?= $semesterLabels[$exam['semester']] ?? $exam['semester'] ?>

                        <?php if (!empty($exam['source']) && $exam['source'] === 'upload'): ?>

                            â¢ Contribution

                        <?php endif; ?>

                    </div>

                    <div class="subject-card-arrow">

                        Voir la fiche â

                    </div>

                </a>

                <?php endforeach; ?>

            </div>

        </div>

        <?php endforeach; ?>



        <?php if (empty($exams)): ?>

        <div style="text-align:center; padding:4rem; color:#4a6a8a;">

            <p>Aucun examen disponible pour le moment.</p>

        </div>

        <?php endif; ?>

    </main>



    <footer class="footer">

        <div class="container">

            <div class="footer-bottom">

                <p>&copy; 2026 IAIDOCS. Tous droits réservés.</p>

            </div>

        </div>

    </footer>



    <script src="js/main.js"></script>

    <script>

        // Level filter functionality

        document.querySelectorAll('.filter-pill').forEach(btn => {

            btn.addEventListener('click', () => {

                document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));

                btn.classList.add('active');

                

                const level = btn.getAttribute('data-level');

                document.querySelectorAll('.level-section').forEach(section => {

                    if (level === 'all' || section.getAttribute('data-level-section') === level) {

                        section.style.display = 'block';

                    } else {

                        section.style.display = 'none';

                    }

                });

            });

        });

    </script>

</body>

</html>

