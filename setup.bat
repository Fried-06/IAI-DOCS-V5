@echo off
REM setup.bat - Dependency Installation Script for Windows
echo =========================================
echo IAI DOCS - Installation des dependances
echo =========================================

REM Check PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERREUR] PHP n'est pas installe ou pas dans le PATH.
    pause
    exit /b 1
)

REM Check Composer
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [AVERTISSEMENT] Composer non detecte. Saut de l'etape PHP.
) else (
    echo [1/2] Installation des dependances PHP...
    composer install --no-interaction --prefer-dist
)

REM Check Python
where python >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERREUR] Python n'est pas installe ou pas dans le PATH.
    pause
    exit /b 1
)

echo [2/2] Installation des dependances Python...
pip install -r requirements.txt

echo =========================================
echo Installation terminee avec succes !
echo =========================================
pause
