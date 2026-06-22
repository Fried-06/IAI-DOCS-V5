<?php
session_start();
require_once __DIR__ . '/../../backend/beta_check.php';
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/pages/'));
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licence 1 - Ressources IAI</title>
    <base href="<?= $base_url ?>/">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
    <nav class="navbar">
        <div class="container nav-container">
            <a href="Accueil" class="logo" style="padding:0;display:flex;align-items:center;">
                <img src="assets/iai_docs_moderne.png" alt="Logo IAI" style="height:200px;width:auto;object-fit:contain;">
            </a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="Accueil" class="nav-item">Accueil</a></li>
                    <li><a href="Examens" class="nav-item">Examens</a></li>
                    <li><a href="Rechercher" class="nav-item">Rechercher</a></li>
                    <li><a href="Contribuer" class="nav-item">Contribuer</a></li>
                </ul>
                <div class="nav-actions">
                    <button class="theme-toggle" id="theme-toggle" title="Basculer le theme">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    <a href="Connexion" class="btn btn-outline auth-login-btn" style="padding:0.5rem 1rem;border:none;">Connexion</a>
                    <a href="Connexion" class="btn btn-primary auth-register-btn" style="padding:0.5rem 1rem;">S'inscrire</a>
                    <a href="Profil" class="btn btn-outline" id="btn-profil" style="display:none;">Profil</a>
                </div>
            </div>
            <button class="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>
    <main class="container" style="padding: 4rem 0;">
        <h1 style="color:var(--primary);margin-bottom:2rem;text-align:center;">Licence 1</h1>
        <div class="grid grid-cols-2">

        <a href="L1/semestre1" class="level-card">
            <div class="level-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div>
                <div class="level-title">Semestre 1</div>
                <div class="level-desc">Voir les matieres de ce semestre</div>
            </div>
        </a>
        <a href="L1/semestre2" class="level-card">
            <div class="level-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div>
                <div class="level-title">Semestre 2</div>
                <div class="level-desc">Voir les matieres de ce semestre</div>
            </div>
        </a>
        </div>
    </main>
    <script src="js/main.js?v=4"></script>
</body>
</html>
