<?php
// backend/auth.php - Real Authentication with MySQL database

session_start();
require_once __DIR__ . '/db.php';

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

        // Create new user in DB
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        
        if ($insertStmt->execute([$name, $email, $hash, $role])) {
            $newUserId = $pdo->lastInsertId();
            
            // Set session
            setSession($newUserId, $name, $email, $role);

            header("Location: ../index.html");
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

        // Find user by email (include role)
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
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

        header("Location: ../index.html");
        exit();
    }
}
 else {
    header("Location: ../login.html");
    exit();
}
?>
