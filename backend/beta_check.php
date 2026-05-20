<?php
// backend/beta_check.php - Checks if user is authorized for the private beta.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

$isAuthorized = false;

// 1. If admin, bypass
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $isAuthorized = true;
}

// 2. If logged in and was approved
if (!$isAuthorized && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT is_beta_approved FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $isApproved = $stmt->fetchColumn();
        if ($isApproved) {
            $isAuthorized = true;
        }
    } catch (\Exception $e) {
        // En cas de problème DB, on évite de bloquer les utilisateurs
        $isAuthorized = true;
    }
}

// 3. If session has temporary beta authorization (entered a valid code before logging in)
if (!$isAuthorized && isset($_SESSION['beta_authorized']) && $_SESSION['beta_authorized'] === true) {
    $isAuthorized = true;
}

// If not authorized
if (!$isAuthorized) {
    // Check if this is an API call or AJAX request
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
              || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
              || str_contains($_SERVER['REQUEST_URI'], '/backend/')
              || str_contains($_SERVER['REQUEST_URI'], '_api');

    if ($isAjax) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Accès restreint aux bêta-testeurs.', 
            'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : false,
            'beta_authorized' => false
        ]);
        exit();
    } else {
        // Redirection vers la page de garde
        $redirectUrl = 'beta_gate.php';
        if (str_contains($_SERVER['REQUEST_URI'], '/backend/')) {
            $redirectUrl = '../beta_gate.php';
        }
        header('Location: ' . $redirectUrl);
        exit();
    }
}
?>
