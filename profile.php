<?php

// profile.php â Real Profile Page with session data + database info

session_start();
header('Content-Type: text/html; charset=utf-8');




// Redirect to login if not authenticated

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {

    header("Location: login.html");

    exit();

}



// Load user data from session

$userId = $_SESSION['user_id'] ?? '';

$username = $_SESSION['user_name'] ?? 'Utilisateur';

$email = $_SESSION['user_email'] ?? '';

$uploadCount = $_SESSION['upload_count'] ?? 0;



// Generate initials

$parts = explode(' ', $username);

$initials = '';

foreach ($parts as $p) {

    if (!empty($p)) $initials .= strtoupper(substr($p, 0, 1));

}

$initials = substr($initials, 0, 2);



// Load user's documents from MySQL (relational schema)
require_once __DIR__ . '/backend/db.php';
$userDocs = [];
$publishedCount = 0;

try {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        "SELECT d.*, s.name AS subject_name, dt.name AS type_name, y.year, 
                l.name AS level_name, sem.name AS semester_name
         FROM documents d
         JOIN subjects s ON d.subject_id = s.id
         JOIN semesters sem ON s.semester_id = sem.id
         JOIN levels l ON sem.level_id = l.id
         JOIN document_types dt ON d.type_id = dt.id
         JOIN years y ON d.year_id = y.id
         WHERE d.user_id = ? ORDER BY d.created_at DESC"
    );
    $stmt->execute([$userId]);
    $userDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($userDocs as $doc) {
        if ($doc['status'] === 'approved') {
            $publishedCount++;
        }
    }
} catch (\PDOException $e) {
    error_log("DB Query Error: " . $e->getMessage());
}

$uploadCount = $publishedCount;
$_SESSION['upload_count'] = $uploadCount;



// Determine rank

if ($uploadCount >= 16) {

    $rank = 'Gold';

    $rankLabel = 'Contributeur Or';

    $rankColor = '#FFD700';

    $rankIcon = '&#127942;';

} elseif ($uploadCount >= 6) {

    $rank = 'Silver';

    $rankLabel = 'Contributeur Argent';

    $rankColor = '#C0C0C0';

    $rankIcon = '&#129352;';

} else {

    $rank = 'Bronze';

    $rankLabel = 'Contributeur Bronze';

    $rankColor = '#CD7F32';

    $rankIcon = '&#129353;';

}



