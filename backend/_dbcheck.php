<?php
require_once __DIR__ . '/db.php';
$pdo = getDB();

echo "=== SUBJECTS ===\n";
$rows = $pdo->query("SELECT s.id, s.name, s.created_at, sem.name as sem_name, l.name as lvl_name FROM subjects s JOIN semesters sem ON s.semester_id=sem.id JOIN levels l ON sem.level_id=l.id ORDER BY l.name, sem.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "{$r['id']} | {$r['lvl_name']} | {$r['sem_name']} | {$r['name']} | created: {$r['created_at']}\n";
}

echo "\n=== YEARS ===\n";
$years = $pdo->query("SELECT * FROM years ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($years as $y) {
    echo "{$y['id']} | {$y['year']} | {$y['created_at']}\n";
}

echo "\n=== LEVELS ===\n";
$levels = $pdo->query("SELECT * FROM levels ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($levels as $l) {
    echo "{$l['id']} | {$l['name']}\n";
}

echo "\n=== SEMESTERS ===\n";
$sems = $pdo->query("SELECT s.*, l.name as lvl FROM semesters s JOIN levels l ON s.level_id=l.id ORDER BY l.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sems as $s) {
    echo "{$s['id']} | {$s['lvl']} | {$s['name']}\n";
}
?>
