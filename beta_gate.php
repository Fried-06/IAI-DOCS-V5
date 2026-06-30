<?php
// beta_gate.php - Private Beta Gating and Waitlist Registration
session_start();
require_once __DIR__ . '/backend/db.php';

$error = '';
$success = '';

// Check if already authorized
if (isset($_SESSION['beta_authorized']) && $_SESSION['beta_authorized'] === true) {
    header('Location: index.php');
    exit;
}

$pdo = getDB();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Verify Beta Code
    if ($action === 'verify_code') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        
        if (empty($code)) {
            $error = 'Veuillez saisir un code d\'accès.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM beta_codes WHERE code = ?");
                $stmt->execute([$code]);
                $codeData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$codeData) {
                    $error = 'Code d\'accès invalide.';
                } elseif ($codeData['is_used']) {
                    $error = 'Ce code d\'accès a déjà été utilisé.';
                } else {
                    // Mark as authorized in session
                    $_SESSION['beta_authorized'] = true;
                    
                    // If user is already logged in, update their database record as well!
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        $updateUser = $pdo->prepare("UPDATE users SET is_beta_approved = TRUE WHERE id = ?");
                        $updateUser->execute([$_SESSION['user_id']]);
                        
                        // Update code as used by this logged in user
                        $updateCode = $pdo->prepare("UPDATE beta_codes SET is_used = TRUE, used_by_email = ?, used_at = NOW() WHERE code = ?");
                        $updateCode->execute([$_SESSION['user_email'], $code]);
                    } else {
                        // Mark code as used by guest (we will associate it if they sign up/login in this session)
                        $_SESSION['pending_beta_code'] = $code;
                    }

                    header('Location: index.php');
                    exit;
                }
            } catch (\Exception $e) {
                $error = 'Erreur serveur. Veuillez réessayer.';
            }
        }
    }

    // Action 2: Join Waitlist
    if ($action === 'join_waitlist') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $device = trim($_POST['device'] ?? '');
        $motivation = trim($_POST['motivation'] ?? '');

        if (empty($name) || empty($email) || empty($level) || empty($device)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } else {
            try {
                // Check if email already on waitlist or users
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM beta_waitlist WHERE email = ?");
                $stmtCheck->execute([$email]);
                $existsWaitlist = $stmtCheck->fetchColumn();

                $stmtCheckUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND is_beta_approved = TRUE");
                $stmtCheckUser->execute([$email]);
                $existsUser = $stmtCheckUser->fetchColumn();

                if ($existsUser) {
                    $error = 'Cet e-mail est déjà approuvé. Veuillez vous connecter.';
                } elseif ($existsWaitlist) {
                    $error = 'Vous êtes déjà inscrit sur la liste d\'attente avec cet e-mail.';
                } else {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO beta_waitlist (name, email, level, device, motivation) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([$name, $email, $level, $device, $motivation]);
                    $success = 'Votre candidature a été soumise avec succès ! L\'équipe vous contactera par e-mail si un accès se libère.';
                }
            } catch (\Exception $e) {
                $error = 'Erreur lors de la soumission. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/IAI-DOCS-WHITE.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Restreint - Bêta Privée | IAI-DOCS</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #000000;
            color: #c8ddf2;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            padding: 20px;
        }

        .background-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .gate-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 2rem;
        }

        .logo-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3.5rem;
            letter-spacing: 0.1em;
            background: linear-gradient(135deg, #00e5c4, #3b82f6, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 0 10px rgba(0, 229, 196, 0.2));
        }

        .badge-beta {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
            vertical-align: middle;
            margin-left: 8px;
        }

        .gate-card {
            background: rgba(10, 18, 30, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gate-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }

        .gate-card p.subtitle {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #00e5c4;
            font-weight: 500;
        }

        .input-stylized {
            width: 100%;
            background: rgba(4, 12, 24, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .input-stylized:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), 
                        inset 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .input-stylized::placeholder {
            color: #4a5a70;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #00e5c4, #3b82f6);
            color: #040c18;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 229, 196, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 229, 196, 0.5);
            background: linear-gradient(135deg, #00ffd0, #4f94ff);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            text-align: left;
            font-family: 'JetBrains Mono', monospace;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #4a5a70;
            font-size: 0.8rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
        }

        .divider span {
            padding: 0 12px;
        }

        .waitlist-trigger {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #c8ddf2;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            font-weight: 500;
        }

        .waitlist-trigger:hover {
            background: rgba(255, 255, 255, 0.03);
            border-color: #3b82f6;
            color: #fff;
        }

        .login-trigger {
            background: transparent;
            border: 1px solid rgba(0, 229, 196, 0.25);
            color: #00e5c4;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .login-trigger:hover {
            background: rgba(0, 229, 196, 0.05);
            border-color: #00e5c4;
            color: #fff;
            box-shadow: 0 0 15px rgba(0, 229, 196, 0.2);
        }

        .waitlist-section {
            display: none;
            margin-top: 1.5rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.08);
            padding-top: 1.5rem;
            text-align: left;
        }

        .waitlist-section h3 {
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .waitlist-section p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .btn-waitlist {
            background: linear-gradient(135deg, #a855f7, #3b82f6);
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
            color: #fff;
        }

        .btn-waitlist:hover {
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.5);
            background: linear-gradient(135deg, #bd6cff, #4f94ff);
        }

        select.input-stylized {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300e5c4' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
        }

        select.input-stylized option {
            background: #07111f;
            color: #c8ddf2;
        }

        .footer {
            margin-top: 2rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #4a5a70;
        }

        .footer a {
            color: #00e5c4;
            text-decoration: none;
            margin-left: 10px;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Mouse tracking glow on the gate card */
        .gate-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 24px;
            padding: 1.5px;
            background: radial-gradient(800px circle at var(--mouse-x, 0) var(--mouse-y, 0), rgba(0, 229, 196, 0.3), transparent 40%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            z-index: 2;
        }
    </style>
</head>
<body>

    <div class="background-glow">
        <div class="jetbrains-glow-orb"></div>
    </div>

    <div class="gate-container">
        
        <div class="logo-container">
            <span class="logo-text">IAI-DOCS</span>
            <span class="badge-beta">Bêta Privée</span>
        </div>

        <div class="gate-card" id="gateCard">
            <h2>Accès Restreint</h2>
            <p class="subtitle">Pour garantir des performances stables sous haute charge, la plateforme est ouverte en accès restreint. Entrez votre code ou inscrivez-vous sur liste d'attente.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Verification Code Form -->
            <form action="" method="POST">
                <input type="hidden" name="action" value="verify_code">
                
                <div class="form-group">
                    <label for="code">Code d'accès bêta</label>
                    <input type="text" id="code" name="code" class="input-stylized" placeholder="IAI-BETA-XXXXXX" autocomplete="off" required style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase; text-align: center; letter-spacing: 0.1em;">
                </div>

                <button type="submit" class="btn-submit">Valider l'accès</button>
            </form>

            <div class="divider">
                <span>OU</span>
            </div>

            <!-- Action Buttons Container -->
            <div style="display: flex; gap: 12px; margin-top: 5px;">
                <button class="waitlist-trigger" id="waitlistTrigger" style="flex: 1;">Rejoindre la liste d'attente</button>
                <a href="Connexion" class="login-trigger" style="flex: 1;">Se connecter</a>
            </div>

            <!-- Waitlist Expandable Section -->
            <div class="waitlist-section" id="waitlistSection">
                <h3>Candidature Liste d'Attente</h3>
                <p>Aucun code d'accès ? Remplissez ce formulaire pour être sélectionné lors de la prochaine vague d'invitations.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="join_waitlist">

                    <div class="form-group">
                        <label for="name">Nom Complet *</label>
                        <input type="text" id="name" name="name" class="input-stylized" placeholder="Ex: Jean Dupont" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse E-mail *</label>
                        <input type="email" id="email" name="email" class="input-stylized" placeholder="Ex: jean.dupont@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="level">Niveau / Filière *</label>
                        <select id="level" name="level" class="input-stylized" required>
                            <option value="" disabled selected>Sélectionnez votre niveau</option>
                            <option value="L1">Licence 1 (Tronc Commun)</option>
                            <option value="L2">Licence 2 (Tronc Commun)</option>
                            <option value="L3 GLSI">Licence 3 GLSI</option>
                            <option value="L3 ASR">Licence 3 ASR</option>
                            <option value="Autre">Autre étudiant / Enseignant</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="device">Appareil utilisé principalement *</label>
                        <select id="device" name="device" class="input-stylized" required>
                            <option value="" disabled selected>Sélectionnez votre appareil</option>
                            <option value="Ordinateur">Ordinateur (Windows / Mac / Linux)</option>
                            <option value="Smartphone">Smartphone (Android / iPhone)</option>
                            <option value="Tablette">Tablette</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="motivation">Pourquoi souhaitez-vous être bêta-testeur ? (Optionnel)</label>
                        <textarea id="motivation" name="motivation" class="input-stylized" rows="3" placeholder="Ex: Contribuer en envoyant mes cours, corriger des devoirs, etc."></textarea>
                    </div>

                    <button type="submit" class="btn-submit btn-waitlist">Soumettre ma candidature</button>
                </form>
            </div>
        </div>

        <div class="footer">
            © <?= date('Y') ?> IAI-DOCS. Tous droits réservés.
        </div>

    </div>

    <script>
        // Toggle waitlist section
        const trigger = document.getElementById('waitlistTrigger');
        const section = document.getElementById('waitlistSection');
        
        trigger.addEventListener('click', () => {
            if (section.style.display === 'block') {
                section.style.display = 'none';
                trigger.textContent = "Rejoindre la liste d'attente";
            } else {
                section.style.display = 'block';
                trigger.textContent = 'Masquer le formulaire';
                // Scroll smooth to show waitlist form
                setTimeout(() => {
                    section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        });

        // Mouse glow movement on card
        const card = document.getElementById('gateCard');
        document.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });

        // If there is an error/success message or waitlist post, expand waitlist form if that was active
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'join_waitlist'): ?>
            section.style.display = 'block';
            trigger.textContent = 'Masquer le formulaire';
        <?php endif; ?>
    </script>
</body>
</html>
