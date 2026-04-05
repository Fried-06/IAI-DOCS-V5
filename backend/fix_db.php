<?php
require_once __DIR__ . '/db.php';

$pdo = getDB();

$slugify = function($text) {
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($ascii === false) {
        $map = ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a',
                'ä'=>'a','î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u',
                'û'=>'u','ü'=>'u','ç'=>'c','É'=>'E','È'=>'E','Ê'=>'E',
                'À'=>'A','Â'=>'A','Î'=>'I','Ô'=>'O','Ù'=>'U','Û'=>'U','Ç'=>'C'];
        $ascii = strtr($text, $map);
    }
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', str_replace(' ', '', $ascii));
};

$stmt = $pdo->query("SELECT d.id, d.file_path, s.name as subject_name, sem.name as semester_name, l.name as level_name, dt.name as type_name, y.year FROM documents d JOIN subjects s ON d.subject_id = s.id JOIN semesters sem ON s.semester_id = sem.id JOIN levels l ON sem.level_id = l.id JOIN document_types dt ON d.type_id = dt.id JOIN years y ON d.year_id = y.id WHERE d.status = 'approved'");

$updates = 0;
while ($doc = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $levelSlug    = $slugify(str_replace('Licence ', 'L', trim($doc['level_name'])));
    $semesterSlug = $slugify(trim($doc['semester_name']));
    $subjectSlug  = $slugify(trim($doc['subject_name']));
    
    $typeSlug = $doc['type_name'];
    if (strpos($typeSlug, 'corrige_') === 0) {
        $typeSlug = 'corrige/' . $typeSlug;
    }
    
    $year = intval($doc['year']);
    
    $relativePath = "$levelSlug/$semesterSlug/$subjectSlug/$typeSlug/$year.html";
    
    if ($doc['file_path'] !== $relativePath) {
        echo "Updating ID {$doc['id']} file_path from '{$doc['file_path']}' to '{$relativePath}'\n";
        $updateStmt = $pdo->prepare("UPDATE documents SET file_path = ? WHERE id = ?");
        $updateStmt->execute([$relativePath, $doc['id']]);
        $updates++;
    }
}

echo "Total paths updated: $updates\n";
?>
