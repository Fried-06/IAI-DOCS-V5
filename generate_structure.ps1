$baseDir = "C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"

# Define the structure
$structure = @{
    "pages" = @{
        "L1" = @("semestre1.html", "semestre2.html")
        "L2" = @("semestre3.html", "semestre4.html")
        "L3" = @("glsi.html", "asr.html")
    }
    "subjects" = @{
        "L1\semestre1" = @("algorithmique.html", "langage-c.html", "architecture-maintenance.html", "electronique-numerique.html", "mathematiques-discretes.html", "analyse-mathematiques.html", "suite-ic3.html", "linux.html", "anglais-informatique.html", "expression-ecrite-orale.html")
        "L1\semestre2" = @("programmation-web.html", "programmation-objet.html", "conception-sd.html", "ccna1a.html", "ccna1b.html", "bases-de-donnees.html", "pratique-sql.html", "environnement-economique.html", "comptabilite.html", "projet-professionnel.html")
        "L2\semestre3" = @("merise.html", "methodes-agiles.html", "uml.html", "implementation-bd.html", "xml-web-services.html", "probabilites-statistiques.html", "recherche-operationnelle.html", "theorie-graphes.html", "algebre-lineaire.html", "cryptographie.html", "ccna2.html", "securite-informatique.html", "programmation-web-2.html", "python-dev.html", "java-poo.html")
        "L2\semestre4" = @("anglais-scientifique.html", "techniques-communication.html", "redaction-scientifique.html", "maintenance-informatique.html", "electronique-appliquee.html", "programmation-mobile.html", "csharp.html", "cloud-computing.html", "tic-management.html", "droit-tic.html")
        "L3\GLSI" = @("sig.html", "big-data.html", "aide-decision.html", "programmation-jee.html", "programmation-distribuee.html", "administration-oracle.html", "securite-bd.html", "administration-sqlserver.html", "introduction-ia.html", "analyse-donnees.html", "genie-logiciel.html", "anglais-expert.html", "developpement-personnel.html")
        "L3\ASR" = @("programmation-systeme.html", "systeme-linux-avance.html", "ccna3.html", "huawei-network.html", "administration-reseaux.html", "administration-systemes.html", "big-data.html", "securite-reseaux.html", "anglais-expert.html", "toeic.html", "developpement-personnel.html")
    }
}

function Get-HtmlBoilerplate($title, $depth) {
    $navPath = "../" * $depth
    if ($depth -eq 0) { $navPath = "" }
    
    return @"
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Ressources IAI</title>
    <link rel="stylesheet" href="$($navPath)css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="$($navPath)index.html" class="logo">Ressources IAI</a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="$($navPath)index.html" class="nav-item">Accueil</a></li>
                    <li><a href="$($navPath)search.html" class="nav-item">Examens</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container" style="padding: 4rem 0;">
        <h1 style="color: var(--primary); margin-bottom: 2rem; text-transform: capitalize;">$($title.Replace('-', ' '))</h1>
        <div class="grid grid-cols-3">
            <div class="exam-card">
                <div class="exam-header">
                    <span class="tag">Examen</span>
                </div>
                <h3 class="exam-title">Sujet Principal 2023</h3>
                <a href="#" class="btn btn-outline" style="margin-top: auto;">Télécharger PDF</a>
            </div>
            <div class="exam-card">
                <div class="exam-header">
                    <span class="tag">Cours</span>
                </div>
                <h3 class="exam-title">Fascicule Complet</h3>
                <a href="#" class="btn btn-outline" style="margin-top: auto;">Télécharger PDF</a>
            </div>
        </div>
    </main>
</body>
</html>
"@
}

# Create pages
foreach ($level in $structure["pages"].Keys) {
    $dirPath = Join-Path -Path $baseDir -ChildPath "pages\$level"
    New-Item -ItemType Directory -Force -Path $dirPath | Out-Null
    
    foreach ($file in $structure["pages"][$level]) {
        $filePath = Join-Path -Path $dirPath -ChildPath $file
        $title = $file.Replace('.html', '')
        $content = Get-HtmlBoilerplate $title 2
        Set-Content -Path $filePath -Value $content -Encoding UTF8
    }
}

# Create subjects
foreach ($subPath in $structure["subjects"].Keys) {
    $dirPath = Join-Path -Path $baseDir -ChildPath "subjects\$subPath"
    New-Item -ItemType Directory -Force -Path $dirPath | Out-Null
    
    foreach ($file in $structure["subjects"][$subPath]) {
        $filePath = Join-Path -Path $dirPath -ChildPath $file
        $title = $file.Replace('.html', '')
        $content = Get-HtmlBoilerplate $title 3
        Set-Content -Path $filePath -Value $content -Encoding UTF8
    }
}

Write-Host "Structure created successfully."
