<?php
require_once __DIR__ . '/db.php';

echo "Démarrage de la reconstruction de l'architecture...\n";

try {
    $pdo = getDB();
    
    $baseDir = dirname(__DIR__); // iai-resources
    $pagesDir = $baseDir . '/pages';
    $subjectsDir = $baseDir . '/subjects';
    $docsDir = $baseDir . '/Docs';

    // Helper: DB name is already PascalCase_With_Underscores — USE IT AS-IS for Sphinx dirs
    // For HTML slugs in subjects/ we need lowercase-hyphenated
    function htmlSlug($dbName) {
        return strtolower(str_replace('_', '-', $dbName));
    }
    
    // Display-friendly name: replace underscores with spaces
    function displayName($dbName) {
        return str_replace('_', ' ', $dbName);
    }

    function sanitizeDirName($text) {
        return str_replace(' ', '_', trim($text)); 
    }

    function getHtmlBoilerplate($title, $depth, $contentHtml) {
        $navPath = str_repeat("../", $depth);
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Ressources IAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{$navPath}css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="{$navPath}index.html" class="logo">Ressources IAI</a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="{$navPath}index.html" class="nav-item">Accueil</a></li>
                    <li><a href="{$navPath}search.html" class="nav-item">Examens</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container" style="padding: 4rem 0;">
        $contentHtml
    </main>
    <script>
        function showYear(year) {
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.year-panel').forEach(panel => panel.classList.remove('active'));
            
            const btn = document.querySelector('.year-btn[data-year="' + year + '"]');
            const panel = document.getElementById('panel-' + year);
            
            if(btn) btn.classList.add('active');
            if(panel) panel.classList.add('active');
        }
    </script>
</body>
</html>
HTML;
    }

    // 1. Fetch DB Data
    $yearsStmt = $pdo->query("SELECT year FROM years ORDER BY year DESC");
    $allYears = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

    $levelsStmt = $pdo->query("SELECT * FROM levels ORDER BY id");
    $levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($levels as $level) {
        $levelName = $level['name']; // "L1", "L2", "L3 GLSI", "L3 ASR"

        // HTML pages directory key
        $htmlDirKey = $levelName;
        if ($levelName == "L3 GLSI") $htmlDirKey = "L3/GLSI";
        elseif ($levelName == "L3 ASR") $htmlDirKey = "L3/ASR";

        // Sphinx Docs directory key
        $docsLevelKey = sanitizeDirName($levelName); // "L1", "L2", "L3_GLSI", "L3_ASR"

        $lvlDirPath = $pagesDir . '/' . $htmlDirKey;
        if (!is_dir($lvlDirPath)) mkdir($lvlDirPath, 0777, true);

        // Fetch Semesters
        $semStmt = $pdo->prepare("SELECT * FROM semesters WHERE level_id = ? ORDER BY name");
        $semStmt->execute([$level['id']]);
        $semesters = $semStmt->fetchAll();

        // Level HTML
        $htmlContent = "<h1 style='color: var(--primary); margin-bottom: 2rem; text-align: center;'>$levelName</h1><div class='grid grid-cols-2'>";
        foreach ($semesters as $sem) {
            $semName = $sem['name'];
            $semSlug = strtolower(str_replace(' ', '', $semName)); // "semestre1"
            $htmlContent .= "
            <a href='{$semSlug}.html' class='level-card'>
                <div class='level-icon'>
                    <svg width='32' height='32' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 6v6m0 0v6m0-6h6m-6 0H6'></path></svg>
                </div>
                <div>
                    <div class='level-title'>$semName</div>
                    <div class='level-desc'>Voir les matières de ce semestre</div>
                </div>
            </a>";
        }
        $htmlContent .= "</div>";
        
        $depth = strpos($htmlDirKey, '/') !== false ? 3 : 2;
        file_put_contents("$lvlDirPath/index.html", getHtmlBoilerplate($levelName, $depth, $htmlContent));

        // Process Semesters
        foreach ($semesters as $sem) {
            $semName = $sem['name'];
            $semSlug = strtolower(str_replace(' ', '', $semName)); // "semestre1"
            $docsSemKey = str_replace(' ', '', $semName); // "Semestre1" (preserving case from DB)

            // Fetch Subjects (only active)
            $subjStmt = $pdo->prepare("SELECT * FROM subjects WHERE semester_id = ? AND is_active = 1 ORDER BY name");
            $subjStmt->execute([$sem['id']]);
            $subjects = $subjStmt->fetchAll();

            $htmlContentSem = "<h1 style='color: var(--primary); margin-bottom: 2rem;'><a href='index.html' style='color: var(--text-muted); font-size: 1.2rem;'>$levelName</a> &gt; $semName</h1><div class='grid grid-cols-3'>";

            $subjDirPath = $subjectsDir . '/' . $htmlDirKey . '/' . $semSlug;
            if (!is_dir($subjDirPath)) mkdir($subjDirPath, 0777, true);

            foreach ($subjects as $subj) {
                $subjDbName = $subj['name'];           // "Administration_BD" — exactly matches Sphinx folder name
                $subjDisplay = displayName($subjDbName); // "Administration BD" — human-readable
                $subjHtmlSlug = htmlSlug($subjDbName);   // "administration-bd" — for subjects/*.html
                $docsSubjKey = $subjDbName;              // USE AS-IS for Sphinx Docs/ directories
                
                $createdYear = intval(date('Y', strtotime($subj['created_at'])));
                if ($createdYear < 2000) $createdYear = 2000;

                // Semester page link to subject
                $linkDepth = strpos($htmlDirKey, 'L3') !== false ? "../../../" : "../../";
                $htmlContentSem .= "
                <a href='{$linkDepth}subjects/{$htmlDirKey}/{$semSlug}/{$subjHtmlSlug}.html' class='exam-card' style='text-align: center; border-top: 4px solid var(--primary-light);'>
                    <h3 class='exam-title' style='margin-top: 1rem; margin-bottom: 1rem;'>$subjDisplay</h3>
                    <span class='btn btn-outline' style='width: 100%;'>Voir les ressources</span>
                </a>";

                // --- Subject HTML Page ---
                $validYears = array_filter($allYears, function($y) use ($createdYear) { return $y >= $createdYear; });
                
                $htmlSubj = "
                <div class='subject-header'>
                    <h1 style='color: var(--primary); margin-bottom: 0.5rem;'>$subjDisplay</h1>
                    <p style='color: var(--text-muted); margin-bottom: 1rem;'>$levelName - $semName</p>
                    <div style='background: var(--bg2); padding: 1rem; border-radius: 8px;'>
                        <p><strong>Description complète :</strong> Ressources académiques relatives à $subjDisplay.</p>
                    </div>
                </div>
                <div class='subject-layout'>
                    <aside class='sidebar'>
                        <div class='sidebar-title'>Années Académiques</div>
                        <div class='year-list'>";

                $firstYear = reset($validYears);
                foreach ($validYears as $year) {
                    $activeClass = ($year == $firstYear) ? "active" : "";
                    $htmlSubj .= "<button class='year-btn $activeClass' data-year='$year' onclick='showYear($year)'>Année $year</button>\n";
                }
                $htmlSubj .= "</div></aside><div class='subject-content year-panels'>";

                // Relative path from subjects page to Docs/_build/html
                $docsRelPath = strpos($htmlDirKey, 'L3') !== false ? "../../../../Docs/_build/html" : "../../../Docs/_build/html";
                $docsBase = "{$docsRelPath}/{$docsLevelKey}/{$docsSemKey}/{$docsSubjKey}";

                foreach ($validYears as $year) {
                    $activeClassPanel = ($year == $firstYear) ? "active" : "";
                    $htmlSubj .= "
                    <div id='panel-$year' class='year-panel $activeClassPanel'>
                        <h2 style='color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;'>Ressources de l'année $year</h2>
                        <div class='resource-grid'>
                            <div class='resource-card'><h3 style='margin-bottom: 1rem; color: var(--text-main);'>Devoirs</h3><p style='font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;'>Sujets de devoirs surveillés et TPs.</p><a href='$docsBase/devoir/$year.html' class='btn btn-outline' style='width: 100%; font-size: 0.85rem;'>Consulter</a></div>
                            <div class='resource-card'><h3 style='margin-bottom: 1rem; color: var(--text-main);'>Partiels</h3><p style='font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;'>Examens finaux et de session normale.</p><a href='$docsBase/partiel/$year.html' class='btn btn-outline' style='width: 100%; font-size: 0.85rem;'>Consulter</a></div>
                            <div class='resource-card'><h3 style='margin-bottom: 1rem; color: var(--text-main);'>Corrigé Partiel</h3><p style='font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;'>Solutions des examens partiels.</p><a href='$docsBase/corrige/corrige_partiel/$year.html' class='btn btn-outline' style='width: 100%; font-size: 0.85rem;'>Consulter</a></div>
                            <div class='resource-card'><h3 style='margin-bottom: 1rem; color: var(--text-main);'>Corrigé Devoir</h3><p style='font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;'>Solutions des devoirs surveillés.</p><a href='$docsBase/corrige/corrige_devoir/$year.html' class='btn btn-outline' style='width: 100%; font-size: 0.85rem;'>Consulter</a></div>
                        </div>
                    </div>";
                }
                $htmlSubj .= "</div></div>";

                $subjDepth = strpos($htmlDirKey, 'L3') !== false ? 5 : 4;
                file_put_contents("{$subjDirPath}/{$subjHtmlSlug}.html", getHtmlBoilerplate($subjDisplay, $subjDepth, $htmlSubj));


                // --- Sphinx Structure ---
                $sphinxSubjDir = "{$docsDir}/{$docsLevelKey}/{$docsSemKey}/{$docsSubjKey}";
                $types = ['devoir', 'partiel', 'corrige'];
                foreach ($types as $t) {
                    if (!is_dir("$sphinxSubjDir/$t")) mkdir("$sphinxSubjDir/$t", 0777, true);
                    
                    if ($t === 'corrige') {
                        if (!is_dir("$sphinxSubjDir/$t/corrige_partiel")) mkdir("$sphinxSubjDir/$t/corrige_partiel", 0777, true);
                        if (!is_dir("$sphinxSubjDir/$t/corrige_devoir")) mkdir("$sphinxSubjDir/$t/corrige_devoir", 0777, true);
                        
                        file_put_contents("$sphinxSubjDir/$t/index.rst", "Corrigés\n========\n\n.. toctree::\n   :maxdepth: 2\n\n   corrige_partiel/index\n   corrige_devoir/index\n");
                        file_put_contents("$sphinxSubjDir/$t/corrige_partiel/index.rst", "Corrige Partiel\n===============\n\n.. toctree::\n   :maxdepth: 1\n   :glob:\n\n   *\n");
                        file_put_contents("$sphinxSubjDir/$t/corrige_devoir/index.rst", "Corrige Devoir\n==============\n\n.. toctree::\n   :maxdepth: 1\n   :glob:\n\n   *\n");
                        
                        foreach ($validYears as $year) {
                            $filePath = "$sphinxSubjDir/$t/corrige_partiel/$year.md";
                            if (!file_exists($filePath)) file_put_contents($filePath, "# $subjDisplay - Corrigé Partiel $year\n\nContenu en attente de contribution.");
                            $filePath2 = "$sphinxSubjDir/$t/corrige_devoir/$year.md";
                            if (!file_exists($filePath2)) file_put_contents($filePath2, "# $subjDisplay - Corrigé Devoir $year\n\nContenu en attente de contribution.");
                        }
                    } else {
                        file_put_contents("$sphinxSubjDir/$t/index.rst", ucfirst($t) . "s\n" . str_repeat('=', strlen($t)+1) . "\n\n.. toctree::\n   :maxdepth: 1\n   :glob:\n\n   *\n");
                        foreach ($validYears as $year) {
                            $filePath = "$sphinxSubjDir/$t/$year.md";
                            if (!file_exists($filePath)) file_put_contents($filePath, "# $subjDisplay - " . ucfirst($t) . " $year\n\nContenu en attente de contribution.");
                        }
                    }
                }
                
                // Subject index.rst
                $rstTitle = $subjDisplay . "\n" . str_repeat('=', strlen($subjDisplay));
                $indexContent = <<<RST
$rstTitle

.. toctree::
   :maxdepth: 2
   :caption: Ressources disponibles

   partiel/index
   devoir/index
   corrige/index
RST;
                file_put_contents("$sphinxSubjDir/index.rst", $indexContent);
            }
            
            // Semester HTML page
            $htmlContentSem .= "</div>";
            file_put_contents("$lvlDirPath/{$semSlug}.html", getHtmlBoilerplate("$levelName - $semName", $depth, $htmlContentSem));
            
            // Semester index.rst for Sphinx
            $semRstTitle = $semName . "\n" . str_repeat('=', strlen($semName));
            $docsSemPath = "{$docsDir}/{$docsLevelKey}/{$docsSemKey}";
            if (!is_dir($docsSemPath)) mkdir($docsSemPath, 0777, true);
            $semToc = $semRstTitle . "\n\n.. toctree::\n   :maxdepth: 2\n\n";
            foreach ($subjects as $subj) {
                 $semToc .= "   {$subj['name']}/index\n"; // Use DB name directly
            }
            file_put_contents("$docsSemPath/index.rst", $semToc);
        }
        
        // Level index.rst for Sphinx
        $docsLvlPath = "{$docsDir}/{$docsLevelKey}";
        if (!is_dir($docsLvlPath)) mkdir($docsLvlPath, 0777, true);
        $lvlRstTitle = $levelName . "\n" . str_repeat('=', strlen($levelName));
        $lvlToc = $lvlRstTitle . "\n\n.. toctree::\n   :maxdepth: 2\n\n";
        foreach ($semesters as $sem) {
            $docsSemKey = str_replace(' ', '', $sem['name']);
            $lvlToc .= "   $docsSemKey/index\n";
        }
        file_put_contents("$docsLvlPath/index.rst", $lvlToc);
    }
    
    // Master index.rst
    $docsToc = "IAI DOCS — Documentation Académique\n=====================================\n\nBienvenue sur la documentation centralisée de l'IAI Togo.\n\n.. toctree::\n   :maxdepth: 3\n   :caption: Niveaux Académiques\n\n";
    foreach ($levels as $level) {
        $docsLevelKey = sanitizeDirName($level['name']);
        $docsToc .= "   $docsLevelKey/index\n";
    }
    file_put_contents("$docsDir/index.rst", $docsToc);
    
    echo "Reconstruction terminée avec succès.\n";
} catch (Exception $e) {
    echo "Erreur lors de la reconstruction: " . $e->getMessage() . "\n";
}
?>
