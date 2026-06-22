$baseDir = "C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

$structure = @{
    "L1" = @{
        "titre" = "Licence 1"
        "dbName" = "L1"
        "semestres" = @{
            "semestre1" = @{
                "titre" = "Semestre 1"
                "dbName" = "Semestre 1"
                "matieres" = @( "algorithmique", "langage-c", "architecture-maintenance", "electronique-numerique", "mathematiques-discretes", "analyse-mathematiques", "suite-ic3-microsoft", "gnu-linux", "anglais-informatique", "expression-ecrite-orale" )
                "noms" = @{
                    "algorithmique" = "Algorithmique"
                    "langage-c" = "Langage C"
                    "architecture-maintenance" = "Architecture et Maintenance"
                    "electronique-numerique" = "Electronique Numerique"
                    "mathematiques-discretes" = "Mathematiques Discretes"
                    "analyse-mathematiques" = "Analyse Mathematique"
                    "suite-ic3-microsoft" = "Suite IC3 Microsoft"
                    "gnu-linux" = "GNU Linux"
                    "anglais-informatique" = "Anglais Informatique"
                    "expression-ecrite-orale" = "Expression Ecrite et Orale"
                }
            }
            "semestre2" = @{
                "titre" = "Semestre 2"
                "dbName" = "Semestre 2"
                "matieres" = @("programmation-web","programmation-objet","conception-sd","ccna1a","ccna1b","bases-de-donnees","pratique-sql","environnement-economique","comptabilite","projet-professionnel")
                "noms" = @{
                    "programmation-web" = "Programmation Web"
                    "programmation-objet" = "Programmation Objet"
                    "conception-sd" = "Conception de Systeme d'Information"
                    "ccna1a" = "CCNA 1A"
                    "ccna1b" = "CCNA 1B"
                    "bases-de-donnees" = "Bases de Donnees"
                    "pratique-sql" = "Pratique SQL"
                    "environnement-economique" = "Environnement Economique"
                    "comptabilite" = "Comptabilite"
                    "projet-professionnel" = "Projet Professionnel"
                }
            }
        }
    }
    "L2" = @{
        "titre" = "Licence 2"
        "dbName" = "L2"
        "semestres" = @{
            "semestre3" = @{
                "titre" = "Semestre 3"
                "dbName" = "Semestre 3"
                "matieres" = @("merise","methodes-agiles","uml","implementation-bd","xml-web-services","probabilites-statistiques","recherche-operationnelle","theorie-graphes","algebre-lineaire","cryptographie","ccna2","securite-informatique","programmation-web-2","python-dev","java-poo")
                "noms" = @{
                    "merise" = "Merise"
                    "methodes-agiles" = "Methodes Agiles"
                    "uml" = "UML"
                    "implementation-bd" = "Implementation de BD"
                    "xml-web-services" = "XML et Web Services"
                    "probabilites-statistiques" = "Probabilites et Statistiques"
                    "recherche-operationnelle" = "Recherche Operationnelle"
                    "theorie-graphes" = "Theorie des Graphes"
                    "algebre-lineaire" = "Algebre Lineaire"
                    "cryptographie" = "Cryptographie"
                    "ccna2" = "CCNA 2"
                    "securite-informatique" = "Securite Informatique"
                    "programmation-web-2" = "Programmation Web 2 (PHP)"
                    "python-dev" = "Developpement Python"
                    "java-poo" = "Java POO"
                }
            }
            "semestre4" = @{
                "titre" = "Semestre 4"
                "dbName" = "Semestre 4"
                "matieres" = @("anglais-scientifique","techniques-communication","redaction-scientifique","maintenance-informatique","electronique-appliquee","programmation-mobile","csharp","cloud-computing","tic-management","droit-tic")
                "noms" = @{
                    "anglais-scientifique" = "Anglais Scientifique"
                    "techniques-communication" = "Techniques de Communication"
                    "redaction-scientifique" = "Redaction Scientifique"
                    "maintenance-informatique" = "Maintenance Informatique"
                    "electronique-appliquee" = "Electronique Appliquee"
                    "programmation-mobile" = "Programmation Mobile"
                    "csharp" = "C#"
                    "cloud-computing" = "Cloud Computing"
                    "tic-management" = "Management des TIC"
                    "droit-tic" = "Droit des TIC"
                }
            }
        }
    }
    "L3-GLSI" = @{
        "titre" = "Licence 3 GLSI"
        "dbName" = "L3 GLSI"
        "semestres" = @{
            "semestre5" = @{
                "titre" = "Semestre 5"
                "dbName" = "Semestre 5"
                "matieres" = @("sig","big-data","aide-decision","programmation-jee","programmation-distribuee","administration-oracle","securite-bd","administration-sqlserver","introduction-ia","analyse-donnees","genie-logiciel","anglais-expert","developpement-personnel")
                "noms" = @{
                    "sig" = "Systeme d'Information Geographique"
                    "big-data" = "Big Data (NoSQL)"
                    "aide-decision" = "Systeme d'Information d'Aide a la Decision"
                    "programmation-jee" = "Programmation JEE (Spring Boot, JSP)"
                    "programmation-distribuee" = "Programmation Distribuee (Python/Java)"
                    "administration-oracle" = "Administration des BD Oracle"
                    "securite-bd" = "Securite des Bases de Donnees"
                    "administration-sqlserver" = "Administration des BD SQL-Server"
                    "introduction-ia" = "Intro a l'Intelligence Artificielle"
                    "analyse-donnees" = "Analyse de Donnees"
                    "genie-logiciel" = "Introduction au Genie Logiciel"
                    "anglais-expert" = "Anglais Expert"
                    "developpement-personnel" = "Developpement Personnel"
                }
            }
            "semestre6" = @{
                "titre" = "Semestre 6"
                "dbName" = "Semestre 6"
                "matieres" = @("creation-entreprises","droit-travail","poo-avancee","outils-programmation-web","audit-si","techniques-multimedia")
                "noms" = @{
                    "creation-entreprises" = "Creation d'Entreprises"
                    "droit-travail" = "Droit du Travail"
                    "poo-avancee" = "POO Avancee (Django, Flask)"
                    "outils-programmation-web" = "Outils de Programmation Web (Laravel, Node)"
                    "audit-si" = "Audit des Systemes d'Informations"
                    "techniques-multimedia" = "Techniques Multimedia et Infographie"
                }
            }
        }
    }
    "L3-ASR" = @{
        "titre" = "Licence 3 ASR"
        "dbName" = "L3 ASR"
        "semestres" = @{
            "semestre5" = @{
                "titre" = "Semestre 5"
                "dbName" = "Semestre 5"
                "matieres" = @("programmation-systeme","linux-avance","ccna3","huawei-network","association","administration-reseaux","administration-systemes","big-data-asr","securite-reseaux","anglais-expert-asr","toeic","developpement-personnel-asr")
                "noms" = @{
                    "programmation-systeme" = "Programmation Systeme"
                    "linux-avance" = "Systeme d'Exploitation Avance (Linux)"
                    "ccna3" = "Reseau et Technologie CISCO CCNA 3"
                    "huawei-network" = "Huawei Certification Network"
                    "association" = "Association"
                    "administration-reseaux" = "Administration des Reseaux"
                    "administration-systemes" = "Administration des Systemes"
                    "big-data-asr" = "Big Data"
                    "securite-reseaux" = "Securite Reseaux"
                    "anglais-expert-asr" = "Anglais Expert"
                    "toeic" = "Preparation TOEIC"
                    "developpement-personnel-asr" = "Developpement Personnel"
                }
            }
            "semestre6" = @{
                "titre" = "Semestre 6"
                "dbName" = "Semestre 6"
                "matieres" = @("creation-entreprises-asr","droit-travail-asr","modele-structuration-reseaux","architectures-avancees-reseaux","reseaux-mobiles","fibre-optique","audit-systemes-reseaux","techniques-multimedia-asr")
                "noms" = @{
                    "creation-entreprises-asr" = "Creation d'Entreprises"
                    "droit-travail-asr" = "Droit du Travail"
                    "modele-structuration-reseaux" = "Modele de Structuration des Reseaux"
                    "architectures-avancees-reseaux" = "Architectures Avancees des Reseaux"
                    "reseaux-mobiles" = "Reseaux Mobiles"
                    "fibre-optique" = "Fibre Optique"
                    "audit-systemes-reseaux" = "Audit des Systemes et Reseaux"
                    "techniques-multimedia-asr" = "Techniques Multimedia et Infographie"
                }
            }
        }
    }
}

