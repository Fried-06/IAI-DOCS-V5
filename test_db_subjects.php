<?php
require_once __DIR__ . '/backend/db.php';
$pdo = getDB();

$stmt = $pdo->query("SELECT s.name AS subject_name, sem.name AS sem_name, l.name AS level_name FROM subjects s JOIN semesters sem ON s.semester_id = sem.id JOIN levels l ON sem.level_id = l.id ORDER BY l.name, sem.name, s.name");
$subjects = $stmt->fetchAll();
foreach ($subjects as $s) {
    echo $s['level_name'] . " -> " . $s['sem_name'] . " -> " . $s['subject_name'] . "\n";
}
