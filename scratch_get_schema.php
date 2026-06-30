<?php
require 'backend/db.php';
$pdo = getDB();
$r = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users'");
print_r($r->fetchAll(PDO::FETCH_ASSOC));
?>
