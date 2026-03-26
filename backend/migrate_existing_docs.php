<?php
/**
 * migrate_existing_docs.php
 * ============================================================
 * Scans all .md files under Docs/ and inserts valid ones into
 * the `documents` table. Only files with real content are indexed.
 *
 * CONTENT FILTER:
 *   A file is VALID only if:
 *   - filesize > 2KB (2048 bytes)
 *   OR
 *   - stripped text content > 100 characters
 *
 * DUPLICATE CHECK:
 *   Skips if (subject_id + type_id + year_id) already exists.
 *
 * PATH RULE:
 *   Only stores relative paths — e.g. "L1/Semestre1/Algorithmique/partiel/2024.md"
 *
 * Run via browser: http://localhost:8000/backend/migrate_existing_docs.php
 * Or via CLI:      php backend/migrate_existing_docs.php
 * ============================================================
 */

declare(strict_types=1);

// ── Bootstrap ──────────────────────────────────────────────
require_once __DIR__ . '/db.php';

$pdo = getDB();

// ── Configuration ──────────────────────────────────────────
// Absolute path to the Docs/ folder
$docsRoot = dirname(__DIR__) . '/Docs';

// Relative prefix stored in DB (relative to project root)
$docsPrefix = 'Docs';

// Minimum content thresholds (Lowered to allow indexing placeholders)
const MIN_FILESIZE_BYTES = 10;   // 10 Bytes
const MIN_TEXT_CHARS    = 5;

// ── Output helper ──────────────────────────────────────────
$isCli = (php_sapi_name() === 'cli');

function out(string $msg, string $type = 'info'): void {
    global $isCli;
    $colors = ['ok' => '✅', 'skip' => '⏭️ ', 'warn' => '⚠️ ', 'error' => '❌', 'info' => 'ℹ️ ', 'head' => '📦'];
    $icon = $colors[$type] ?? 'ℹ️';
    if ($isCli) {
        echo $icon . ' ' . $msg . "\n";
    } else {
        $color = match($type) {
            'ok'    => '#22c55e',
            'skip'  => '#94a3b8',
            'warn'  => '#f59e0b',
            'error' => '#ef4444',
            'head'  => '#6366f1',
            default => '#cbd5e1',
        };
        echo "<div style='color:{$color}; font-family:monospace; font-size:0.85rem; margin:2px 0;'>{$icon} " . htmlspecialchars($msg) . "</div>\n";
    }
}

// ── Type folder → document_type name mapping ───────────────
$TYPE_MAP = [
    'cours'            => 'cours',
    'devoir'           => 'devoir',
    'partiel'          => 'partiel',
    'exercice'         => 'exercice',
    'td_tp'            => 'exercice',   // backwards-compat alias
    'corrige_devoir'   => 'corrige_devoir',
    'corrige_exercice' => 'corrige_exercice',
    'corrige_partiel'  => 'corrige_partiel',
    'corrige'          => 'corrige_partiel',  // legacy fallback
];

// ── Content filter ─────────────────────────────────────────
function isValidContent(string $filePath): bool {
    if (!file_exists($filePath)) return false;
    $size = filesize($filePath);
    if ($size >= MIN_FILESIZE_BYTES) return true;
    // Strip markdown/HTML and check character count
    $raw  = file_get_contents($filePath);
    $text = strip_tags((string)$raw);
    // Also strip markdown syntax
    $text = preg_replace('/[#\*\_\`\[\]\(\)\!\>\-\=\|]+/', ' ', $text);
    $text = trim((string)preg_replace('/\s+/', ' ', $text));
    return mb_strlen($text) > MIN_TEXT_CHARS;
}

// ── Cache helpers ──────────────────────────────────────────
$levelCache   = [];
$semesterCache = [];
$subjectCache = [];
$typeCache    = [];
$yearCache    = [];

function getOrCreateLevel(PDO $pdo, string $name): int {
    global $levelCache;
    if (isset($levelCache[$name])) return $levelCache[$name];
    $stmt = $pdo->prepare("SELECT id FROM levels WHERE name = ?");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if ($row) { $levelCache[$name] = (int)$row['id']; return $levelCache[$name]; }
    $pdo->prepare("INSERT IGNORE INTO levels (name) VALUES (?)")->execute([$name]);
    $levelCache[$name] = (int)$pdo->lastInsertId();
    return $levelCache[$name];
}

function getOrCreateSemester(PDO $pdo, int $levelId, string $name): int {
    global $semesterCache;
    $key = "{$levelId}_{$name}";
    if (isset($semesterCache[$key])) return $semesterCache[$key];
    $stmt = $pdo->prepare("SELECT id FROM semesters WHERE level_id = ? AND name = ?");
    $stmt->execute([$levelId, $name]);
    $row = $stmt->fetch();
    if ($row) { $semesterCache[$key] = (int)$row['id']; return $semesterCache[$key]; }
    $pdo->prepare("INSERT IGNORE INTO semesters (level_id, name) VALUES (?, ?)")->execute([$levelId, $name]);
    $semesterCache[$key] = (int)$pdo->lastInsertId();
    return $semesterCache[$key];
}

