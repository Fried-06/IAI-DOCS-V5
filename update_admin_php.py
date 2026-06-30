import re

file_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\backend\admin.php"
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add PHP fetching logic for users
php_logic = """
// Users tab data
$allSystemUsers = [];
if ($tab === 'users') {
    $allSystemUsers = $pdo->query("SELECT u.*, 
        (SELECT COUNT(*) FROM documents d WHERE d.user_id = u.id) as total_docs,
        (SELECT COUNT(*) FROM documents d WHERE d.user_id = u.id AND d.status = 'approved') as approved_docs 
        FROM users u ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
"""
content = content.replace('?>\n<!DOCTYPE html>', php_logic + '<!DOCTYPE html>')

# 2. Add Tab
tab_html = """            <a href="admin.php?tab=beta" class="tab <?= $tab === 'beta' ? 'active' : '' ?>">🔑 Gérer la Bêta</a>
            <a href="admin.php?tab=users" class="tab <?= $tab === 'users' ? 'active' : '' ?>">👥 Gérer Utilisateurs</a>"""
content = content.replace('<a href="admin.php?tab=beta" class="tab <?= $tab === \'beta\' ? \'active\' : \'\' ?>">🔑 Gérer la Bêta</a>', tab_html)

# 3. Add Tab Content
users_tab_html = """
        <!-- ==================== TAB: USERS ==================== -->
        <?php elseif ($tab === 'users'): ?>
            <div style="margin-bottom: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-family: 'Bebas Neue', sans-serif; font-size: 2rem; letter-spacing: 0.05em; color: #fff;">👥 Gestion des Utilisateurs</h2>
                </div>
                <?php if (empty($allSystemUsers)): ?>
                    <div class="empty-state">Aucun utilisateur trouvé.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID / Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Documents (Approuvés / Total)</th>
                                <th>Dernière Activité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allSystemUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['name']) ?></strong><br>
                                    <small style="color: #4a6a8a;">ID: <?= $u['id'] ?></small>
                                </td>
                                <td><a href="mailto:<?= htmlspecialchars($u['email']) ?>" style="color: #00e5c4; text-decoration: none;"><?= htmlspecialchars($u['email']) ?></a></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge badge-active">Admin</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(168,85,247,0.15); color: #a855f7;">Étudiant</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $u['approved_docs'] ?></strong> / <?= $u['total_docs'] ?>
                                </td>
                                <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                                    <?= $u['last_active'] ? htmlspecialchars($u['last_active']) : 'Jamais' ?>
                                </td>
                                <td>
                                    <form method="POST" action="admin_action.php" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <input type="hidden" name="action" value="demote_admin">
                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Retirer le rôle Admin à cet utilisateur ?');">Rétrograder</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="promote_admin">
                                            <button type="submit" class="btn btn-approve btn-sm" onclick="return confirm('Nommer cet utilisateur Administrateur ?');">Nommer Admin</button>
                                        <?php endif; ?>
                                    </form>
                                    <form method="POST" action="admin_action.php" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <button type="submit" class="btn btn-reject btn-sm" onclick="return confirm('Supprimer définitivement cet utilisateur et ses documents ? (Cette action ne supprime que de la BD locale, pas de Supabase Auth)');">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
"""

content = content.replace('<?php endif; ?>\n    </div>\n</body>', users_tab_html + '\n        <?php endif; ?>\n    </div>\n</body>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("admin.php updated successfully!")