# ─────────────────────────────────────────────────
# NAVBAR TEMPLATE (absolute URLs — clean URLs)
# ─────────────────────────────────────────────────
function Get-Navbar {
    return @'
    <nav class="navbar">
        <div class="container nav-container">
            <a href="/Accueil" class="logo" style="padding:0;display:flex;align-items:center;">
                <img src="/assets/iai_docs_moderne.png" alt="Logo IAI" style="height:200px;width:auto;object-fit:contain;">
            </a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="/Accueil" class="nav-item">Accueil</a></li>
                    <li><a href="/Examens" class="nav-item">Examens</a></li>
                    <li><a href="/Rechercher" class="nav-item">Rechercher</a></li>
                    <li><a href="/Contribuer" class="nav-item">Contribuer</a></li>
                </ul>
                <div class="nav-actions">
                    <button class="theme-toggle" id="theme-toggle" title="Basculer le theme">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    <a href="/Connexion" class="btn btn-outline auth-login-btn" style="padding:0.5rem 1rem;border:none;">Connexion</a>
                    <a href="/Connexion" class="btn btn-primary auth-register-btn" style="padding:0.5rem 1rem;">S'inscrire</a>
                    <a href="/Profil" class="btn btn-outline" id="btn-profil" style="display:none;">Profil</a>
                </div>
            </div>
            <button class="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>
'@
}

