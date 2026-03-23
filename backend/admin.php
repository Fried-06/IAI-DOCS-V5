<?php
// backend/admin.php — Admin Dashboard with tabs
// Tabs: Documents en Attente | Gérer Années | Gérer Matières

session_start();
require_once __DIR__ . '/db.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'documents';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// --- Handle form submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    // Add Year
    if ($formAction === 'add_year') {
        $year = intval($_POST['year'] ?? 0);
        if ($year >= 2000 && $year <= 2099) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO years (year) VALUES (?)");
                $stmt->execute([$year]);
                header("Location: admin.php?tab=years&success=Année $year ajoutée.");
                exit;
            } catch (\PDOException $e) {
                header("Location: admin.php?tab=years&error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    // Add Subject
    if ($formAction === 'add_subject') {
        $subjectName = trim($_POST['subject_name'] ?? '');
        $semesterId = intval($_POST['semester_id'] ?? 0);
        if (!empty($subjectName) && $semesterId > 0) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO subjects (name, semester_id, is_active) VALUES (?, ?, 1)");
                $stmt->execute([$subjectName, $semesterId]);
                header("Location: admin.php?tab=subjects&success=Matière ajoutée.");
                exit;
            } catch (\PDOException $e) {
                header("Location: admin.php?tab=subjects&error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    // Toggle Subject Active
    if ($formAction === 'toggle_subject') {
        $subjectId = intval($_POST['subject_id'] ?? 0);
        if ($subjectId > 0) {
            $pdo->prepare("UPDATE subjects SET is_active = NOT is_active WHERE id = ?")->execute([$subjectId]);
            header("Location: admin.php?tab=subjects&success=Statut modifié.");
            exit;
        }
    }
}

// --- Fetch data ---

// Pending Documents (with JOINs)
$pendingDocs = $pdo->query(
    "SELECT d.id, d.title, d.filename, d.created_at,
            s.name AS subject_name, dt.name AS type_name, y.year,
            l.name AS level_name, sem.name AS semester_name,
            u.name AS user_name
     FROM documents d 
     JOIN subjects s ON d.subject_id = s.id
     JOIN semesters sem ON s.semester_id = sem.id
     JOIN levels l ON sem.level_id = l.id
     JOIN document_types dt ON d.type_id = dt.id
     JOIN years y ON d.year_id = y.id
     LEFT JOIN users u ON d.user_id = u.id
     WHERE d.status = 'pending'
     ORDER BY d.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// All Years
$allYears = $pdo->query("SELECT * FROM years ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);

// All Subjects with context
$allSubjects = $pdo->query(
    "SELECT sub.id, sub.name, sub.is_active, sem.name AS semester_name, l.name AS level_name
     FROM subjects sub
     JOIN semesters sem ON sub.semester_id = sem.id
     JOIN levels l ON sem.level_id = l.id
     ORDER BY l.name, sem.name, sub.name"
)->fetchAll(PDO::FETCH_ASSOC);

// Levels and Semesters for the add-subject form
$allLevels = $pdo->query("SELECT id, name FROM levels ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$allSemesters = $pdo->query(
    "SELECT s.id, s.name, l.name AS level_name FROM semesters s JOIN levels l ON s.level_id = l.id ORDER BY l.name, s.name"
)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - IAI DOCS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; color: #1e293b; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { font-size: 1.5rem; color: #0f172a; }
        .header-btn { background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 500; }
        .header-btn:hover { background: #cbd5e1; }

        /* Tabs */
        .tabs { display: flex; gap: 0; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; }
        .tab { padding: 12px 24px; text-decoration: none; color: #64748b; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
        .tab:hover { color: #3b82f6; }
        .tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Table */
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 0.9rem; }
        th { background: #3b82f6; color: white; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f8fafc; }

        /* Buttons */
        .btn { padding: 6px 14px; border: none; cursor: pointer; color: white; border-radius: 6px; font-weight: 500; font-size: 0.85rem; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .btn-approve { background: #10b981; } .btn-approve:hover { background: #059669; }
        .btn-reject { background: #ef4444; } .btn-reject:hover { background: #dc2626; }
        .btn-view { background: #64748b; } .btn-view:hover { background: #475569; }
        .btn-primary { background: #3b82f6; } .btn-primary:hover { background: #2563eb; }
        .btn-warning { background: #f59e0b; } .btn-warning:hover { background: #d97706; }
        .btn-sm { padding: 4px 10px; font-size: 0.8rem; }

        /* Forms */
        .form-card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; max-width: 500px; }
        .form-card h3 { margin-bottom: 16px; color: #0f172a; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 4px; font-weight: 500; font-size: 0.85rem; color: #475569; }
        .form-input { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9rem; }
        .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.2); }

        .empty-state { background: white; padding: 40px; text-align: center; border-radius: 8px; color: #64748b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙ Administration IAI DOCS</h1>
            <a href="../index.html" class="header-btn">← Retour au site</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <a href="admin.php?tab=documents" class="tab <?= $tab === 'documents' ? 'active' : '' ?>">📄 Documents en Attente (<?= count($pendingDocs) ?>)</a>
            <a href="admin.php?tab=years" class="tab <?= $tab === 'years' ? 'active' : '' ?>">📅 Gérer Années</a>
            <a href="admin.php?tab=subjects" class="tab <?= $tab === 'subjects' ? 'active' : '' ?>">📚 Gérer Matières</a>
        </div>

        <!-- ==================== TAB: DOCUMENTS ==================== -->
        <?php if ($tab === 'documents'): ?>
            <?php if (empty($pendingDocs)): ?>
                <div class="empty-state">Aucun document en attente de validation.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Matière</th>
                            <th>Type</th>
                            <th>Année</th>
                            <th>Niveau</th>
                            <th>Contributeur</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingDocs as $doc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($doc['title']) ?></strong></td>
                            <td><?= htmlspecialchars($doc['subject_name']) ?></td>
                            <td><?= htmlspecialchars($doc['type_name']) ?></td>
                            <td><?= htmlspecialchars($doc['year']) ?></td>
                            <td><?= htmlspecialchars($doc['level_name']) ?> — <?= htmlspecialchars($doc['semester_name']) ?></td>
                            <td><?= htmlspecialchars($doc['user_name'] ?? 'Anonyme') ?></td>
                            <td><?= htmlspecialchars($doc['created_at']) ?></td>
                            <td style="white-space: nowrap;">
                                <a href="../uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank" class="btn btn-view btn-sm">Voir</a>
                                <form method="POST" action="admin_action.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve btn-sm" onclick="return confirm('Approuver et publier ce document ?');">Approuver</button>
                                </form>
                                <form method="POST" action="admin_action.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject btn-sm" onclick="return confirm('Rejeter ce document ?');">Rejeter</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <!-- ==================== TAB: YEARS ==================== -->
        <?php elseif ($tab === 'years'): ?>
            <div class="form-card">
                <h3>➕ Ajouter une Année</h3>
                <form method="POST" action="admin.php?tab=years">
                    <input type="hidden" name="form_action" value="add_year">
                    <div class="form-group">
                        <label>Année académique</label>
                        <input type="number" name="year" class="form-input" min="2000" max="2099" value="<?= date('Y') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>

            <table>
                <thead><tr><th>ID</th><th>Année</th><th>Ajoutée le</th></tr></thead>
                <tbody>
                    <?php foreach ($allYears as $y): ?>
                    <tr>
                        <td><?= $y['id'] ?></td>
                        <td><strong><?= $y['year'] ?></strong></td>
                        <td><?= $y['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <!-- ==================== TAB: SUBJECTS ==================== -->
        <?php elseif ($tab === 'subjects'): ?>
            <div class="form-card">
                <h3>➕ Ajouter une Matière</h3>
                <form method="POST" action="admin.php?tab=subjects">
                    <input type="hidden" name="form_action" value="add_subject">
                    <div class="form-group">
                        <label>Nom de la matière</label>
                        <input type="text" name="subject_name" class="form-input" placeholder="Ex: Algorithmique" required>
                    </div>
                    <div class="form-group">
                        <label>Semestre</label>
                        <select name="semester_id" class="form-input" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($allSemesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>"><?= htmlspecialchars($sem['level_name']) ?> — <?= htmlspecialchars($sem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>

            <table>
                <thead><tr><th>Matière</th><th>Niveau</th><th>Semestre</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($allSubjects as $sub): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($sub['name']) ?></strong></td>
                        <td><?= htmlspecialchars($sub['level_name']) ?></td>
                        <td><?= htmlspecialchars($sub['semester_name']) ?></td>
                        <td>
                            <?php if ($sub['is_active']): ?>
                                <span class="badge badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="admin.php?tab=subjects" style="display:inline;">
                                <input type="hidden" name="form_action" value="toggle_subject">
                                <input type="hidden" name="subject_id" value="<?= $sub['id'] ?>">
                                <button type="submit" class="btn <?= $sub['is_active'] ? 'btn-warning' : 'btn-approve' ?> btn-sm">
                                    <?= $sub['is_active'] ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
