<?php
// backend/reset_password.php — Réinitialisation du mot de passe via token
require_once __DIR__ . '/db.php';

$pdo = getDB();
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm)) {
        echo "<script>alert('Tous les champs sont obligatoires.'); window.history.back();</script>";
        exit;
    }
    if ($password !== $confirm) {
        echo "<script>alert('Les mots de passe ne correspondent pas.'); window.history.back();</script>";
        exit;
    }
    if (strlen($password) < 4) {
        echo "<script>alert('Le mot de passe doit contenir au moins 4 caractères.'); window.history.back();</script>";
        exit;
    }
    // Vérifier le token
    $stmt = $pdo->prepare("SELECT pr.id, pr.user_id, pr.expires_at, pr.used, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row || $row['used'] || strtotime($row['expires_at']) < time()) {
        echo "<script>alert('Lien invalide ou expiré.'); window.location='../login.html';</script>";
        exit;
    }
    // Mettre à jour le mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $row['user_id']]);
    // Marquer le token comme utilisé
    $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$row['id']]);
    echo "<script>alert('Mot de passe réinitialisé avec succès.'); window.location='../login.html';</script>";
    exit;
}

// Affichage du formulaire si GET avec token valide
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id, expires_at, used FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if ($row && !$row['used'] && strtotime($row['expires_at']) > time()) {
        // Formulaire de réinitialisation
        echo '<form method="POST" style="max-width:400px;margin:40px auto;padding:20px;border:1px solid #ccc;">';
        echo '<h2>Réinitialiser le mot de passe</h2>';
        echo '<input type="hidden" name="token" value="'.htmlspecialchars($token).'">';
        echo '<label>Nouveau mot de passe :</label><br>';
        echo '<input type="password" name="password" required><br><br>';
        echo '<label>Confirmer le mot de passe :</label><br>';
        echo '<input type="password" name="confirm_password" required><br><br>';
        echo '<button type="submit">Valider</button>';
        echo '</form>';
        exit;
    }
}
echo "<script>alert('Lien invalide ou expiré.'); window.location='../login.html';</script>";
exit;
?>
