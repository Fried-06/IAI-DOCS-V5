import re

file_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\backend\admin_action.php"
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add User Management Actions
user_actions_logic = """
$userId = intval($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

// ============================================
// USER MANAGEMENT ACTIONS
// ============================================
if (in_array($action, ['promote_admin', 'demote_admin', 'delete_user'])) {
    if ($userId <= 0) {
        die("Invalid user ID.");
    }
    
    $pdo = getDB();
    
    if ($action === 'promote_admin') {
        $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("L'utilisateur a été promu administrateur."));
        exit;
    }
    
    if ($action === 'demote_admin') {
        $pdo->prepare("UPDATE users SET role = 'student' WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("Le rôle administrateur a été retiré."));
        exit;
    }
    
    if ($action === 'delete_user') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        header("Location: admin.php?tab=users&success=" . urlencode("L'utilisateur a été supprimé."));
        exit;
    }
}
"""

content = content.replace("if ($_SERVER['REQUEST_METHOD'] !== 'POST') {\n    die(\"Invalid request\");\n}\n\n$docId = intval($_POST['id'] ?? 0);", 
                          "if ($_SERVER['REQUEST_METHOD'] !== 'POST') {\n    die(\"Invalid request\");\n}\n" + user_actions_logic + "\n$docId = intval($_POST['id'] ?? 0);")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("admin_action.php updated successfully!")
