#!/bin/bash
# setup.sh - Dependency Installation Script for macOS/Linux

echo "========================================="
echo "IAI DOCS - Installation des dépendances"
echo "========================================="

# Check PHP & Composer
if ! command -v php &> /dev/null; then
    echo "❌ Erreur: PHP n'est pas installé."
    exit 1
fi
if ! command -v composer &> /dev/null; then
    echo "❌ Erreur: Composer n'est pas installé."
    exit 1
fi

echo "🔹 Installation des dépendances PHP..."
composer install --no-interaction --prefer-dist

# Check Python & Pip
if ! command -v python3 &> /dev/null; then
    if ! command -v python &> /dev/null; then
        echo "❌ Erreur: Python n'est pas installé."
        exit 1
    else
        PYTHON_CMD="python"
    fi
else
    PYTHON_CMD="python3"
fi

if ! command -v pip &> /dev/null && ! command -v pip3 &> /dev/null; then
    echo "❌ Erreur: pip n'est pas installé."
    exit 1
fi
PIP_CMD="pip"
if command -v pip3 &> /dev/null; then
    PIP_CMD="pip3"
fi

echo "🔹 Installation des dépendances Python..."
$PIP_CMD install -r requirements.txt

echo "========================================="
echo "✅ Installation terminée avec succès !"
echo "========================================="
