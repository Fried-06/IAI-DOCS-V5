<?php
session_start();
require_once __DIR__ . '/../../backend/beta_check.php';
?>
﻿
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licence 2 - Semestre 3 - Ressources IAI</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="/Accueil" class="logo">Ressources IAI</a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="/Accueil" class="nav-item">Accueil</a></li>
                    <li><a href="/Examens" class="nav-item">Examens</a></li>
                    <li><a href="/Rechercher" class="nav-item">Rechercher</a></li>
                    <li><a href="/Contribuer" class="nav-item">Contribuer</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container" style="padding: 4rem 0;">
        <h1 style='color: var(--primary); margin-bottom: 2rem;'><a href='index.php' style='color: var(--text-muted); font-size: 1.2rem;'>Licence 2</a> &gt; Semestre 3</h1><div class='grid grid-cols-3'>            <a href="semestre3/merise.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Merise</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/methodes-agiles.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">MÃ©thodes Agiles</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/uml.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">UML</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/implementation-bd.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">ImplÃ©mentation de BD</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/xml-web-services.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">XML & Web Services</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/probabilites-statistiques.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">ProbabilitÃ©s & Statistiques</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/recherche-operationnelle.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Recherche OpÃ©rationnelle</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/theorie-graphes.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">ThÃ©orie des Graphes</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/algebre-lineaire.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">AlgÃ¨bre LinÃ©aire</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/cryptographie.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Cryptographie</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/ccna2.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">CCNA 2</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/securite-informatique.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">SÃ©curitÃ© Informatique</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/programmation-web-2.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Programmation Web 2 (PHP)</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/python-dev.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">DÃ©veloppement Python</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre3/java-poo.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Java POO</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a></div>
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
