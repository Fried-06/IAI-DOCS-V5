<?php
// backend/auth.php - Real Authentication with MySQL database


session_start();
require_once __DIR__ . '/db.php';

// Connexion automatique via le cookie "remember_me"
if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_me'])) {
    $pdo = getDB();
    $token = $_COOKIE['remember_me'];
    $stmt = $pdo->prepare("SELECT ut.user_id, u.name, u.email, u.role FROM user_tokens ut JOIN users u ON ut.user_id = u.id WHERE ut.token = ? AND ut.expires_at > NOW()");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if ($row) {
        setSession($row['user_id'], $row['name'], $row['email'], $row['role']);
        // Rafraîchir le cookie (optionnel)
        setcookie('remember_me', $token, time() + 60*60*24*30, '/', '', false, true);
        // Rediriger vers la page d'accueil si sur login
        if (basename($_SERVER['PHP_SELF']) === 'auth.php' || basename($_SERVER['PHP_SELF']) === 'login.html') {
            header('Location: ../index.php');
            exit();
        }
    }
}

// Helper: set session variables
// Helper: set session variables
function setSession($userId, $userName, $userEmail, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $userEmail;
    $_SESSION['user_role'] = $role; // Store role in session
    $_SESSION['logged_in'] = true;
    
    // Dynamically calculate upload count
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$userId]);
        $_SESSION['upload_count'] = $stmt->fetchColumn();
    } catch (\PDOException $e) {
        $_SESSION['upload_count'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $pdo = getDB();

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Vérifier si l'utilisateur a coché "Se rappeler de moi"
        $rememberMe = isset($_POST['remember_me']);
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validations
        if (empty($name) || empty($email) || empty($password)) {
            echo "<script>alert('Erreur: Tous les champs sont obligatoires.'); window.history.back();</script>";
            exit;
        }

        if ($password !== $confirm_password) {
            echo "<script>alert('Erreur: Les mots de passe ne correspondent pas.'); window.history.back();</script>";
            exit;
        }

        if (strlen($password) < 4) {
            echo "<script>alert('Erreur: Le mot de passe doit contenir au moins 4 caractères.'); window.history.back();</script>";
            exit;
        }

        // Check if email already exists in DB
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "<script>alert('Erreur: Cet email est déjà utilisé.'); window.history.back();</script>";
            exit;
        }

        // Determine Role (Auto-promote if email ends with wiwi@gmail.com)
        $role = 'student';
        if (str_ends_with(strtolower($email), 'wiwi@gmail.com')) {
            $role = 'admin';
        }

        // Determine is_beta_approved status
        $isBetaApproved = 0;
        if ($role === 'admin' || (isset($_SESSION['beta_authorized']) && $_SESSION['beta_authorized'] === true)) {
            $isBetaApproved = 1;
        }

        // Create new user in DB
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_beta_approved) VALUES (?, ?, ?, ?, ?)");
        
        if ($insertStmt->execute([$name, $email, $hash, $role, $isBetaApproved])) {
            $newUserId = $pdo->lastInsertId();
            
            // Set session
            setSession($newUserId, $name, $email, $role);
            if ($isBetaApproved) {
                $_SESSION['beta_authorized'] = true;
            }

            // Consume pending beta code if present
            if (isset($_SESSION['pending_beta_code'])) {
                $pendingCode = $_SESSION['pending_beta_code'];
                $updateCode = $pdo->prepare("UPDATE beta_codes SET is_used = TRUE, used_by_email = ?, used_at = NOW() WHERE code = ?");
                $updateCode->execute([$email, $pendingCode]);
                $_SESSION['beta_authorized'] = true;
                unset($_SESSION['pending_beta_code']);
            }

            header("Location: ../index.php");
            exit();
        } else {
            echo "<script>alert('Erreur lors de la création du compte.'); window.history.back();</script>";
            exit;
        }

    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo "<script>alert('Erreur: Email et mot de passe requis.'); window.history.back();</script>";
            exit;
        }

        // Find user by email (include role and is_beta_approved)
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_beta_approved FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "<script>alert('Erreur: Aucun compte trouvé avec cet email.'); window.history.back();</script>";
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            echo "<script>alert('Erreur: Mot de passe incorrect.'); window.history.back();</script>";
            exit;
        }

        // Set session
        setSession($user['id'], $user['name'], $user['email'], $user['role']);

        // Handle beta authorization
        if ($user['role'] === 'admin' || $user['is_beta_approved']) {
            $_SESSION['beta_authorized'] = true;
        } elseif (isset($_SESSION['pending_beta_code'])) {
            // Apply pending beta code to this existing user
            $pendingCode = $_SESSION['pending_beta_code'];
            try {
                $updateUser = $pdo->prepare("UPDATE users SET is_beta_approved = TRUE WHERE id = ?");
                $updateUser->execute([$user['id']]);
                
                $updateCode = $pdo->prepare("UPDATE beta_codes SET is_used = TRUE, used_by_email = ?, used_at = NOW() WHERE code = ?");
                $updateCode->execute([$user['email'], $pendingCode]);
                
                $_SESSION['beta_authorized'] = true;
                unset($_SESSION['pending_beta_code']);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Si "Se rappeler de moi" est coché, créer un cookie persistant (30 jours)
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            // Stocker le token dans la base de données (table à créer: user_tokens)
            $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt->execute([$user['id'], $token, $expires]);
            setcookie('remember_me', $token, time() + 60*60*24*30, '/', '', false, true);
        }

        header("Location: ../index.php");
        exit();
    }
    // Traitement du mot de passe oublié
    elseif ($action === 'forgot_password') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            echo "<script>alert('Erreur: Email requis.'); window.history.back();</script>";
            exit;
        }
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            echo "<script>alert('Aucun compte trouvé avec cet email.'); window.history.back();</script>";
            exit;
        }
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        // Stocker le token dans la base (table à créer: password_resets)
        $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$user['id'], $token, $expires]);
        // Envoyer un email avec le lien de réinitialisation (ici, simulation)
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=$token";
        // En vrai, utiliser mail() ou une librairie d'email
        echo "<script>alert('Un lien de réinitialisation a été envoyé à votre email (simulation). Lien: $resetLink'); window.location='../login.html';</script>";
        exit;
    }
} 
else {
    header("Location: ../login.php");
    exit();
}
?>