// Load user creation date from MySQL
$memberSince = '';
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if ($created_at = $stmt->fetchColumn()) {
        $memberSince = date('d/m/Y', strtotime($created_at));
    }
} catch (\PDOException $e) {
    // Ignore
}
?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil de <?= htmlspecialchars($username) ?> - Ressources IAI</title>

    <link rel="stylesheet" href="css/style.css?v=2">

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    <style>

        .profile-hero {

            position: relative;

            padding: 4rem 0 3rem;

            background: linear-gradient(135deg, #040c18 0%, #0b1930 50%, #0a1628 100%);

            overflow: hidden;

        }

        .profile-hero::before {

            content:''; position:absolute; inset:0;

            background-image:

                linear-gradient(rgba(0,229,196,.04) 1px,transparent 1px),

                linear-gradient(90deg,rgba(0,229,196,.04) 1px,transparent 1px);

            background-size:60px 60px;

            pointer-events:none;

        }

        .profile-hero::after {

            content:''; position:absolute; top:-50%; right:-20%;

            width:600px; height:600px;

            background: radial-gradient(circle, rgba(0,229,196,0.08), transparent 60%);

            pointer-events:none;

        }

        .profile-card {

            position:relative; z-index:1;

            display: flex; align-items: center; gap: 2rem;

        }

        .profile-avatar {

            width: 110px; height: 110px;

            background: linear-gradient(135deg, #00e5c4, #a855f7);

            border-radius: 50%;

            display: flex; align-items: center; justify-content: center;

            font-size: 2.5rem; font-weight: 700; color: #fff;

            box-shadow: 0 0 30px rgba(0,229,196,0.3), 0 0 60px rgba(168,85,247,0.15);

            border: 3px solid rgba(255,255,255,0.1);

            flex-shrink: 0;

        }

        .profile-info h1 {

            font-family: 'Bebas Neue', sans-serif;

            font-size: 2.5rem; color: #fff;

            letter-spacing: 0.03em; margin-bottom: 0.25rem;

        }

        .profile-email {

            color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

            font-size: 0.85rem; margin-bottom: 1rem;

        }

        .profile-badges {

            display: flex; gap: 0.75rem; flex-wrap: wrap;

        }

        .badge {

            display: inline-flex; align-items: center; gap: 0.4rem;

            padding: 0.4rem 0.9rem;

            background: rgba(255,255,255,0.05);

            border: 1px solid rgba(255,255,255,0.1);

            border-radius: 2rem;

            font-size: 0.82rem; color: #c8ddf2;

            font-family: 'JetBrains Mono', monospace;

        }

        .badge-rank { border-color: rgba(255,215,0,0.3); }

        .badge svg { width:16px; height:16px; }



        /* Dashboard */

        .dashboard { padding: 3rem 0; }

        .dash-grid {

            display: grid;

            grid-template-columns: 1fr 300px;

            gap: 2rem;

        }

        @media(max-width:768px) {

            .dash-grid { grid-template-columns: 1fr; }

            .profile-card { flex-direction: column; text-align: center; }

            .profile-badges { justify-content: center; }

        }

        .dash-card {

            background: #07111f;

            border: 1px solid #152540;

            border-radius: 12px;

            padding: 1.5rem;

        }

        .dash-card h2 {

            font-family: 'Bebas Neue', sans-serif;

            font-size: 1.5rem; color: #fff;

            letter-spacing: 0.04em;

            margin-bottom: 1rem;

            padding-bottom: 0.75rem;

            border-bottom: 1px solid #152540;

        }

        .doc-list { list-style: none; padding: 0; margin: 0; }

        .doc-item {

            display: flex; justify-content: space-between; align-items: center;

            padding: 1rem 0;

            border-bottom: 1px solid #152540;

        }

        .doc-item:last-child { border-bottom: none; }

        .doc-title-text {

            font-weight: 600; color: #c8ddf2;

            margin-bottom: 0.2rem;

        }

        .doc-meta-text {

            font-size: 0.8rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

        }

        .status-badge {

            padding: 0.3rem 0.7rem;

            border-radius: 1rem;

            font-size: 0.75rem; font-weight: 600;

            white-space: nowrap;

        }

        .status-published { background: rgba(0,229,196,0.15); color: #00e5c4; }

        .status-pending { background: rgba(255,183,3,0.15); color: #ffb703; }

        .status-rejected { background: rgba(255,100,100,0.15); color: #ff6b6b; }



        /* Stats sidebar */

        .stat-box {

            text-align: center;

            padding: 1.25rem;

            background: rgba(0,229,196,0.04);

            border: 1px solid #1e3558;

            border-radius: 10px;

            margin-bottom: 1rem;

        }

        .stat-box-number {

            font-family: 'Bebas Neue', sans-serif;

            font-size: 2.5rem; color: #00e5c4;

        }

        .stat-box-label {

            font-size: 0.8rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

            text-transform: uppercase; letter-spacing: 0.08em;

        }

        .action-btn {

            display: block; width: 100%;

            padding: 0.8rem;

            text-align: center;

            border-radius: 8px;

            font-family: 'JetBrains Mono', monospace;

            font-weight: 600; font-size: 0.85rem;

            text-decoration: none;

            margin-bottom: 0.75rem;

            transition: all 0.3s;

            text-transform: uppercase;

            letter-spacing: 0.05em;

        }

        .action-primary {

            background: linear-gradient(135deg, #00e5c4, #00c4a7);

            color: #000;

        }

        .action-primary:hover {

            box-shadow: 0 0 20px rgba(0,229,196,0.4);

            transform: translateY(-2px);

        }

        .action-outline {

            background: transparent;

            border: 1px solid #1e3558;

            color: #c8ddf2;

        }

        .action-outline:hover {

            border-color: #00e5c4;

            color: #00e5c4;

        }

        .action-danger {

            background: transparent;

            border: 1px solid rgba(255,100,100,0.3);

            color: #ff6b6b;

        }

        .action-danger:hover {

            background: rgba(255,100,100,0.1);

            border-color: #ff6b6b;

        }

        .empty-state {

            text-align: center;

            padding: 3rem 1rem;

            color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

            font-size: 0.9rem;

        }

        .empty-state svg { margin-bottom: 1rem; opacity: 0.3; }

    </style>

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

                    <li><a href="contribute.html" class="nav-item">Contribuer</a></li>

                </ul>

                <div class="nav-actions">

                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème" style="margin-right: 0.5rem;">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>

                    </button>

                    <a href="login.html" class="btn btn-outline auth-login-btn" style="padding: 0.5rem 1rem; border: none;">Connexion</a>

                    <a href="login.html" class="btn btn-primary auth-register-btn" style="padding: 0.5rem 1rem;">S'inscrire</a>

                    <a href="profile.php" class="btn btn-outline" id="btn-profil" style="display: none; padding: 0.5rem 1rem;">Profil</a>

                </div>

            </div>

            <button class="mobile-menu-btn">

                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>

            </button>

        </div>

    </nav>



    <!-- Profile Hero -->

    <header class="profile-hero">

        <div class="container">

            <div class="profile-card">

                <div class="profile-avatar"><?= htmlspecialchars($initials) ?></div>

                <div class="profile-info">

                    <h1><i><?= htmlspecialchars($username) ?></i></h1>

                    <p class="profile-email"><b style="color: #00c4a7;"><?= htmlspecialchars($email) ?></p></b>

                    <div class="profile-badges">

                        <div class="badge">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>

                            <b><?= $uploadCount ?> document(s) validé(s)</b>

                        </div>

                        <div class="badge badge-rank">

                            <b><?= $rankIcon ?> <?= $rankLabel ?></b>

                        </div>

                        <?php if ($memberSince): ?>

                        <div class="badge">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"></rect><line x1="16" y1="2" x2="16" y2="6" stroke-width="2"></line><line x1="8" y1="2" x2="8" y2="6" stroke-width="2"></line></svg>

                            <b>Membre depuis le <?= $memberSince ?></b>

                        </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </header>



    <!-- Dashboard Content -->

    <main class="container dashboard">

        <div class="dash-grid">

            <!-- Documents History -->

            <div class="dash-card">

                <h2><i> Vos Documents</i></h2>

                <?php if (empty($userDocs)): ?>

                    <div class="empty-state">

                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>

                        <p>Aucun document uploadé pour le moment.</p>

                        <p style="margin-top:0.5rem;">Commencez à contribuer à la communauté !</p>

                    </div>

                <?php else: ?>

                    <ul class="doc-list">

                        <?php foreach (array_reverse($userDocs) as $doc): ?>

                        <li class="doc-item">

                            <div>

                                <div class="doc-title-text"><?= htmlspecialchars($doc['title'] ?? 'Sans titre') ?></div>

                                <div class="doc-meta-text">

                                    <?= htmlspecialchars($doc['level_name'] ?? '') ?> • <?= htmlspecialchars($doc['type_name'] ?? '') ?> • <?= $doc['year'] ?? '' ?>

                                    <?php if (!empty($doc['created_at'])): ?>

                                        • <?= explode(' ', $doc['created_at'])[0] ?>

                                    <?php endif; ?>

                                </div>

                            </div>

                            <div>

                                <?php 

                                $status = $doc['status'] ?? 'pending';

                                $statusClass = 'status-pending';

                                $statusLabel = 'En attente';

                                if ($status === 'approved') { $statusClass = 'status-published'; $statusLabel = 'Publié'; }

                                elseif ($status === 'rejected') { $statusClass = 'status-rejected'; $statusLabel = 'Rejeté'; }

                                ?>

                                <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>

                            </div>

                        </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>

            </div>



            <!-- Sidebar -->

            <div>

                <!-- Stats -->

                <div class="dash-card" style="margin-bottom: 1.5rem;">

                    <h2><i>Vos Statistiques</i></h2>

                    <div class="stat-box">

                        <div class="stat-box-number"><?= count($userDocs) ?></div>

                        <div class="stat-box-label">Total Uploads</div>

                    </div>

                    <div class="stat-box">

                        <div class="stat-box-number"><?= $uploadCount ?></div>

                        <div class="stat-box-label">Documents Validés</div>

                    </div>

                    <div class="stat-box" style="border-color: <?= $rankColor ?>33;">

                        <div class="stat-box-number" style="color: <?= $rankColor ?>;"><?= $rankIcon ?></div>

                        <div class="stat-box-label"><?= $rankLabel ?></div>

                    </div>

                </div>



                <!-- Actions -->

                <div class="dash-card">

                    <h2><i> Actions</i></h2>

                    <a href="contribute.html" class="action-btn action-primary">Ajouter un Document</a>

                    <a href="contributors.php" class="action-btn action-outline">Voir Classement</a>

                    <a href="backend/logout.php" class="action-btn action-danger">Déconnexion</a>

                </div>

            </div>

        </div>

    </main>



    <footer class="footer">

        <div class="container">

            <div class="footer-bottom">

                <p>&copy; 2026 IAIDOCS. Tous droits réservés.</p>

            </div>

        </div>

    </footer>

<script src="js/main.js"></script>

</body>

</html>

