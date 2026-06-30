<?php
session_start();
require_once __DIR__ . '/backend/beta_check.php';
?>
<!DOCTYPE html>








<html lang="fr">








<head>








    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/IAI-DOCS-WHITE.png">








    <meta name="viewport" content="width=device-width, initial-scale=1.0">








    <title>Ressources IAI - Hub de Connaissances</title>








    <link rel="stylesheet" href="css/style.css?v=2">


    <link rel="stylesheet" href="css/footer-reveal.css?v=1">








    <!-- Google Fonts -->








    <link rel="preconnect" href="https://fonts.googleapis.com">








    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>








    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=DM+Sans:wght@400;500;700&family=Bebas+Neue&display=swap" rel="stylesheet">








<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">








    <script src="js/theme.js?v=2"></script>


</head>








<body class="page-fade-in">

















    <!-- Preloader -->








    <div id="preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out;">








        <video id="loading-video" src="assets/animated_logo2.mp4" muted playsinline style="width: 100vw; height: 100vh; object-fit: cover;"></video>








    </div>








    <script>








        if (sessionStorage.getItem('visited')) {








            document.getElementById('preloader').style.display = 'none';








        } else {








            const preloader = document.getElementById('preloader');








            const video = document.getElementById('loading-video');








            








            // Allow clicking to dismiss if auto-play is blocked








            preloader.addEventListener('click', dismissPreloader);

















            // Attempt to play








            video.play().catch(() => {








                // If blocked by browser policy, dismiss after a short delay








                setTimeout(dismissPreloader, 3000);








            });

















            video.addEventListener('ended', dismissPreloader);

















            function dismissPreloader() {








                preloader.style.opacity = '0';








                setTimeout(() => {








                    preloader.style.display = 'none';








                    sessionStorage.setItem('visited', 'true');








                }, 500);








            }








            








            // Fallback timeout just in case it hangs








            setTimeout(dismissPreloader, 8000);








        }








    </script>

















    <!-- Navbar -->








    <nav class="navbar">








        <div class="container nav-container">








            <a href="Accueil" class="logo">


                <img src="assets/IAI-NEW-LOGO.png" alt="Logo IAI">


            </a>








            <div class="nav-links">








                <ul class="nav-menu">








                    <li><a href="Accueil" class="nav-item">Accueil</a></li>








                    <li><a href="Examens" class="nav-item">Examens</a></li>








                    <li><a href="Rechercher" class="nav-item">Rechercher</a></li>








                    <li><a href="contributors.php" class="nav-item">Contributeurs</a></li>








                    <li><a href="Contribuer" class="nav-item">Contribuer</a></li>








                </ul>








                <div class="nav-actions">


                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème"></button>


                    <a href="Connexion" class="auth-login-btn">Connexion</a>


                    <a href="Connexion" class="auth-register-btn">S'inscrire</a>


                    <a href="Profil" class="btn btn-outline" id="btn-profil" style="display: none;">Profil</a>


                </div>








            </div>








            <button class="mobile-menu-btn">








                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>








            </button>








        </div>








    </nav>

















    <main>








        <!-- • • •  HERO SECTION — Modern AI Startup • • •  -->





        <section class="hero-v12" id="hero">





            <!-- Animated grid background -->


            <div class="hero-grid"></div>





            <!-- Radial glow orbs -->


            <div class="hero-glow hero-glow-1"></div>


            <div class="hero-glow hero-glow-2"></div>


            <div class="hero-glow hero-glow-3"></div>





            <!-- Mouse spotlight -->


            <div class="hero-spotlight" id="hero-spotlight"></div>
            <!-- JetBrains Breathing & Color-cycling Glow Orb -->
            <div class="jetbrains-glow-orb"></div>





            <div class="container hero-v12-content">





                <span class="hero-badge anim-fade-up" style="animation-delay:0.1s">


                    <span class="hero-badge-dot"></span>


                    Plateforme Académique Open Source


                </span>





                <h1 class="hero-title anim-fade-up" style="animation-delay:0.25s">


                    Le Wikipédia de l'<br><span class="gradient-text">IAI-TOGO</span>


                </h1>





                <p class="hero-subtitle anim-fade-up" style="animation-delay:0.45s">


                    Accédez aux anciens examens et devoirs des années académiques précédentes de manière centralisée.


                </p>





                <div class="hero-cta anim-fade-up" style="animation-delay:0.6s">





                    <a href="L1" class="btn-glass btn-glass-primary hero-float">


                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>


                        Parcourir les Niveaux


                    </a>





                    <a href="Rechercher" class="btn-glass btn-glass-outline hero-float" style="animation-delay:0.15s">


                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>


                        Explorer les Examens


                    </a>





                </div>





            </div>





        </section>








        <style>


            /* Premium Micro-animations for Cards */


            .level-card, .exam-card { position: relative; overflow: hidden; }


            .level-card::before, .exam-card::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); transform: skewX(-20deg); transition: 0.5s; z-index: 1; }


            .level-card:hover::before, .exam-card:hover::before { left: 150%; }


            .level-card:hover .level-icon { transform: scale(1.1) rotate(5deg); }


        </style>

















        <!-- ââ¢ââ¢ââ¢ STATISTICS ROW ââ¢ââ¢ââ¢ -->








        <section class="stats-section scroll-reveal">








            <div class="container">








                <div class="stats-grid">








                    <div class="stat-item">








                        <span class="stat-number" data-target="500">0</span><span class="stat-suffix">+</span>








                        <span class="stat-label">Examens</span>








                    </div>








                    <div class="stat-item">








                        <span class="stat-number" data-target="300">0</span><span class="stat-suffix">+</span>








                        <span class="stat-label">Devoirs</span>








                    </div>








                    <div class="stat-item">








                        <span class="stat-number" data-target="700">0</span><span class="stat-suffix">+</span>








                        <span class="stat-label">Étudiants</span>








                    </div>








                    <div class="stat-item">








                        <span class="stat-number" data-target="75">0</span><span class="stat-suffix">+</span>








                        <span class="stat-label">Matières</span>








                    </div>








                </div>








            </div>








        </section>

















        <!-- Academic Levels Section -->








        <section class="section scroll-reveal">








            <div class="container">








                <h2 class="section-title">Niveaux Académiques</h2>








                








                <div class="grid grid-cols-4">








                    <!-- L1 -->








                    <a href="L1" class="level-card level-l1">








                        <div class="level-card-img"><img src="assets/l1_cover.png" alt="L1"></div>








                        <div class="level-icon">








                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">








                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>








                                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>








                            </svg>








                        </div>








                        <div>








                            <div class="level-title">Licence 1</div>








                            <div class="level-desc">Algorithmique, C, Maths Discrètes...</div>








                        </div>








                    </a>

















                    <!-- L2 -->








                    <a href="L2" class="level-card level-l2">








                        <div class="level-card-img"><img src="assets/l2_cover.png" alt="L2"></div>








                        <div class="level-icon">








                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">








                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>








                                <line x1="16" y1="2" x2="16" y2="6"></line>








                                <line x1="8" y1="2" x2="8" y2="6"></line>








                                <line x1="3" y1="10" x2="21" y2="10"></line>








                            </svg>








                        </div>








                        <div>








                            <div class="level-title">Licence 2</div>








                            <div class="level-desc">Merise, Prog Web 2, Réseaux...</div>








                        </div>








                    </a>

















                    <!-- L3 GLSI -->








                    <a href="L3/GLSI" class="level-card level-glsi">








                        <div class="level-card-img"><img src="assets/glsi_cover.png" alt="GLSI"></div>








                        <div class="level-icon">








                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">








                                <polyline points="16 18 22 12 16 6"></polyline>








                                <polyline points="8 6 2 12 8 18"></polyline>








                            </svg>








                        </div>








                        <div>








                            <div class="level-title">Licence 3 GLSI</div>








                            <div class="level-desc">Génie Logiciel, Big Data, JEE...</div>








                        </div>








                    </a>

















                    <!-- L3 ASR -->








                    <a href="L3/ASR" class="level-card level-asr">








                        <div class="level-card-img"><img src="assets/asr_cover.png" alt="ASR"></div>








                        <div class="level-icon">








                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">








                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>








                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>








                                <line x1="6" y1="6" x2="6.01" y2="6"></line>








                                <line x1="6" y1="18" x2="6.01" y2="18"></line>








                            </svg>








                        </div>








                        <div>








                            <div class="level-title">Licence 3 ASR</div>








                            <div class="level-desc">Système Avancé, Sécurité, CCNA...</div>








                        </div>








                    </a>








                </div>








            </div>








        </section>

















        <!-- IAI Study Studio Promo Section -->


        <section class="section studio-promo scroll-reveal" style="background: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.05), transparent 70%); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 8rem 0;">


            <div class="container" style="display: flex; align-items: center; gap: 4rem; flex-wrap: wrap;">


                <div class="studio-promo-text" style="flex: 1; min-width: 300px;">


                    <span class="badge" style="background: var(--primary); color: white; padding: 0.4rem 1rem; border-radius: 2rem; font-size: 0.8rem; font-weight: 700; margin-bottom: 1.5rem; display: inline-block;">NOUVEAU : MENTOR IA</span>


                    <h2 style="font-size: 3rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; color: #fff;">


                        Révisez intelligemment avec <br><span class="gradient-text">IAI Study Studio</span>


                    </h2>


                    <p style="font-size: 1.1rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 2.5rem;">


                        Transformez vos cours en quiz et cartes mentales. Téléversez vos documents et laissez notre IA multimodale vous guider.


                    </p>


                    <div style="display: flex; gap: 1rem;">


                        <a href="studio/index.php" class="btn btn-primary" style="padding: 1rem 2rem; font-weight: 700;">


                            Lancer le Studio


                        </a>


                    </div>


                </div>


                <div class="studio-promo-visual" style="flex: 1; min-width: 300px; position: relative;">


                    <div style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">


                        <img src="assets/studio_preview.png" alt="Studio Preview" style="width: 100%; border-radius: 8px;" onerror="this.src='https://placehold.co/600x400/0f172a/a855f7?text=IAI+Study+Studio'">


                    </div>


                </div>


            </div>


        </section>





        <!-- Recent Exams Section -->








        <section class="section bg-light scroll-reveal">








            <div class="container">








                <h2 class="section-title">Examens Récents</h2>








                








                <div id="recentExamsGrid" class="grid grid-cols-3">








                    <!-- Exam 1 -->








                    <div class="exam-card">








                        <div class="exam-header">








                            <span class="tag">L1 - S1</span>








                            <div class="exam-meta">








                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>








                                2023








                            </div>








                        </div>








                        <h3 class="exam-title">Examen Final : Algorithmique</h3>








                        <a href="Docs/_build/html/index.html" class="btn btn-outline" style="margin-top: auto;">Voir l'Examen</a>








                    </div>

















                    <!-- Exam 2 -->








                    <div class="exam-card">








                        <div class="exam-header">








                            <span class="tag">L2 - S3</span>








                            <div class="exam-meta">








                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>








                                2022








                            </div>








                        </div>








                        <h3 class="exam-title">Projet : Base de Données Implémentation</h3>








                        <a href="Docs/_build/html/index.html" class="btn btn-outline" style="margin-top: auto;">Voir l'Examen</a>








                    </div>

















                    <!-- Exam 3 -->








                    <div class="exam-card">








                        <div class="exam-header">








                            <span class="tag">L3 GLSI</span>








                            <div class="exam-meta">








                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>








                                2024








                            </div>








                        </div>








                        <h3 class="exam-title">Devoir Surveillé : Programmation JEE</h3>








                        <a href="Docs/_build/html/index.html" class="btn btn-outline" style="margin-top: auto;">Voir l'Examen</a>








                    </div>








                </div>








            </div>








        </section>

















        <!-- ââ¢ââ¢ââ¢ Vision Timeline Section ââ¢ââ¢ââ¢ -->








        <section class="section timeline-section" id="vision">








            <div class="container">








                <h2 class="section-title" style="font-family:'Bebas Neue',sans-serif; font-size: clamp(2.5rem,5vw,4rem); letter-spacing:0.04em;">Notre Vision</h2>








                <p style="text-align:center; color:var(--muted); font-family:'JetBrains Mono',monospace; font-size:0.8rem; letter-spacing:0.08em; margin-bottom:3rem;">DE L'IDÉE À LA RÉALITÉ</p>

















                <div class="timeline">








                    <!-- The animated vertical line -->








                    <div class="timeline-line">








                        <div class="timeline-line-fill" id="timeline-fill"></div>








                    </div>

















                    <!-- Step 1 ââ¬â Left -->








                    <div class="timeline-item tl-left tl-reveal">








                        <div class="timeline-node"></div>








                        <div class="timeline-card">








                            <div class="timeline-img-wrap">








                                <img src="images/image1.png" alt="Problème" loading="lazy">








                            </div>








                            <span class="timeline-step">ÉTAPE 01</span>








                            <h3>Des ressources dispersées</h3>








                            <p>Les cours, TD et examens sont souvent éparpillés entre différents groupes et plateformes, ce qui rend la révision difficile.</p>








                        </div>








                    </div>

















                    <!-- Step 2 ââ¬â Right -->








                    <div class="timeline-item tl-right tl-reveal">








                        <div class="timeline-node"></div>








                        <div class="timeline-card">








                            <div class="timeline-img-wrap">








                                <img src="images/image2.png" alt="Centralisation" loading="lazy">








                            </div>








                            <span class="timeline-step">ÉTAPE 02</span>








                            <h3>Centraliser les ressources</h3>








                            <p>IAI Docs rassemble cours, TD, examens et explications pédagogiques en un seul endroit pour faciliter la révision.</p>








                        </div>








                    </div>

















                    <!-- Step 3 ââ¬â Left -->








                    <div class="timeline-item tl-left tl-reveal">








                        <div class="timeline-node"></div>








                        <div class="timeline-card">








                            <div class="timeline-img-wrap">








                                <img src="images/image3.png" alt="Communauté" loading="lazy">








                            </div>








                            <span class="timeline-step">ÉTAPE 03</span>








                            <h3>Une communauté étudiante</h3>








                            <p>Les étudiants contribuent ensemble en partageant leurs notes, leurs corrections et leurs explications.</p>








                        </div>








                    </div>

















                    <!-- Step 4 ââ¬â Right -->








                    <div class="timeline-item tl-right tl-reveal">








                        <div class="timeline-node"></div>








                        <div class="timeline-card">








                            <div class="timeline-img-wrap">








                                <img src="images/image4.png" alt="Intelligence" loading="lazy">








                            </div>








                            <span class="timeline-step">ÉTAPE 04</span>








                            <h3>Un hub d'apprentissage intelligent</h3>








                            <p>Grâce à l'intelligence artificielle, les étudiants pourront obtenir des explications, des exemples et une aide personnalisée directement à partir de leurs cours.</p>








                        </div>








                    </div>

















                    <!-- Step 5 ââ¬â Left -->








                    <div class="timeline-item tl-left tl-reveal">








                        <div class="timeline-node"></div>








                        <div class="timeline-card">








                            <div class="timeline-img-wrap">








                                <img src="images/image5.png" alt="Expansion" loading="lazy">








                            </div>








                            <span class="timeline-step">ÉTAPE 05</span>








                            <h3>Une plateforme pour tous les IAI</h3>








                            <p>L'objectif est d'étendre la plateforme pour regrouper les ressources de toutes les représentations IAI.</p>








                        </div>








                    </div>








                </div>








            </div>








        </section>

















    </main>


























        <!-- ââ¢ââ¢ââ¢ WHY USE IAI DOCS ââ¢ââ¢ââ¢ -->








        <section class="section why-section scroll-reveal" id="why">








            <div class="container">








                <span class="section-badge">Pourquoi nous choisir</span>








                <h2 class="section-title-v12">Pourquoi utiliser <span class="gradient-text">IAI Docs</span> ?</h2>








                <div class="why-grid">








                    <div class="why-card scroll-reveal">








                        <div class="why-icon" style="--accent:var(--cyan)">








                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>








                        </div>








                        <h3>Centralisé</h3>








                        <p>Tous les cours, TD, examens et corrections rassemblés en un seul endroit accessible à tout moment.</p>








                    </div>








                    <div class="why-card scroll-reveal">








                        <div class="why-icon" style="--accent:var(--purple)">








                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>








                        </div>








                        <h3>Communautaire</h3>








                        <p>Construit par les étudiants, pour les étudiants. Chacun contribue avec ses notes et corrections.</p>








                    </div>








                    <div class="why-card scroll-reveal">








                        <div class="why-icon" style="--accent:var(--green)">








                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>








                        </div>








                        <h3>Structuré</h3>








                        <p>Organisation par niveau, semestre et matière. Navigation intuitive pour trouver rapidement ce dont vous avez besoin.</p>








                    </div>








                    <div class="why-card scroll-reveal">








                        <div class="why-icon" style="--accent:var(--amber)">








                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>








                        </div>








                        <h3>évolutif</h3>








                        <p>Nouvelles ressources ajoutées chaque année. La plateforme grandit avec chaque nouvelle promotion.</p>








                    </div>








                </div>








            </div>








        </section>

















        <!-- ââ¢ââ¢ââ¢ CONTRIBUTE CTA ââ¢ââ¢ââ¢ -->








        <section class="section cta-section scroll-reveal" id="contribute-cta">








            <div class="container">








                <div class="cta-box">








                    <div class="cta-glow"></div>








                    <span class="section-badge" style="margin-bottom:1rem">Open Source</span>








                    <h2 class="section-title-v12" style="margin-bottom:1rem">Rejoignez la <span class="gradient-text">communauté</span></h2>








                    <p style="color:var(--muted);max-width:500px;margin:0 auto 2rem;font-size:1.05rem;line-height:1.7">








                        Partagez vos cours, corrections et examens pour aider les promotions futures. Chaque contribution compte.








                    </p>








                    <a href="Contribuer" class="btn-glass btn-glass-primary" style="display:inline-flex">








                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>








                        Contribuer maintenant








                    </a>








                </div>








            </div>








        </section>





    </main>





    <!--  PREMIUM TWO-STAGE REVEAL FOOTER  -->


    <div class="footer-reveal-wrapper">





        <!-- SECTION A — Standard Footer (sticky, covers Section B) -->


        <footer class="footer footer-standard" id="footer-standard">


            <div class="container">


                <div class="footer-grid footer-grid-upgraded">





                    <!-- Col 1: Brand + About -->


                    <div class="footer-col-upgraded footer-brand-col">


                        <div class="footer-logo-row">


                            <img src="assets/IAI-NEW-LOGO.png" alt="IAI-DOCS Logo">


                            <span class="footer-logo-text">IAI DOCS</span>


                        </div>


                        <p class="footer-about-text">L'Institut Africain d'Informatique — Une plateforme open source dédiée au partage de ressources académiques pour la réussite de tous les étudiants.</p>


                    </div>





                    <!-- Col 2: Navigation -->


                    <div class="footer-col-upgraded">


                        <h4>Navigation</h4>


                        <ul class="footer-link-list">


                            <li><a href="Accueil">Accueil</a></li>


                            <li><a href="Rechercher">Recherche</a></li>


                            <li><a href="Examens">Examens</a></li>


                            <li><a href="contributors.php">Contributeurs</a></li>


                            <li><a href="Connexion">Connexion</a></li>


                            <li><a href="Connexion">Inscription</a></li>


                        </ul>


                    </div>





                    <!-- Col 3: Academic Levels -->


                    <div class="footer-col-upgraded">


                        <h4>Niveaux</h4>


                        <ul class="footer-link-list">


                            <li><a href="L1">Licence 1</a></li>


                            <li><a href="L2">Licence 2</a></li>


                            <li><a href="L3/GLSI">L3 GLSI</a></li>


                            <li><a href="L3/ASR">L3 ASR</a></li>


                        </ul>


                    </div>





                    <!-- Col 4: Developers -->


                    <div class="footer-col-upgraded">


                        <h4>Équipe</h4>


                        <div class="footer-devs-list">


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">AH</div>


                                <span class="footer-dev-name">AHIAGBA Kokou</span>


                            </div>


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">DK</div>


                                <span class="footer-dev-name">DOSSOU K. Krist</span>


                            </div>


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">AB</div>


                                <span class="footer-dev-name">ADZEVI Boris</span>


                            </div>


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">NR</div>


                                <span class="footer-dev-name">NAKPASSE Ruth</span>


                            </div>


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">NF</div>


                                <span class="footer-dev-name">NOUGNANKÉY Faure</span>


                            </div>


                            <div class="footer-dev-item">


                                <div class="footer-dev-avatar">SW</div>


                                <span class="footer-dev-name">SAMBO Wilfried</span>


                            </div>


                        </div>


                    </div>





                    <!-- Col 5: Contact & Links -->


                    <div class="footer-col-upgraded footer-contact-col">


                        <h4>Contact</h4>


                        <a href="contact.html" class="footer-contact-link">


                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>


                            Suggestion / Contact


                        </a>


                        <a href="about.html" class="footer-contact-link">


                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>


                            À propos


                        </a>


                        <a href="Contribuer" class="footer-contact-link">


                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>


                            Contribuer


                        </a>


                    </div>





                </div>





                <!-- Footer Bottom Bar -->


                <div class="footer-bottom-upgraded">


                    <p>&copy; 2026 IAI DOCS. Tous droits réservés.</p>





                    <div class="footer-social-row">


                        <a href="#" class="footer-social-link" title="GitHub" aria-label="GitHub">


                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>


                        </a>


                    </div>





                    <button class="back-to-top-btn" id="back-to-top">


                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>


                        Haut de page


                    </button>


                </div>


            </div>


        </footer>





        <!-- SECTION B — Large Branded Reveal Panel (revealed when user scrolls past footer) -->


        <section class="footer-brand-reveal" id="footer-brand-reveal">


            <div class="footer-brand-reveal__inner">


                <div class="footer-brand-reveal__glow"></div>


                <div class="footer-brand-wordmark" id="footer-wordmark">


                    <span class="wordmark-letter" data-letter="I">I</span>


                    <span class="wordmark-letter" data-letter="A">A</span>


                    <span class="wordmark-letter" data-letter="I">I</span>


                    <span class="wordmark-spacer"></span>


                    <span class="wordmark-letter" data-letter="D">D</span>


                    <span class="wordmark-letter" data-letter="O">O</span>


                    <span class="wordmark-letter" data-letter="C">C</span>


                    <span class="wordmark-letter" data-letter="S">S</span>


                </div>


                <p class="footer-brand-tagline">Knowledge for every IAI student, across Africa.</p>


            </div>


        </section>





    </div><!-- /.footer-reveal-wrapper -->














    <script>


        // Gestion dynamique de la navbar au scroll


        window.addEventListener('scroll', () => {


            const nav = document.querySelector('.navbar');


            if (window.scrollY > 50) {


                nav.classList.add('scrolled');


            } else {


                nav.classList.remove('scrolled');


            }


        });


    </script>


    <script src="js/main.js?v=3"></script>


    <script src="js/footer-reveal.js?v=1"></script>









<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('recentExamsGrid');
    if (!grid) return;
    
    try {
        const recent = JSON.parse(localStorage.getItem('recentExams') || '[]');
        if (recent.length > 0) {
            grid.innerHTML = ''; // Clear static cards
            recent.forEach((exam, i) => {
                const tagStr = exam.tag ? `<span class="tag">${exam.tag}</span>` : '';
                grid.innerHTML += `
                    <div class="exam-card" style="animation-delay: ${i*0.1}s">
                        <div class="exam-header">
                            ${tagStr}
                            <div class="exam-meta">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Récemment ouvert
                            </div>
                        </div>
                        <h3 class="exam-title">${exam.title}</h3>
                        <a href="${exam.link}" class="btn btn-outline" style="margin-top: auto;">Reprendre la lecture</a>
                    </div>
                `;
            });
        }
    } catch(e) { console.error(e); }
});
</script>

</body>








</html>








