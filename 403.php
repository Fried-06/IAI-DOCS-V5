<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(403);

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['user_name'] ?? '';
$role = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Interdit - 403</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #040c18;
            --bg2: #07111f;
            --bg3: #0b1930;
            --border: #1e3558;
            --purple: #a855f7;
            --red: #ef4444;
            --text: #c8ddf2;
            --muted: #4a6a8a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: radial-gradient(circle at 50% 50%, #150920, var(--bg));
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Blueprint grid layout */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(168, 85, 247, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(168, 85, 247, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 1;
        }

        .stars {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: pulse 3s infinite ease-in-out;
        }

        .container {
            position: relative;
            text-align: center;
            padding: 3rem;
            max-width: 600px;
            background: rgba(7, 17, 31, 0.85);
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 35px rgba(168, 85, 247, 0.08);
            z-index: 2;
            animation: fadeIn 0.8s ease-out;
        }

        .glitch-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 9rem;
            line-height: 1;
            background: linear-gradient(135deg, #fff, var(--purple));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(168, 85, 247, 0.4);
            margin-bottom: 1rem;
            letter-spacing: 0.05em;
        }

        h2 {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .warning-box {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .warning-box strong {
            color: var(--red);
        }

        .user-status {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 2.5rem;
            background: rgba(11, 25, 48, 0.5);
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            border: 1px solid var(--border);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 2rem;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 0.08em;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--purple);
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.2);
        }

        .btn-primary:hover {
            background: #b96bf8;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--purple);
            color: #fff;
            background: rgba(168, 85, 247, 0.05);
            transform: translateY(-3px);
        }

        .lock-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            animation: float 6s ease-in-out infinite;
        }

        .countdown-wrap {
            margin-top: 2rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--muted);
        }

        .progress-bar {
            width: 100%;
            height: 2px;
            background: var(--border);
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--purple);
            width: 100%;
            transition: width 1s linear;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.8; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="stars" id="starsContainer"></div>

    <div class="container">
        <!-- SVG Lock Icon -->
        <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>

        <div class="glitch-num">403</div>
        <h2>Accès Interdit</h2>
        
        <div class="warning-box">
            Vous tentez d'accéder à un espace restreint. <strong>Vous ne possédez pas les autorisations nécessaires</strong> pour consulter cette page.
        </div>

        <div class="user-status">
            <?php if ($isLoggedIn): ?>
                Connecté : <?= htmlspecialchars($username) ?> (<?= htmlspecialchars(strtoupper($role)) ?>)
            <?php else: ?>
                Statut : Non connecté
            <?php endif; ?>
        </div>

        <div class="btn-group">
            <a href="/index.html" class="btn btn-primary">
                Retour à l'accueil
            </a>
            <?php if ($isLoggedIn && $role !== 'admin'): ?>
                <a href="/login.html" class="btn btn-outline">
                    Changer de compte
                </a>
            <?php elseif (!$isLoggedIn): ?>
                <a href="/login.html" class="btn btn-outline">
                    Se connecter
                </a>
            <?php endif; ?>
        </div>

        <div class="countdown-wrap">
            Redirection automatique vers l'accueil dans <span id="countdown">12</span>s...
            <div class="progress-bar">
                <div class="progress-fill" id="progress"></div>
            </div>
        </div>
    </div>

    <script>
        // Generate space stars background
        const container = document.getElementById('starsContainer');
        const count = 50;
        for (let i = 0; i < count; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.width = Math.random() * 3 + 'px';
            star.style.height = star.style.width;
            star.style.left = Math.random() * 100 + 'vw';
            star.style.top = Math.random() * 100 + 'vh';
            star.style.animationDelay = Math.random() * 5 + 's';
            star.style.animationDuration = Math.random() * 3 + 2 + 's';
            container.appendChild(star);
        }

        // Automatic redirect countdown
        let timeLeft = 12;
        const countdownEl = document.getElementById('countdown');
        const progressEl = document.getElementById('progress');
        
        const interval = setInterval(() => {
            timeLeft--;
            countdownEl.textContent = timeLeft;
            progressEl.style.width = ((timeLeft / 12) * 100) + '%';
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.href = '/index.html';
            }
        }, 1000);
    </script>
</body>
</html>
