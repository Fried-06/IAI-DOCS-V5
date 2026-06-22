<?php
session_start();
require_once __DIR__ . '/../../../backend/beta_check.php';
?>
﻿
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licence 3 GLSI - Semestre 5 - Ressources IAI</title>
    <link rel="stylesheet" href="../../../css/style.css">
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
        <h1 style='color: var(--primary); margin-bottom: 2rem;'><a href='index.php' style='color: var(--text-muted); font-size: 1.2rem;'>Licence 3 GLSI</a> &gt; Semestre 5</h1><div class='grid grid-cols-3'>            <a href="semestre5/sig.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">SystÃ¨me d'Information GÃ©ographique</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/big-data.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Big Data (NoSQL)</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/aide-decision.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">SystÃ¨me d'Information d'Aide Ã  la DÃ©cision</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/programmation-jee.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Programmation JEE (Spring Boot, JSP)</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/programmation-distribuee.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Programmation DistribuÃ©e (Python/Java)</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/administration-oracle.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Administration des BD Oracle</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/securite-bd.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">SÃ©curitÃ© des Bases de DonnÃ©es</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/administration-sqlserver.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Administration des BD SQL-Server</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/introduction-ia.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Intro Ã  l'Intelligence Artificielle</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/analyse-donnees.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Analyse de DonnÃ©es</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/genie-logiciel.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Introduction au GÃ©nie Logiciel</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/anglais-expert.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">Anglais Expert</h3>
                <span class="btn btn-outline" style="width: 100%;">Voir les ressources</span>
            </a>            <a href="semestre5/developpement-personnel.php" class="exam-card" style="text-align: center; border-top: 4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top: 1rem; margin-bottom: 1rem;">DÃ©veloppement Personnel</h3>
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
