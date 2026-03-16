<?php
// backend/auth.php - Skeleton for Authentication

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            die("Erreur: Les mots de passe ne correspondent pas.");
        }

        // TODO: Validate data, hash password, and insert into database
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // DB Insert...

        // Simulate success and create session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;

        header("Location: ../profile.html");
        exit();

    } elseif ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // TODO: Query database, verify password
        // if (password_verify($password, $db_hashed_password)) { ... }

        // Simulate successful login
        $_SESSION['user_name'] = "Étudiant Dev 1"; // Mock name
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;

        header("Location: ../profile.html");
        exit();
    }
} else {
    header("Location: ../login.html");
    exit();
}
?>