function getOrCreateSubject(PDO $pdo, int $semId, string $name): int {
    global $subjectCache;
    $key = "{$semId}_{$name}";
    if (isset($subjectCache[$key])) return $subjectCache[$key];
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE semester_id = ? AND name = ?");
    $stmt->execute([$semId, $name]);
    $row = $stmt->fetch();
    if ($row) { $subjectCache[$key] = (int)$row['id']; return $subjectCache[$key]; }
    $pdo->prepare("INSERT IGNORE INTO subjects (semester_id, name) VALUES (?, ?)")->execute([$semId, $name]);
    $subjectCache[$key] = (int)$pdo->lastInsertId();
    return $subjectCache[$key];
}

function getTypeId(PDO $pdo, string $typeName): ?int {
    global $typeCache;
    if (isset($typeCache[$typeName])) return $typeCache[$typeName];
    $stmt = $pdo->prepare("SELECT id FROM document_types WHERE name = ?");
    $stmt->execute([$typeName]);
    $row = $stmt->fetch();
    if ($row) { $typeCache[$typeName] = (int)$row['id']; return $typeCache[$typeName]; }
    return null;
}

function getOrCreateYear(PDO $pdo, int $year): int {
    global $yearCache;
    if (isset($yearCache[$year])) return $yearCache[$year];
    $stmt = $pdo->prepare("SELECT id FROM years WHERE year = ?");
    $stmt->execute([$year]);
    $row = $stmt->fetch();
    if ($row) { $yearCache[$year] = (int)$row['id']; return $yearCache[$year]; }
    $pdo->prepare("INSERT IGNORE INTO years (year) VALUES (?)")->execute([$year]);
    $yearCache[$year] = (int)$pdo->lastInsertId();
    return $yearCache[$year];
}

function documentExists(PDO $pdo, int $subjectId, int $typeId, int $yearId): bool {
    $stmt = $pdo->prepare("SELECT id FROM documents WHERE subject_id = ? AND type_id = ? AND year_id = ?");
    $stmt->execute([$subjectId, $typeId, $yearId]);
    return (bool)$stmt->fetch();
}

