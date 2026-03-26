# restructure_corrige.ps1
# Restructures all corrige/ folders into corrige_devoir/, corrige_exercice/, corrige_partiel/
# SAFE: copies files, does NOT delete originals

$baseDir = "C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\Docs"

Write-Host "=== CORRIGE RESTRUCTURE SCRIPT ===" -ForegroundColor Cyan
Write-Host "Base directory: $baseDir" -ForegroundColor Gray
Write-Host ""

$subTypes = @("corrige_devoir", "corrige_exercice", "corrige_partiel")
$corrigeDirs = Get-ChildItem -Path $baseDir -Recurse -Directory -Filter "corrige"

if ($corrigeDirs.Count -eq 0) {
    Write-Host "No corrige/ folders found. Exiting." -ForegroundColor Yellow
    exit
}

Write-Host "Found $($corrigeDirs.Count) corrige/ folder(s):" -ForegroundColor Green

foreach ($dir in $corrigeDirs) {
    Write-Host ""
    Write-Host "  Processing: $($dir.FullName)" -ForegroundColor Yellow

    foreach ($subType in $subTypes) {
        $newSubDir = Join-Path $dir.FullName $subType
        
        # Create subfolder if not already existing
        if (-not (Test-Path $newSubDir)) {
            New-Item -ItemType Directory -Path $newSubDir -Force | Out-Null
            Write-Host "    [CREATED] $subType/" -ForegroundColor Green
        } else {
            Write-Host "    [EXISTS]  $subType/ (skipped creation)" -ForegroundColor Gray
        }

        # Copy .md files from corrige/ into each subfolder
        $mdFiles = Get-ChildItem -Path $dir.FullName -Filter "*.md" -File
        foreach ($mdFile in $mdFiles) {
            $destPath = Join-Path $newSubDir $mdFile.Name
            if (-not (Test-Path $destPath)) {
                Copy-Item -Path $mdFile.FullName -Destination $destPath
                Write-Host "      [COPIED] $($mdFile.Name) -> $subType/$($mdFile.Name)" -ForegroundColor Cyan
            } else {
                Write-Host "      [SKIP]   $($mdFile.Name) already in $subType/" -ForegroundColor Gray
            }
        }
    }
}

Write-Host ""
Write-Host "=== DONE ===" -ForegroundColor Cyan
Write-Host "The original corrige/ folders and their files have NOT been deleted." -ForegroundColor Green
Write-Host "Verify the new structure manually, then you can remove old corrige/ folders if needed." -ForegroundColor Green

# Print summary
Write-Host ""
Write-Host "=== STRUCTURE VERIFICATION ===" -ForegroundColor Cyan
foreach ($dir in $corrigeDirs) {
    Write-Host ""
    Write-Host "  $($dir.FullName)" -ForegroundColor White
    foreach ($subType in $subTypes) {
        $subDir = Join-Path $dir.FullName $subType
        if (Test-Path $subDir) {
            $count = (Get-ChildItem -Path $subDir -Filter "*.md" -File).Count
            Write-Host "    [OK] $subType/ ($count file(s))" -ForegroundColor Green
        } else {
            Write-Host "    [MISSING] $subType/" -ForegroundColor Red
        }
    }
}