# ─────────────────────────────────────────────────
# HEAD / FOOT for level & semester pages (HTML)
# ─────────────────────────────────────────────────
function Get-HtmlBoilerplate {
    param([string]$title, [string]$contentHtml)
    $navbar = Get-Navbar
    return @"
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Ressources IAI</title>
    <link rel="stylesheet" href="/css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="/js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
$navbar
    <main class="container" style="padding: 4rem 0;">
        $contentHtml
    </main>
    <script src="/js/main.js?v=2"></script>
</body>
</html>
"@
}

# ─────────────────────────────────────────────────
# PHP TEMPLATE for Subject pages (dynamic cards)
# ─────────────────────────────────────────────────
function Get-SubjectPhpPage {
    param(
        [string]$subjectName,
        [string]$levelName,
        [string]$semesterName,
        [string]$dbLevelName,
        [string]$dbSemesterName,
        [string]$dbSubjectName
    )
    $navbar = Get-Navbar

    return @"
<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../../backend/db.php';

// ── Fetch documents for this specific subject from the DB ──
\$documents = [];
\$totalDocs = 0;
try {
    \$pdo = getDB();
    \$stmt = \$pdo->prepare(
        "SELECT d.id, d.title, d.file_path, d.pdf_url, d.status,
                s.name AS subject_name,
                sem.name AS semester_name,
                l.name AS level_name,
                dt.name AS type_name,
                y.year,
                u.name AS user_name
         FROM documents d
         JOIN subjects s ON d.subject_id = s.id
         JOIN semesters sem ON s.semester_id = sem.id
         JOIN levels l ON sem.level_id = l.id
         JOIN document_types dt ON d.type_id = dt.id
         JOIN years y ON d.year_id = y.id
         LEFT JOIN users u ON d.user_id = u.id
         WHERE d.status = 'approved'
           AND LOWER(l.name) = LOWER(:level)
           AND LOWER(sem.name) = LOWER(:semester)
           AND LOWER(s.name) = LOWER(:subject)
         ORDER BY y.year DESC, dt.name"
    );
    \$stmt->execute([
        ':level'    => '$dbLevelName',
        ':semester' => '$dbSemesterName',
        ':subject'  => '$dbSubjectName'
    ]);
    \$records = \$stmt->fetchAll(PDO::FETCH_ASSOC);
    \$totalDocs = count(\$records);

    foreach (\$records as \$rec) {
        // PDF Link
        if (!empty(\$rec['pdf_url'])) {
            \$pdfLink = (strpos(\$rec['pdf_url'], 'http') === 0)
                ? \$rec['pdf_url']
                : '/' . ltrim(\$rec['pdf_url'], '/');
        } else {
            \$pdfLink = '#';
        }
        // HTML Viewer Link
        \$htmlLink = '#';
        if (!empty(\$rec['file_path']) || !empty(\$rec['pdf_url'])) {
            \$htmlLink = '/viewer/' . \$rec['id'];
        }
        \$documents[] = [
            'id'       => \$rec['id'],
            'title'    => \$rec['title'],
            'type'     => \$rec['type_name'],
            'year'     => \$rec['year'],
            'pdfLink'  => \$pdfLink,
            'htmlLink' => \$htmlLink,
            'hasHtml'  => (\$htmlLink !== '#'),
            'user'     => \$rec['user_name'] ?? 'Anonyme'
        ];
    }
} catch (Exception \$e) {
    error_log("Subject page DB error: " . \$e->getMessage());
}

// Group by year for the sidebar tabs
\$byYear = [];
foreach (\$documents as \$doc) {
    \$byYear[\$doc['year']][] = \$doc;
}
krsort(\$byYear);

\$years = array_keys(\$byYear);
\$firstYear = !empty(\$years) ? \$years[0] : date('Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars('$subjectName') ?> - Ressources IAI</title>
    <meta name="description" content="Ressources de la matiere $subjectName pour $levelName - $semesterName">
    <link rel="stylesheet" href="/css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="/js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
$navbar

    <main class="container" style="padding: 4rem 0;">

        <div class="subject-header">
            <h1 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars('$subjectName') ?></h1>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">$levelName - $semesterName</p>
            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <span style="background:var(--primary-light);color:#000;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.8rem;font-weight:600;">
                    <?= \$totalDocs ?> document<?= \$totalDocs > 1 ? 's' : '' ?> disponible<?= \$totalDocs > 1 ? 's' : '' ?>
                </span>
                <a href="/Rechercher" style="color:var(--text-muted);font-size:0.85rem;">&#8592; Rechercher d'autres ressources</a>
            </div>
        </div>

        <?php if (empty(\$documents)): ?>
        <div style="text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document disponible pour le moment</p>
            <p style="font-size:0.9rem;">Soyez le premier a contribuer pour cette matiere !</p>
            <a href="/Contribuer" class="btn btn-primary" style="margin-top:1.5rem;display:inline-block;">Contribuer</a>
        </div>
        <?php else: ?>

        <div class="subject-layout">
            <!-- Sidebar: Year Tabs -->
            <aside class="sidebar">
                <div class="sidebar-title">Annees Academiques</div>
                <div class="year-list">
                    <?php foreach (\$years as \$i => \$yr): ?>
                    <button class="year-btn <?= \$i === 0 ? 'active' : '' ?>"
                            data-year="<?= \$yr ?>"
                            onclick="showYear(<?= \$yr ?>)">
                        Annee <?= \$yr ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Content Panels -->
            <div class="subject-content year-panels">
                <?php foreach (\$byYear as \$yr => \$docs): ?>
                <?php \$isFirst = (\$yr === \$firstYear); ?>
                <div id="panel-<?= \$yr ?>" class="year-panel <?= \$isFirst ? 'active' : '' ?>">
                    <h2 style="color:var(--primary);margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:0.5rem;">
                        Ressources de l'annee <?= \$yr ?>
                    </h2>
                    <div class="resource-grid">
                        <?php foreach (\$docs as \$doc): ?>
                        <div class="resource-card">
                            <h3 style="margin-bottom:0.5rem;color:var(--text-main);font-size:0.95rem;"><?= htmlspecialchars(\$doc['title']) ?></h3>
                            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.35rem;text-transform:capitalize;">
                                <?= htmlspecialchars(str_replace('_', ' ', \$doc['type'])) ?>
                            </p>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">
                                Par <?= htmlspecialchars(\$doc['user']) ?>
                            </p>
                            <div style="display:flex;gap:0.5rem;flex-direction:column;">
                                <?php if (\$doc['hasHtml']): ?>
                                <a href="<?= htmlspecialchars(\$doc['htmlLink']) ?>"
                                   class="btn btn-primary"
                                   style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;">
                                    &#128065; Voir HTML
                                </a>
                                <?php endif; ?>
                                <?php if (\$doc['pdfLink'] !== '#'): ?>
                                <a href="<?= htmlspecialchars(\$doc['pdfLink']) ?>"
                                   class="btn btn-outline"
                                   style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;"
                                   target="_blank" rel="noopener">
                                    &#128196; Voir PDF
                                </a>
                                <?php endif; ?>
                                <?php if (!\$doc['hasHtml'] && \$doc['pdfLink'] === '#'): ?>
                                <span style="color:var(--text-muted);font-size:0.8rem;text-align:center;">Document indisponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        function showYear(year) {
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.year-panel').forEach(panel => panel.classList.remove('active'));
            const btn = document.querySelector('.year-btn[data-year="' + year + '"]');
            const panel = document.getElementById('panel-' + year);
            if (btn) btn.classList.add('active');
            if (panel) panel.classList.add('active');
        }
    </script>
    <script src="/js/main.js?v=2"></script>
</body>
</html>
<?php // End of subject page ?>
"@
}

# ─────────────────────────────────────────────────
# GENERATE PAGES
# ─────────────────────────────────────────────────

foreach ($levelKey in $structure.Keys) {
    # Determine directory path
    if ($levelKey -eq "L3-GLSI") { $dirKey = "L3/GLSI" }
    elseif ($levelKey -eq "L3-ASR") { $dirKey = "L3/ASR" }
    else { $dirKey = $levelKey }

    $dirPath    = Join-Path -Path $baseDir -ChildPath "pages\$dirKey"
    $levelName  = $structure[$levelKey].titre
    $dbLevelName = $structure[$levelKey].dbName

    New-Item -ItemType Directory -Force -Path $dirPath | Out-Null

    # ── 1. Level index page (lists semestres) ──
    $htmlContent = "<h1 style='color:var(--primary);margin-bottom:2rem;text-align:center;'>$levelName</h1><div class='grid grid-cols-2'>"
    foreach ($sem in $structure[$levelKey].semestres.Keys) {
        $semTitle = $structure[$levelKey].semestres[$sem].titre
        $htmlContent += @"
        <a href="$sem.php" class="level-card">
            <div class="level-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div>
                <div class="level-title">$semTitle</div>
                <div class="level-desc">Voir les matieres de ce semestre</div>
            </div>
        </a>
"@
    }
    $htmlContent += "</div>"
    $content  = Get-HtmlBoilerplate -title $levelName -contentHtml $htmlContent
    $filePath = Join-Path -Path $dirPath -ChildPath "index.php"
    Set-Content -Path $filePath -Value $content -Encoding UTF8
    Write-Host "  [OK] $filePath"

    # ── 2. Semester pages & Subject pages ──
    foreach ($sem in $structure[$levelKey].semestres.Keys) {
        $semTitle       = $structure[$levelKey].semestres[$sem].titre
        $dbSemesterName = $structure[$levelKey].semestres[$sem].dbName

        # Semester page
        $htmlContentSem  = "<h1 style='color:var(--primary);margin-bottom:2rem;'><a href='index.php' style='color:var(--text-muted);font-size:1.2rem;'>$levelName</a> &gt; $semTitle</h1><div class='grid grid-cols-3'>"
        foreach ($matiere in $structure[$levelKey].semestres[$sem].matieres) {
            $nomMatiere      = $structure[$levelKey].semestres[$sem].noms[$matiere]
            $dbSubjectName   = $structure[$levelKey].semestres[$sem].noms[$matiere]
            $htmlContentSem += @"
            <a href="/Matiere/$dirKey/$sem/$matiere" class="exam-card" style="text-align:center;border-top:4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top:1rem;margin-bottom:1rem;">$nomMatiere</h3>
                <span class="btn btn-outline" style="width:100%;">Voir les ressources</span>
            </a>
"@
        }
        $htmlContentSem += "</div>"
        $contentSem     = Get-HtmlBoilerplate -title "$levelName - $semTitle" -contentHtml $htmlContentSem
        $filePathSem    = Join-Path -Path $dirPath -ChildPath "$sem.php"
        Set-Content -Path $filePathSem -Value $contentSem -Encoding UTF8
        Write-Host "  [OK] $filePathSem"

        # Subject (Matiere) pages
        $subjDirPath = Join-Path -Path $baseDir -ChildPath "subjects\$dirKey\$sem"
        New-Item -ItemType Directory -Force -Path $subjDirPath | Out-Null

        foreach ($matiere in $structure[$levelKey].semestres[$sem].matieres) {
            $nomMatiere    = $structure[$levelKey].semestres[$sem].noms[$matiere]
            $dbSubjectName = $structure[$levelKey].semestres[$sem].noms[$matiere]

            $phpContent    = Get-SubjectPhpPage `
                                -subjectName    $nomMatiere `
                                -levelName      $levelName `
                                -semesterName   $semTitle `
                                -dbLevelName    $dbLevelName `
                                -dbSemesterName $dbSemesterName `
                                -dbSubjectName  $dbSubjectName

            $filePathSubj  = Join-Path -Path $subjDirPath -ChildPath "$matiere.php"
            Set-Content -Path $filePathSubj -Value $phpContent -Encoding UTF8
            Write-Host "  [OK] $filePathSubj"
        }
    }
}

Write-Host ""
Write-Host "===================================="
Write-Host " Structure v3 generee avec succes !"
Write-Host " Pages PHP dynamiques creees."
Write-Host "===================================="