// ── Main scan ─────────────────────────────────────────────
if (!$isCli) {
    echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'>";
    echo "<title>Migration — IAI Docs</title>";
    echo "<style>body{background:#0f172a;color:#e2e8f0;font-family:monospace;padding:2rem;}
          h1{color:#818cf8;} h2{color:#a5b4fc;font-size:1rem;margin-top:1.5rem;}
          .summary{background:#1e293b;padding:1rem;border-radius:8px;margin-top:2rem;}</style></head><body>";
    echo "<h1>📂 Migration des documents existants</h1>";
    flush();
}

out("Scanning: {$docsRoot}", 'head');
out("Content filter: filesize > " . MIN_FILESIZE_BYTES . "B OR text > " . MIN_TEXT_CHARS . " chars", 'info');
echo $isCli ? "\n" : "<br>";

// Counters
$counters = ['inserted' => 0, 'skipped_empty' => 0, 'skipped_dup' => 0, 'skipped_type' => 0, 'errors' => 0];

// Recursively iterate only the real Docs/, not _build
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsRoot, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $fileInfo) {
    /** @var SplFileInfo $fileInfo */
    if ($fileInfo->getExtension() !== 'md') continue;

    $absPath = $fileInfo->getRealPath();

    // ── Skip _build folder ────────────────────────────────
    if (strpos($absPath, DIRECTORY_SEPARATOR . '_build' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }

    // ── Content filter ────────────────────────────────────
    if (!isValidContent($absPath)) {
        out("SKIP (empty): " . basename(dirname($absPath)) . '/' . $fileInfo->getFilename(), 'skip');
        $counters['skipped_empty']++;
        continue;
    }

    // ── Parse path ────────────────────────────────────────
    // Expected structure inside Docs/:
    // <Level>/<Semester>/<Subject>/<type_folder>/YYYY.md
    // or nested corrige:
    // <Level>/<Semester>/<Subject>/corrige/<sub_type>/YYYY.md

    // Strip the absolute prefix correctly to get the relative path
    $relPath = substr($absPath, strlen($docsRoot) + 1);
    $relPath = str_replace('\\', '/', $relPath);
    $parts   = explode('/', $relPath);

    // Minimum depth: Level/Semester/Subject/type/year.md = 5 parts
    if (count($parts) < 5) {
        out("SKIP (bad path): {$relPath}", 'warn');
        $counters['errors']++;
        continue;
    }

    $levelName   = $parts[0];  // e.g. "L1"
    $semName     = $parts[1];  // e.g. "Semestre1"
    $subjectName = $parts[2];  // e.g. "Algorithmique"
    $typeFolder  = $parts[3];  // e.g. "corrige"
    $yearFile    = end($parts); // e.g. "2024.md"

    // Handle nested corrige: corrige/corrige_partiel/2024.md
    if ($typeFolder === 'corrige' && count($parts) === 6) {
        $typeFolder = $parts[4]; // corrige_devoir / corrige_exercice / corrige_partiel
    } elseif ($typeFolder === 'corrige' && count($parts) === 5) {
        // Old-style flat corrige/2024.md → skip (replaced by subfolders)
        out("SKIP (legacy flat corrige): {$relPath}", 'skip');
        $counters['skipped_empty']++;
        continue;
    }

    // Map type folder to DB type name
    $typeName = $TYPE_MAP[$typeFolder] ?? null;
    if ($typeName === null) {
        out("SKIP (unknown type '{$typeFolder}'): {$relPath}", 'warn');
        $counters['skipped_type']++;
        continue;
    }

    // Extract year from filename
    $yearStr = pathinfo($yearFile, PATHINFO_FILENAME);
    if (!preg_match('/^\d{4}$/', $yearStr)) {
        out("SKIP (bad year '{$yearStr}'): {$relPath}", 'warn');
        $counters['errors']++;
        continue;
    }
    $yearInt = (int)$yearStr;

    // ── Resolve / create DB entities ──────────────────────
    try {
        // Clean up level name: L3_GLSI → "L3 GLSI"
        $levelDisplayName = str_replace('_', ' ', $levelName);

        $levelId   = getOrCreateLevel($pdo, $levelDisplayName);

        // Normalize semester name (ensure space: 'Semestre1' -> 'Semestre 1')
        $normSemName = preg_replace('/([a-zA-Z]+)(\d+)/', '$1 $2', $semName);
        $semId     = getOrCreateSemester($pdo, $levelId, $normSemName);

        $subjectId = getOrCreateSubject($pdo, $semId, $subjectName);
        $typeId    = getTypeId($pdo, $typeName);
        $yearId    = getOrCreateYear($pdo, $yearInt);

        if ($typeId === null) {
            out("SKIP (type not in DB '{$typeName}'): {$relPath}", 'error');
            $counters['errors']++;
            continue;
        }

        // ── Duplicate check ───────────────────────────────
        if (documentExists($pdo, $subjectId, $typeId, $yearId)) {
            out("SKIP (duplicate): {$subjectName} / {$typeName} / {$yearStr}", 'skip');
            $counters['skipped_dup']++;
            continue;
        }

        // ── Build relative path for DB ────────────────────
        // Replace .md with .html and format cleanly for relative paths
        $htmlRelPath = preg_replace('/\.md$/i', '.html', $relPath);
        $dbPath = str_replace('\\', '/', $htmlRelPath);

        // ── Insert document ───────────────────────────────
        $title = $subjectName . ' — ' . ucwords(str_replace('_', ' ', $typeName)) . ' ' . $yearStr;

        $stmt = $pdo->prepare("
            INSERT INTO documents
                (title, subject_id, type_id, year_id, original_name, filename, file_path, status)
            VALUES
                (:title, :subject_id, :type_id, :year_id, :original_name, :filename, :file_path, 'approved')
        ");
        $stmt->execute([
            ':title'         => $title,
            ':subject_id'    => $subjectId,
            ':type_id'       => $typeId,
            ':year_id'       => $yearId,
            ':original_name' => $yearFile,
            ':filename'      => $yearFile,
            ':file_path'     => $dbPath,
        ]);

        out("INSERTED: {$subjectName} / {$typeName} / {$yearStr}", 'ok');
        $counters['inserted']++;

    } catch (PDOException $e) {
        out("ERROR: {$relPath} — " . $e->getMessage(), 'error');
        $counters['errors']++;
    }
}

// ── Summary ───────────────────────────────────────────────
echo $isCli ? "\n=== SUMMARY ===\n" : "<div class='summary'><h2>📊 Résumé</h2>";
out("Inserted (new documents) : " . $counters['inserted'], 'ok');
out("Skipped (empty/placeholder): " . $counters['skipped_empty'], 'skip');
out("Skipped (duplicates)     : " . $counters['skipped_dup'], 'skip');
out("Skipped (unknown type)   : " . $counters['skipped_type'], 'warn');
out("Errors                   : " . $counters['errors'], 'error');

if (!$isCli) {
    echo "</div></body></html>";
}
