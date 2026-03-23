<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recherche - Ressources IAI</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    <style>

        .search-hero {

            padding: 4rem 0 3rem;

            background: linear-gradient(135deg, #040c18 0%, #0b1930 50%, #0a1628 100%);

            text-align: center; position: relative; overflow: hidden;

        }

        .search-hero::before {

            content:''; position:absolute; inset:0;

            background-image:

                linear-gradient(rgba(168,85,247,.03) 1px,transparent 1px),

                linear-gradient(90deg,rgba(168,85,247,.03) 1px,transparent 1px);

            background-size:50px 50px; pointer-events:none;

        }

        .search-hero h1 {

            font-family: 'Bebas Neue', sans-serif;

            font-size: clamp(2rem,5vw,3.5rem); color: #fff;

            letter-spacing: 0.04em; position: relative; z-index: 1;

        }

        .search-bar-wrap {

            max-width: 700px; margin: 1.5rem auto 0; position: relative; z-index: 1;

        }

        .search-input-v2 {

            width: 100%; padding: 1rem 1.5rem 1rem 3.5rem;

            background: #07111f; border: 1px solid #1e3558;

            border-radius: 12px; color: #c8ddf2;

            font-family: 'JetBrains Mono', monospace; font-size: 0.95rem;

            outline: none; transition: all 0.3s;

        }

        .search-input-v2:focus {

            border-color: #a855f7;

            box-shadow: 0 0 20px rgba(168,85,247,0.2);

        }

        .search-input-v2::placeholder { color: #4a6a8a; }

        .search-icon-v2 {

            position: absolute; left: 1.2rem; top: 50%;

            transform: translateY(-50%); color: #4a6a8a;

            width: 20px; height: 20px; pointer-events: none;

        }

        .filters-row {

            display: flex; gap: 0.75rem; flex-wrap: wrap;

            justify-content: center; margin-top: 1.25rem;

            position: relative; z-index: 1;

        }

        .filter-select {

            padding: 0.5rem 1rem; border-radius: 8px;

            background: rgba(255,255,255,0.05); color: #c8ddf2;

            border: 1px solid #1e3558; font-size: 0.85rem;

            font-family: 'JetBrains Mono', monospace;

            cursor: pointer; transition: all 0.3s;

        }

        .filter-select:focus { border-color: #a855f7; outline: none; }

        .filter-select option { background: #07111f; color: #c8ddf2; }

        .search-btn {

            padding: 0.5rem 1.5rem; border-radius: 8px;

            background: linear-gradient(135deg, #a855f7, #7c3aed);

            color: #fff; border: none; cursor: pointer;

            font-family: 'JetBrains Mono', monospace; font-weight: 600;

            font-size: 0.85rem; transition: all 0.3s;

        }

        .search-btn:hover {

            box-shadow: 0 0 20px rgba(168,85,247,0.4);

            transform: translateY(-2px);

        }



        /* Results */

        .results-info {

            padding: 1rem 0; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;

            border-bottom: 1px solid #152540; margin-bottom: 1.5rem;

        }

        .results-info strong { color: #a855f7; }

        .result-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));

            gap: 1rem;

        }

        .result-card {

            background: #07111f; border: 1px solid #152540;

            border-radius: 10px; padding: 1.25rem;

            transition: all 0.3s; text-decoration: none;

            display: flex; flex-direction: column;

        }

        .result-card:hover {

            border-color: #a855f7;

            transform: translateY(-3px);

            box-shadow: 0 8px 25px rgba(0,0,0,0.3), 0 0 15px rgba(168,85,247,0.1);

        }

        .result-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }

        .result-type-badge {

            padding: 0.25rem 0.6rem; border-radius: 0.3rem;

            font-size: 0.7rem; font-family: 'JetBrains Mono', monospace;

            text-transform: uppercase; letter-spacing: 0.05em;

            background: rgba(168,85,247,0.15); color: #a855f7;

        }

        .result-level {

            font-size: 0.75rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

        }

        .result-title {

            font-weight: 600; color: #c8ddf2;

            font-size: 1rem; margin-bottom: 0.3rem;

        }

        .result-semester {

            font-size: 0.8rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

        }

        .result-link {

            margin-top: auto; padding-top: 0.75rem;

            font-size: 0.8rem; color: #a855f7;

            font-family: 'JetBrains Mono', monospace;

        }

        .empty-results {

            text-align: center; padding: 4rem; color: #4a6a8a;

            font-family: 'JetBrains Mono', monospace;

        }

        .loading-spinner {

            text-align: center; padding: 2rem; color: #4a6a8a;

            display: none;

        }

    </style>

</head>

<body class="page-fade-in">

    <nav class="navbar">

        <div class="container nav-container">

            <a href="index.html" class="logo" style="padding: 0; display: flex; align-items: center;">

                <img src="assets/logoiai.png" alt="Logo IAI" style="height: 200px; width: auto; object-fit: contain;">

            </a>

            <div class="nav-links">

                <ul class="nav-menu">

                    <li><a href="index.html" class="nav-item">Accueil</a></li>

                    <li><a href="exams.php" class="nav-item">Examens</a></li>

                    <li><a href="search.php" class="nav-item">Rechercher</a></li>

                    <li><a href="contribute.html" class="nav-item">Contribuer</a></li>

                </ul>

                <div class="nav-actions">

                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème" style="margin-right: 0.5rem;">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>

                    </button>

                    <a href="login.html" class="btn btn-outline auth-login-btn" style="padding: 0.5rem 1rem; border: none;">Connexion</a>

                    <a href="login.html" class="btn btn-primary auth-register-btn" style="padding: 0.5rem 1rem;">S'inscrire</a>

                    <a href="profile.php" class="btn btn-outline" id="btn-profil" style="display: none;">Profil</a>

                </div>

            </div>

            <button class="mobile-menu-btn">

                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>

            </button>

        </div>

    </nav>



    <main>

        <section class="search-hero">

            <div class="container">

                <h1>Rechercher des <span style="color:#a855f7;">Ressources</span></h1>

                <div class="search-bar-wrap">

                    <svg class="search-icon-v2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>

                    <input type="text" class="search-input-v2" id="searchInput" placeholder="Rechercher par matière, cours, mot-clé..." autofocus>

                </div>

                <div class="filters-row">

                    <select class="filter-select" id="filterLevel">

                        <option value="">Tous les niveaux</option>

                        <option value="L1">Licence 1</option>

                        <option value="L2">Licence 2</option>

                        <option value="L3_GLSI">Licence 3 GLSI</option>

                        <option value="L3_ASR">Licence 3 ASR</option>

                    </select>

                    <select class="filter-select" id="filterSemester">

                        <option value="">Tous les semestres</option>

                        <option value="Semestre1">Semestre 1</option>

                        <option value="Semestre2">Semestre 2</option>

                        <option value="Semestre3">Semestre 3</option>

                        <option value="Semestre4">Semestre 4</option>

                        <option value="Semestre5">Semestre 5</option>

                        <option value="Semestre6">Semestre 6</option>

                    </select>

                    <select class="filter-select" id="filterType">

                        <option value="">Tous les types</option>

                        <option value="exam">Examen</option>

                        <option value="course">Cours</option>

                        <option value="assignment">Devoir / TP</option>

                    </select>

                    <button class="search-btn" id="searchBtn">Rechercher</button>

                </div>

            </div>

        </section>



        <section class="section">

            <div class="container">

                <div class="results-info" id="resultsInfo" style="display:none;">

                    <span id="resultsCount"></span>

                </div>

                <div class="loading-spinner" id="loadingSpinner">Recherche en cours...</div>

                <div class="result-grid" id="resultsGrid"></div>

                <div class="empty-results" id="emptyResults" style="display:none;">

                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.3;margin-bottom:1rem;"><circle cx="11" cy="11" r="8" stroke-width="1.5"></circle><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="1.5"></line></svg>

                    <p>Aucun résultat trouvé. Essayez avec d'autres termes ou filtres.</p>

                </div>

                <div class="empty-results" id="initialState">

                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.3;margin-bottom:1rem;"><circle cx="11" cy="11" r="8" stroke-width="1.5"></circle><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="1.5"></line></svg>

                    <p>Tapez un mot-clé ou utilisez les filtres pour rechercher des documents.</p>

                    <p style="margin-top:0.5rem;font-size:0.8rem;">Cliquez «Rechercher» sans texte pour voir tous les documents disponibles.</p>

                </div>

            </div>

        </section>

    </main>



    <footer class="footer">

        <div class="container">

            <div class="footer-bottom">

                <p>&copy; 2026 IAIDOCS. Tous droits réservés.</p>

            </div>

        </div>

    </footer>



    <script src="js/main.js"></script>

    <script>

        const searchInput = document.getElementById('searchInput');

        const searchBtn = document.getElementById('searchBtn');

        const filterLevel = document.getElementById('filterLevel');

        const filterSemester = document.getElementById('filterSemester');

        const filterType = document.getElementById('filterType');

        const resultsGrid = document.getElementById('resultsGrid');

        const resultsInfo = document.getElementById('resultsInfo');

        const resultsCount = document.getElementById('resultsCount');

        const emptyResults = document.getElementById('emptyResults');

        const initialState = document.getElementById('initialState');

        const loadingSpinner = document.getElementById('loadingSpinner');



        let debounceTimer;



        function performSearch() {

            const q = searchInput.value.trim();

            const level = filterLevel.value;

            const semester = filterSemester.value;

            const type = filterType.value;



            initialState.style.display = 'none';

            loadingSpinner.style.display = 'block';

            resultsGrid.innerHTML = '';

            emptyResults.style.display = 'none';

            resultsInfo.style.display = 'none';



            const params = new URLSearchParams();

            if (q) params.set('q', q);

            if (level) params.set('level', level);

            if (semester) params.set('semester', semester);

            if (type) params.set('type', type);



            fetch('backend/search_api.php?' + params.toString())

                .then(res => res.json())

                .then(data => {

                    loadingSpinner.style.display = 'none';

                    

                    if (data.results.length === 0) {

                        emptyResults.style.display = 'block';

                        resultsInfo.style.display = 'none';

                        return;

                    }



                    resultsInfo.style.display = 'block';

                    resultsCount.innerHTML = '<strong>' + data.count + '</strong> résultat(s) trouvé(s)';



                    data.results.forEach((item, i) => {

                        const card = document.createElement('a');

                        card.href = item.link;

                        card.className = 'result-card';

                        card.style.animationDelay = (i * 50) + 'ms';

                        card.innerHTML = `

                            <div class="result-card-header">

                                <span class="result-type-badge">${escHtml(item.type)}</span>

                                <span class="result-level">${escHtml(item.level_label)}</span>

                            </div>

                            <div class="result-title">${escHtml(item.title)}</div>

                            <div class="result-semester">${escHtml(item.semester_label || '')}</div>

                            <div class="result-link">Ouvrir â</div>

                        `;

                        resultsGrid.appendChild(card);

                    });

                })

                .catch(() => {

                    loadingSpinner.style.display = 'none';

                    emptyResults.style.display = 'block';

                });

        }



        function escHtml(str) {

            const div = document.createElement('div');

            div.textContent = str;

            return div.innerHTML;

        }



        // Event listeners

        searchBtn.addEventListener('click', performSearch);

        searchInput.addEventListener('keydown', (e) => {

            if (e.key === 'Enter') performSearch();

        });



        // Live search with debounce

        searchInput.addEventListener('input', () => {

            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {

                if (searchInput.value.trim().length >= 2) {

                    performSearch();

                }

            }, 400);

        });



        // Filter changes trigger search

        [filterLevel, filterSemester, filterType].forEach(el => {

            el.addEventListener('change', () => {

                if (searchInput.value.trim() || el.value) performSearch();

            });

        });

    </script>

</body>

</html>

