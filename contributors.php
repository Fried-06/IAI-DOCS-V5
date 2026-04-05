<?php

// contributors.php à¢€” Enhanced Contributors Page with ranks

// Reads from both database.json AND users.json

session_start();
header('Content-Type: text/html; charset=utf-8');




require_once __DIR__ . '/backend/db.php';
$contributors = [];

try {
    $pdo = getDB();
    
    // 1. Get all users
    $stmtUsers = $pdo->query("SELECT id, name, email, created_at FROM users");
    while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
        $contributors[$row['id']] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'valid_uploads' => 0,
            'total_uploads' => 0,
            'documents' => [],
            'member_since' => $row['created_at']
        ];
    }
    
    // 2. Get all documents with relational JOINs
    $stmtDocs = $pdo->query(
        "SELECT d.user_id, d.title, d.status, d.created_at,
                l.name AS level_name, dt.name AS type_name, y.year
         FROM documents d
         JOIN subjects s ON d.subject_id = s.id
         JOIN semesters sem ON s.semester_id = sem.id
         JOIN levels l ON sem.level_id = l.id
         JOIN document_types dt ON d.type_id = dt.id
         JOIN years y ON d.year_id = y.id
         WHERE d.user_id IS NOT NULL"
    );
    while ($doc = $stmtDocs->fetch(PDO::FETCH_ASSOC)) {
        $uid = $doc['user_id'];
        if (isset($contributors[$uid])) {
            $contributors[$uid]['total_uploads']++;
            if ($doc['status'] === 'approved') {
                $contributors[$uid]['valid_uploads']++;
                $contributors[$uid]['documents'][] = [
                    'title' => $doc['title'],
                    'level' => $doc['level_name'],
                    'category' => $doc['type_name'],
                    'date' => $doc['created_at']
                ];
            }
        }
    }
} catch (\PDOException $e) {
    error_log("DB Query Error: " . $e->getMessage());
}



// Sort by valid uploads descending

usort($contributors, function($a, $b) {

    return $b['valid_uploads'] <=> $a['valid_uploads'];

});



// Rank helper

function getRank($count) {
    if ($count >= 16) return ['Gold', 'Contributeur Or', '#FFD700', '&#127942;'];
    if ($count >= 6) return ['Silver', 'Contributeur Argent', '#C0C0C0', '&#129352;'];
    return ['Bronze', 'Contributeur Bronze', '#CD7F32', '&#129353;'];
}


// Safe string helpers to prevent crashes if mbstring is missing
function safe_strtoupper($str) {
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($str, 'UTF-8');
    }
    return strtoupper($str);
}

function safe_substr($str, $start, $length = null) {
    if (function_exists('mb_substr')) {
        return mb_substr($str, $start, $length, 'UTF-8');
    }
    return substr($str, $start, $length);
}

$totalContributors = count($contributors);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contributeurs - Ressources IAI</title>

    <link rel="stylesheet" href="css/style.css?v=2">

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    

    <script src="js/theme.js?v=2"></script>
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

                    <li><a href="contributors.php" class="nav-item active">Contributeurs</a></li>

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



    <header class="contrib-hero">

        <div class="container">

            <h1>Nos <span style="color:#ffb703;">Contributeurs</span></h1>

            <p>Découvrez les étudiants qui aident la communauté IAI en partageant leurs documents.</p>

            <div class="count-badge">&#128101; <?= $totalContributors ?> contributeur(s) inscrits</div>

        </div>

    </header>



    <main class="container" style="padding: 2rem 0 4rem;">

        <?php if (empty($contributors)): ?>

            <div class="empty-contrib">

                <p>Aucun contributeur pour le moment.</p>

                <p style="margin-top:0.5rem;">Soyez le premier à partager vos documents !</p>

                <a href="contribute.html" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.5rem;background:linear-gradient(135deg,#00e5c4,#00c4a7);color:#000;border-radius:8px;text-decoration:none;font-weight:600;">Contribuer</a>

            </div>

        <?php else: ?>

            <!-- Podium for top 3 -->

            <?php if (count($contributors) >= 1): ?>

            <div class="podium">

                <?php

                $podiumRanks = ['🥇', '🥈', '🥉'];

                $podiumClasses = ['podium-1', 'podium-2', 'podium-3'];

                for ($i = 0; $i < min(3, count($contributors)); $i++):

                    $c = $contributors[$i];

                    $nameInitials = safe_strtoupper(safe_substr($c['name'], 0, 1));

                    $parts = explode(' ', $c['name']);

                    if (count($parts) > 1) $nameInitials .= safe_strtoupper(safe_substr($parts[1], 0, 1));

                ?>

                <div class="podium-card <?= $podiumClasses[$i] ?>">

                    <div class="podium-rank"><?= $podiumRanks[$i] ?></div>

                    <div class="podium-avatar"><?= htmlspecialchars($nameInitials) ?></div>

                    <div class="podium-name"><?= htmlspecialchars($c['name']) ?></div>

                    <div class="podium-count"><?= $c['valid_uploads'] ?> doc(s) validé(s)</div>

                </div>

                <?php endfor; ?>

            </div>

            <?php endif; ?>



            <!-- Full list -->

            <div class="contributors-list">

                <?php foreach ($contributors as $index => $c):

                    $rank = getRank($c['valid_uploads']);

                    $rankClass = strtolower($rank[0]);

                    $nameInitials = safe_strtoupper(safe_substr($c['name'], 0, 1));

                    $parts = explode(' ', $c['name']);

                    if (count($parts) > 1) $nameInitials .= safe_strtoupper(safe_substr($parts[1], 0, 1));

                ?>

                <div class="contributor-card">

                    <div class="contributor-header" onclick="toggleDocs(this)">

                        <div class="contributor-left">

                            <div class="contributor-rank-num">#<?= $index + 1 ?></div>

                            <div class="avatar-circle"><?= htmlspecialchars($nameInitials) ?></div>

                            <span class="contributor-name"><?= htmlspecialchars($c['name']) ?></span>

                        </div>

                        <div class="contributor-right">

                            <span class="rank-badge rank-<?= $rankClass ?>"><?= $rank[3] ?> <?= $rank[1] ?></span>

                            <span class="upload-count"><?= $c['valid_uploads'] ?> doc(s)</span>

                        </div>

                    </div>

                    <?php if (!empty($c['documents'])): ?>

                    <ul class="documents-list">

                        <?php foreach ($c['documents'] as $doc): ?>

                        <li class="doc-item">

                            <div>

                                <div class="doc-title"><?= htmlspecialchars($doc['title']) ?></div>

                                <div class="doc-meta"><?= htmlspecialchars($doc['level']) ?> • <?= htmlspecialchars($doc['category']) ?></div>

                            </div>

                            <div class="doc-meta"><?= !empty($doc['date']) ? explode(' ', $doc['date'])[0] : '' ?></div>

                        </li>

                        <?php endforeach; ?>

                    </ul>

                    <?php endif; ?>

                </div>

                <?php endforeach; ?>

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

        function toggleDocs(el) {

            const list = el.nextElementSibling;

            if (list && list.classList.contains('documents-list')) {

                list.classList.toggle('active');

            }

        }

    </script>

</body>

</html>

