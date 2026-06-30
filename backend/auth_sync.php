<?php
// backend/auth_sync.php
// Ce script reçoit le token d'accès Supabase du client, le vérifie de manière sécurisée auprès de Supabase, 
// synchronise la table locale `public.users` et crée la session PHP.

error_reporting(0); // Empêche les Warnings (comme curl_close deprecated) de corrompre le JSON
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$access_token = $input['access_token'] ?? null;

if (!$access_token) {
    echo json_encode(['success' => false, 'error' => 'No access token provided']);
    exit;
}

// Vérification sécurisée du token auprès de l'API Supabase
$ch = curl_init(SUPABASE_STORAGE_URL . '/auth/v1/user');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $access_token,
    "apikey: sb_publishable_OkgQSdS18pTOf1lNiFYdzw_QJw6ufZx" // Use anon key to match the frontend
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Désactiver la vérification SSL pour le développement local (ex: XAMPP sur Windows)
// Désactiver la vérification SSL pour le développement local (ex: XAMPP sur Windows)
// En production, vous devriez configurer cacert.pem dans php.ini et retirer cette ligne.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpcode !== 200) {
    $logData = date('Y-m-d H:i:s') . "\n";
    $logData .= "Token: " . substr($access_token, 0, 15) . "...\n";
    $logData .= "HTTP Code: " . $httpcode . "\n";
    $logData .= "cURL Error: " . $curl_error . "\n";
    $logData .= "Response: " . $response . "\n\n";
    file_put_contents(__DIR__ . '/debug_auth.log', $logData, FILE_APPEND);

    echo json_encode([
        'success' => false, 
        'error' => 'Invalid or expired token', 
        'debug_http_code' => $httpcode, 
        'debug_curl_error' => $curl_error, 
        'debug_response' => $response,
        'debug_url' => SUPABASE_STORAGE_URL . '/auth/v1/user'
    ]);
    exit;
}

$supabaseUser = json_decode($response, true);
if (!$supabaseUser || !isset($supabaseUser['id'])) {
    echo json_encode(['success' => false, 'error' => 'Failed to parse user data from Supabase']);
    exit;
}

$email = $supabaseUser['email'] ?? '';
$supabaseId = $supabaseUser['id']; // uuid from auth.users
$name = $supabaseUser['user_metadata']['name'] ?? $supabaseUser['user_metadata']['full_name'] ?? explode('@', $email)[0];

$pdo = getDB();

try {
    // Vérifier si l'utilisateur existe déjà dans public.users par email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $localUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($localUser) {
        // Utilisateur existe déjà
        $userId = $localUser['id'];
        $role = $localUser['role'] ?? 'student';
        
        // Mettre à jour last_active
        $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$userId]);
    } else {
        // Nouvel utilisateur (ex: Inscription ou connexion via OAuth)
        $role = 'student';
        // Auto-promo admin pour wiwi@gmail.com est enlevé comme demandé. Mais pour le premier admin on pourrait garder si besoin, ici on retire.
        $isBetaApproved = 0; // Par défaut. Les admins le géreront.
        $dummyPassword = 'SUPABASE_MANAGED_AUTH'; // Mot de passe bidon car géré par Supabase

        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_beta_approved) VALUES (?, ?, ?, ?, ?) RETURNING id");
        $insertStmt->execute([$name, $email, $dummyPassword, $role, $isBetaApproved]);
        $userId = $insertStmt->fetchColumn();
    }

    // Set PHP Session
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $localUser['name'] ?? $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $localUser['role'] ?? $role;
    $_SESSION['logged_in'] = true;
    
    // Beta authorization logic
    if (($_SESSION['user_role'] === 'admin') || (isset($localUser['is_beta_approved']) && $localUser['is_beta_approved'])) {
        $_SESSION['beta_authorized'] = true;
    } else {
        $_SESSION['beta_authorized'] = false;
    }

    // Upload count calculation
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND status = 'approved'");
    $countStmt->execute([$userId]);
    $_SESSION['upload_count'] = $countStmt->fetchColumn();

    echo json_encode(['success' => true]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
