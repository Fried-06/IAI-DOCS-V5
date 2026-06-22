<?php
require 'backend/db.php';
$pdo = getDB();
$stmt = $pdo->query("SELECT s.name AS subject, sem.name AS semester, l.name AS level FROM subjects s JOIN semesters sem ON s.semester_id = sem.id JOIN levels l ON sem.level_id = l.id");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
?>
