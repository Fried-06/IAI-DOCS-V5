<?php
// backend/session_check.php — Returns JSON with current auth state
// Called by main.js on every page load to toggle navbar

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    require_once __DIR__ . '/db.php';

    // Dynamically count approved uploads from relational documents table
    $uploadCount = 0;
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$_SESSION['user_id']]);
        $uploadCount = (int) $stmt->fetchColumn();
        $_SESSION['upload_count'] = $uploadCount;
    } catch (\PDOException $e) {
        $uploadCount = $_SESSION['upload_count'] ?? 0;
    }

    // Rank calculation
    if ($uploadCount >= 16) {
        $rank = 'Gold';
        $rankLabel = 'Contributeur Or';
    } elseif ($uploadCount >= 6) {
        $rank = 'Silver';
        $rankLabel = 'Contributeur Argent';
    } else {
        $rank = 'Bronze';
        $rankLabel = 'Contributeur Bronze';
    }

    // Generate initials from username
    $username = $_SESSION['user_name'] ?? 'User';
    $parts = explode(' ', $username);
    $initials = '';
    foreach ($parts as $p) {
        if (!empty($p)) {
            $initials .= strtoupper(substr($p, 0, 1));
        }
    }
    $initials = substr($initials, 0, 2);

    echo json_encode([
        'logged_in'    => true,
        'user_id'      => $_SESSION['user_id'] ?? '',
        'username'     => $username,
        'email'        => $_SESSION['user_email'] ?? '',
        'initials'     => $initials,
        'upload_count' => $uploadCount,
        'rank'         => $rank,
        'rank_label'   => $rankLabel
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
