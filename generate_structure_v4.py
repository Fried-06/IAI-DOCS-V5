#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
generate_structure_v4.py
Génère toutes les pages PHP de pages/ et subjects/ avec:
- Protection beta_check.php intégrée
- Chemins relatifs corrects pour toutes les ressources
- Vraies variables PHP (pas corrompues)
"""
import os
import re

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

STRUCTURE = {
    "L1": {
        "titre": "Licence 1",
        "db_name": "L1",
        "semestres": {
            "semestre1": {
                "titre": "Semestre 1",
                "db_name": "Semestre 1",
                "matieres": {
                    "algorithmique": {"titre": "Algorithmique", "db_name": "Algorithmique"},
                    "langage-c": {"titre": "Langage C", "db_name": "Langage_C"},
                    "architecture-maintenance": {"titre": "Architecture et Maintenance", "db_name": "Architecture_et_Maintenance"},
                    "electronique-numerique": {"titre": "Electronique Numerique", "db_name": "Electronique_Numerique"},
                    "mathematiques-discretes": {"titre": "Mathematiques Discretes", "db_name": "Mathematiques_Discretes"},
                    "analyse-mathematiques": {"titre": "Analyse Mathematique", "db_name": "Analyse_Mathematique"},
                    "suite-ic3-microsoft": {"titre": "Suite IC3 Microsoft", "db_name": "Suite_IC3_Microsoft"},
                    "gnu-linux": {"titre": "GNU Linux", "db_name": "GNU_Linux"},
                    "anglais-informatique": {"titre": "Anglais Informatique", "db_name": "Anglais_Informatique"},
                    "expression-ecrite-orale": {"titre": "Expression Ecrite et Orale", "db_name": "Expression_Ecrite_et_Orale"},
                }
            },
            "semestre2": {
                "titre": "Semestre 2",
                "db_name": "Semestre 2",
                "matieres": {
                    "programmation-web": {"titre": "Programmation Web", "db_name": "Programmation_Web"},
                    "programmation-objet": {"titre": "Programmation Objet", "db_name": "Programmation_Objet"},
                    "conception-sd": {"titre": "Conception Systemes Distribues", "db_name": "Conception_Systemes_Distribues"},
                    "ccna1a": {"titre": "CCNA 1A", "db_name": "Cisco_CCNA_1a"},
                    "ccna1b": {"titre": "CCNA 1B", "db_name": "Cisco_CCNA_1b"},
                    "bases-de-donnees": {"titre": "Bases de Donnees", "db_name": "Bases_de_Donnees"},
                    "pratique-sql": {"titre": "Pratique SQL", "db_name": "SQL_Pratique"},
                    "environnement-economique": {"titre": "Environnement Economique", "db_name": "Environnement_Economique"},
                    "comptabilite": {"titre": "Comptabilite", "db_name": "Comptabilite_Generale"},
                    "projet-professionnel": {"titre": "Projet Professionnel", "db_name": "Projet_Professionnel"},
                }
            },
        }
    },
    "L2": {
        "titre": "Licence 2",
        "db_name": "L2",
        "semestres": {
            "semestre3": {
                "titre": "Semestre 3",
                "db_name": "Semestre 3",
                "matieres": {
                    "merise": {"titre": "Merise", "db_name": "Merise"},
                    "methodes-agiles": {"titre": "Methodes Agiles", "db_name": "Methodes_Agiles"},
                    "uml": {"titre": "UML", "db_name": "UML"},
                    "implementation-bd": {"titre": "Implementation de BD", "db_name": "Administration_BD"},
                    "xml-web-services": {"titre": "XML et Web Services", "db_name": "XML_et_Web_Services"},
                    "probabilites-statistiques": {"titre": "Probabilites et Statistiques", "db_name": "Probabilites"},
                    "recherche-operationnelle": {"titre": "Recherche Operationnelle", "db_name": "Recherche_Operationnelle"},
                    "theorie-graphes": {"titre": "Theorie des Graphes", "db_name": "Theorie_des_Graphes"},
                    "algebre-lineaire": {"titre": "Algebre Lineaire", "db_name": "Algebre_Lineaire"},
                    "cryptographie": {"titre": "Cryptographie", "db_name": "Cryptographie"},
                    "ccna2": {"titre": "CCNA 2", "db_name": "CCNA2"},
                    "securite-informatique": {"titre": "Securite Informatique", "db_name": "Securite_Informatique"},
                    "programmation-web-2": {"titre": "Programmation Web 2 (PHP)", "db_name": "PHP_Web"},
                    "python-dev": {"titre": "Developpement Python", "db_name": "Python_Developpement"},
                    "java-poo": {"titre": "Java POO", "db_name": "Java_POO"},
                }
            },
            "semestre4": {
                "titre": "Semestre 4",
                "db_name": "Semestre 4",
                "matieres": {
                    "anglais-scientifique": {"titre": "Anglais Scientifique", "db_name": "Anglais_Scientifique"},
                    "techniques-communication": {"titre": "Techniques de Communication", "db_name": "Techniques_de_Communication"},
                    "redaction-scientifique": {"titre": "Redaction Scientifique", "db_name": "Redaction_Scientifique"},
                    "maintenance-informatique": {"titre": "Maintenance Informatique", "db_name": "Maintenance_Informatique"},
                    "electronique-appliquee": {"titre": "Electronique Appliquee", "db_name": "Electronique_Appliquee"},
                    "programmation-mobile": {"titre": "Programmation Mobile", "db_name": "Programmation_Mobile"},
                    "csharp": {"titre": "C#", "db_name": "CSharp_DotNET"},
                    "cloud-computing": {"titre": "Cloud Computing", "db_name": "Cloud_Computing"},
                    "tic-management": {"titre": "Management des TIC", "db_name": "TIC_et_Management"},
                    "droit-tic": {"titre": "Droit des TIC", "db_name": "Droit_des_TIC"},
                }
            },
        }
    },
    "L3/GLSI": {
        "titre": "Licence 3 GLSI",
        "db_name": "L3 GLSI",
        "semestres": {
            "semestre5": {
                "titre": "Semestre 5",
                "db_name": "Semestre 5",
                "matieres": {
                    "sig": {"titre": "Systeme d'Information Geographique", "db_name": "Systeme_Information_Geographique"},
                    "big-data": {"titre": "Big Data (NoSQL)", "db_name": "Big_Data"},
                    "aide-decision": {"titre": "Systeme d'Information d'Aide a la Decision", "db_name": "Aide_Decision"},
                    "programmation-jee": {"titre": "Programmation JEE (Spring Boot, JSP)", "db_name": "JEE"},
                    "programmation-distribuee": {"titre": "Architecture Logicielle", "db_name": "Architecture_Logicielle"},
                    "administration-oracle": {"titre": "Administration des BD Oracle", "db_name": "Administration_Oracle"},
                    "securite-bd": {"titre": "Securite des Bases de Donnees", "db_name": "Securite_BD"},
                    "administration-sqlserver": {"titre": "DevOps", "db_name": "DevOps"},
                    "introduction-ia": {"titre": "Intro a l'Intelligence Artificielle", "db_name": "Intelligence_Artificielle"},
                    "analyse-donnees": {"titre": "Analyse Numerique", "db_name": "Analyse_Numerique"},
                    "genie-logiciel": {"titre": "Introduction au Genie Logiciel", "db_name": "Genie_Logiciel"},
                    "anglais-expert": {"titre": "Anglais Professionnel", "db_name": "Anglais_Professionnel"},
                    "developpement-personnel": {"titre": "Management de Projet", "db_name": "Management_de_Projet"},
                }
            },
            "semestre6": {
                "titre": "Semestre 6",
                "db_name": "Semestre 6",
                "matieres": {
                    "creation-entreprises": {"titre": "Creation d'Entreprises", "db_name": "Entrepreneuriat"},
                    "droit-travail": {"titre": "Droit du Numerique", "db_name": "Droit_du_Numerique"},
                    "poo-avancee": {"titre": "Projet de Fin d'Etudes", "db_name": "Projet_de_Fin_Etudes"},
                    "outils-programmation-web": {"titre": "Seminaires Professionnels", "db_name": "Seminaires_Professionnels"},
                    "audit-si": {"titre": "Stage et Memoire", "db_name": "Stage_et_Memoire"},
                    "techniques-multimedia": {"titre": "Ethique Informatique", "db_name": "Ethique_Informatique"},
                }
            },
        }
    },
    "L3/ASR": {
        "titre": "Licence 3 ASR",
        "db_name": "L3 ASR",
        "semestres": {
            "semestre5": {
                "titre": "Semestre 5",
                "db_name": "Semestre 5",
                "matieres": {
                    "programmation-systeme": {"titre": "Programmation Systeme", "db_name": "Programmation_Systeme"},
                    "linux-avance": {"titre": "Administration Systeme Avancee (Linux)", "db_name": "Administration_Systeme_Avancee"},
                    "ccna3": {"titre": "Reseau et Technologie CISCO CCNA 3", "db_name": "CCNA3"},
                    "huawei-network": {"titre": "Services Reseaux", "db_name": "Services_Reseaux"},
                    "association": {"titre": "Supervision Reseaux", "db_name": "Supervision_Reseaux"},
                    "administration-reseaux": {"titre": "Virtualisation", "db_name": "Virtualisation"},
                    "administration-systemes": {"titre": "Analyse Numerique", "db_name": "Analyse_Numerique"},
                    "big-data-asr": {"titre": "Big Data", "db_name": "Big_Data"},
                    "securite-reseaux": {"titre": "Securite Reseaux", "db_name": "Securite_Reseaux"},
                    "anglais-expert-asr": {"titre": "Anglais Professionnel", "db_name": "Anglais_Professionnel"},
                    "toeic": {"titre": "Preparation TOEIC", "db_name": "Preparation_TOEIC"},
                    "developpement-personnel-asr": {"titre": "Management de Projet", "db_name": "Management_de_Projet"},
                }
            },
            "semestre6": {
                "titre": "Semestre 6",
                "db_name": "Semestre 6",
                "matieres": {
                    "creation-entreprises-asr": {"titre": "Creation d'Entreprises", "db_name": "Entrepreneuriat"},
                    "droit-travail-asr": {"titre": "Droit du Numerique", "db_name": "Droit_du_Numerique"},
                    "modele-structuration-reseaux": {"titre": "Projet de Fin d'Etudes", "db_name": "Projet_de_Fin_Etudes"},
                    "architectures-avancees-reseaux": {"titre": "Seminaires Professionnels", "db_name": "Seminaires_Professionnels"},
                    "reseaux-mobiles": {"titre": "Stage et Memoire", "db_name": "Stage_et_Memoire"},
                    "fibre-optique": {"titre": "Ethique Informatique", "db_name": "Ethique_Informatique"},
                }
            },
        }
    },
}


def get_navbar():
    return """    <nav class="navbar">
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
    </nav>"""


def make_level_index(level_key, level_data, beta_check_path, css_path, js_path):
    """Generate the level index.php (lists semesters)"""
    titre = level_data['titre']
    navbar = get_navbar()
    
    sem_cards = ""
    for sem_key, sem_data in level_data['semestres'].items():
        sem_titre = sem_data['titre']
        sem_cards += f"""
        <a href="{level_key}/{sem_key}" class="level-card">
            <div class="level-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div>
                <div class="level-title">{sem_titre}</div>
                <div class="level-desc">Voir les matieres de ce semestre</div>
            </div>
        </a>"""

    return f"""<?php
session_start();
require_once __DIR__ . '/{beta_check_path}';
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/pages/'));
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{titre} - Ressources IAI</title>
    <base href="<?= $base_url ?>/">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
{navbar}
    <main class="container" style="padding: 4rem 0;">
        <h1 style="color:var(--primary);margin-bottom:2rem;text-align:center;">{titre}</h1>
        <div class="grid grid-cols-2">
{sem_cards}
        </div>
    </main>
    <script src="js/main.js?v=4"></script>
</body>
</html>
"""


def make_semester_page(level_key, level_titre, sem_key, sem_data, beta_check_path, css_path, js_path):
    """Generate a semester page listing all subjects"""
    sem_titre = sem_data['titre']
    navbar = get_navbar()
    
    subject_cards = ""
    for mat_key, mat_nom in sem_data['matieres'].items():
        if isinstance(mat_nom, dict):
            mat_nom = mat_nom['titre']
            
        # Build URL for subject: Matiere/L1/semestre1/algorithmique
        level_url = level_key.replace('/', '/')  # keep as is
        subject_cards += f"""
            <a href="Matiere/{level_key}/{sem_key}/{mat_key}" class="exam-card" style="text-align:center;border-top:4px solid var(--primary-light);">
                <h3 class="exam-title" style="margin-top:1rem;margin-bottom:1rem;">{mat_nom}</h3>
                <span class="btn btn-outline" style="width:100%;">Voir les ressources</span>
            </a>"""

    return f"""<?php
session_start();
require_once __DIR__ . '/{beta_check_path}';
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/pages/'));
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{level_titre} - {sem_titre} - Ressources IAI</title>
    <base href="<?= $base_url ?>/">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
{navbar}
    <main class="container" style="padding: 4rem 0;">
        <h1 style="color:var(--primary);margin-bottom:2rem;">
            <a href="{level_key}" style="color:var(--text-muted);font-size:1.2rem;">{level_titre}</a>
            &gt; {sem_titre}
        </h1>
        <div class="grid grid-cols-3">
{subject_cards}
        </div>
    </main>
    <script src="js/main.js?v=4"></script>
"""


def make_subject_page(level_key, level_titre, sem_key, sem_titre, mat_key, mat_nom, mat_db_name, db_level, db_sem, beta_check_path, css_path, js_path):
    """Generate a subject page with dynamic PHP content from DB"""
    navbar = get_navbar()
    import html
    mat_nom_html = html.escape(mat_nom)
    mat_nom_escaped_php = mat_nom.replace("'", "\\'")
    mat_db_name_escaped_php = mat_db_name.replace("'", "\\'")
    
    return f"""<?php
session_start();
require_once __DIR__ . '/{beta_check_path}';
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/{beta_check_path.replace("beta_check.php", "db.php")}';
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/subjects/'));

// Fetch documents for this subject from the DB
$documents = [];
$totalDocs = 0;
try {{
    $pdo = getDB();
    $stmt = $pdo->prepare(
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
           AND LOWER(dt.name) NOT IN ('cours', 'exercice')
           AND LOWER(l.name) = LOWER(:level)
           AND LOWER(sem.name) = LOWER(:semester)
           AND LOWER(s.name) = LOWER(:subject)
         ORDER BY y.year DESC, dt.name"
     );
    $stmt->execute([
        ':level'    => '{db_level}',
        ':semester' => '{db_sem}',
        ':subject'  => '{mat_db_name_escaped_php}'
    ]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalDocs = count($records);

    foreach ($records as $rec) {{
        // PDF Link
        if (!empty($rec['pdf_url'])) {{
            $pdfLink = (strpos($rec['pdf_url'], 'http') === 0)
                ? $rec['pdf_url']
                : '/' . ltrim($rec['pdf_url'], '/');
        }} else {{
            $pdfLink = '#';
        }}
        // HTML Viewer Link
        $htmlLink = '#';
        if (!empty($rec['file_path']) || !empty($rec['pdf_url'])) {{
            $htmlLink = 'viewer/' . $rec['id'];
        }}
        $documents[] = [
            'id'       => $rec['id'],
            'title'    => $rec['title'],
            'type'     => $rec['type_name'],
            'year'     => $rec['year'],
            'pdfLink'  => $pdfLink,
            'htmlLink' => $htmlLink,
            'hasHtml'  => ($htmlLink !== '#'),
            'user'     => $rec['user_name'] ?? 'Anonyme'
        ];
    }}
}} catch (Exception $e) {{
    error_log("Subject page DB error: " . $e->getMessage());
}}

// Group by year
$byYear = [];
foreach ($documents as $doc) {{
    $byYear[$doc['year']][] = $doc;
}}
krsort($byYear);
$years = array_keys($byYear);
$firstYear = !empty($years) ? $years[0] : date('Y');
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mat_nom_html} - Ressources IAI</title>
    <meta name="description" content="Ressources de la matiere {mat_nom_html} pour {level_titre} - {sem_titre}">
    <base href="<?= $base_url ?>/">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=2"></script>
</head>
<body class="page-fade-in">
{navbar}

    <main class="container" style="padding: 4rem 0;">
        <div class="subject-header">
            <h1 style="color: var(--primary); margin-bottom: 0.5rem;">{mat_nom_html}</h1>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">{level_titre} - {sem_titre}</p>
            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <span style="background:var(--primary-light);color:#000;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.8rem;font-weight:600;">
                    <?= $totalDocs ?> document<?= $totalDocs > 1 ? 's' : '' ?> disponible<?= $totalDocs > 1 ? 's' : '' ?>
                </span>
                <a href="Rechercher" style="color:var(--text-muted);font-size:0.85rem;">&#8592; Rechercher d'autres ressources</a>
            </div>
        </div>

        <?php if (empty($documents)): ?>
        <div style="text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document disponible pour le moment</p>
            <p style="font-size:0.9rem;">Soyez le premier a contribuer pour cette matiere !</p>
            <a href="Contribuer" class="btn btn-primary" style="margin-top:1.5rem;display:inline-block;">Contribuer</a>
        </div>
        <?php else: ?>        <div class="subject-layout">
            <!-- Sidebar: Filters -->
            <aside class="sidebar">
                <div class="sidebar-title">Filtres</div>
                
                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em; margin-top:1rem;">Type de document</h4>
                <div class="type-filters" style="margin-bottom: 2rem;">
                    <button class="year-btn active" data-type="all">Tous les types</button>
                    <?php 
                    $typesPresent = [];
                    foreach($documents as $d) {{ $typesPresent[$d['type']] = true; }}
                    foreach(array_keys($typesPresent) as $type): 
                    ?>
                    <button class="year-btn" data-type="<?= htmlspecialchars($type) ?>">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type))) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em;">Annee Academique</h4>
                <div class="year-filters">
                    <button class="year-btn active" data-year="all">Toutes les annees</button>
                    <?php foreach ($years as $yr): ?>
                    <button class="year-btn" data-year="<?= $yr ?>">Annee <?= $yr ?></button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Content Panels -->
            <div class="subject-content">
                <div class="resource-grid" id="docs-grid">
                    <?php foreach ($documents as $doc): ?>
                    <div class="resource-card doc-item" data-year="<?= $doc['year'] ?>" data-type="<?= htmlspecialchars($doc['type']) ?>" style="display:flex; flex-direction:column; text-align:left;">
                        <h3 style="margin-bottom:0.5rem;color:var(--text-main);font-size:0.95rem;"><?= htmlspecialchars($doc['title']) ?></h3>
                        <div style="margin-bottom:0.5rem;">
                            <span style="font-size:0.75rem; padding:0.2rem 0.5rem; background:var(--primary-light); color:#000; border-radius:4px; font-weight:600; text-transform:capitalize; margin-right:0.5rem;">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['type'])) ?>
                            </span>
                            <span style="font-size:0.75rem; padding:0.2rem 0.5rem; background:var(--bg3); color:var(--text); border-radius:4px; font-weight:600;">
                                <?= $doc['year'] ?>
                            </span>
                        </div>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem; flex-grow:1;">
                            Par <?= htmlspecialchars($doc['user'] ? $doc['user'] : 'Anonyme') ?>
                        </p>
                        <div style="display:flex;gap:0.5rem;flex-direction:column;margin-top:auto;">
                            <?php if ($doc['hasHtml']): ?>
                            <a href="<?= htmlspecialchars($doc['htmlLink']) ?>"
                               class="btn btn-primary"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;">
                                &#128065; Voir HTML
                            </a>
                            <?php endif; ?>
                            <?php if ($doc['pdfLink'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($doc['pdfLink']) ?>"
                               class="btn btn-outline"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;"
                               target="_blank" rel="noopener">
                                &#128196; Voir PDF
                            </a>
                            <?php endif; ?>
                            <?php if (!$doc['hasHtml'] && $doc['pdfLink'] === '#'): ?>
                            <span style="color:var(--text-muted);font-size:0.8rem;text-align:center;">Document indisponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="no-docs-message" style="display:none; text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document ne correspond a vos criteres</p>
                    <p style="font-size:0.9rem;">Essayez de modifier vos filtres.</p>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        let currentType = 'all';
        let currentYear = 'all';

        function filterDocs() {{
            document.querySelectorAll('.type-filters .year-btn').forEach(btn => {{
                btn.onclick = function() {{
                    document.querySelectorAll('.type-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentType = this.getAttribute('data-type');
                    applyFilters();
                }};
            }});
            document.querySelectorAll('.year-filters .year-btn').forEach(btn => {{
                btn.onclick = function() {{
                    document.querySelectorAll('.year-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentYear = this.getAttribute('data-year');
                    applyFilters();
                }};
            }});
        }}
        
        function applyFilters() {{
            let visibleCount = 0;
            document.querySelectorAll('.doc-item').forEach(item => {{
                const matchType = (currentType === 'all' || item.getAttribute('data-type') === currentType);
                const matchYear = (currentYear === 'all' || item.getAttribute('data-year') === currentYear);
                if (matchType && matchYear) {{
                    item.style.display = 'block';
                    visibleCount++;
                }} else {{
                    item.style.display = 'none';
                }}
            }});
            document.getElementById('no-docs-message').style.display = visibleCount === 0 ? 'block' : 'none';
        }}
        
        // Initialiser
        filterDocs();
    </script>
    <script src="js/main.js?v=4"></script>
</body>
</html>
"""


def generate():
    pages_generated = 0
    
    for level_key, level_data in STRUCTURE.items():
        level_dir_key = level_key  # e.g. "L1", "L3/GLSI"
        pages_dir = os.path.join(BASE_DIR, 'pages', level_dir_key.replace('/', os.sep))
        os.makedirs(pages_dir, exist_ok=True)
        
        # Depths for pages/L1/ = 2 levels deep from root -> ../../backend/beta_check.php
        level_depth = len(pages_dir.replace(BASE_DIR, '').strip(os.sep).split(os.sep))
        beta_check_rel = '/' + '/'.join(['..'] * level_depth) + '/backend/beta_check.php'
        beta_check_rel = '../' * level_depth + 'backend/beta_check.php'
        css_path = '/css/style.css?v=2'
        js_path = '/js/main.js?v=4'
        
        # Generate level index
        idx_content = make_level_index(level_key, level_data, beta_check_rel, css_path, js_path)
        idx_path = os.path.join(pages_dir, 'index.php')
        with open(idx_path, 'w', encoding='utf-8') as f:
            f.write(idx_content)
        print(f"[OK] {idx_path}")
        pages_generated += 1
        
        for sem_key, sem_data in level_data['semestres'].items():
            # Generate semester page
            sem_content = make_semester_page(
                level_key, level_data['titre'],
                sem_key, sem_data,
                beta_check_rel, css_path, js_path
            )
            sem_path = os.path.join(pages_dir, f'{sem_key}.php')
            with open(sem_path, 'w', encoding='utf-8') as f:
                f.write(sem_content)
            print(f"[OK] {sem_path}")
            pages_generated += 1
            
            # Generate subject pages
            subj_dir = os.path.join(BASE_DIR, 'subjects', level_dir_key.replace('/', os.sep), sem_key)
            os.makedirs(subj_dir, exist_ok=True)
            
            subj_depth = len(subj_dir.replace(BASE_DIR, '').strip(os.sep).split(os.sep))
            subj_beta_rel = '../' * subj_depth + 'backend/beta_check.php'
            
            for mat_key, mat_info in sem_data['matieres'].items():
                if isinstance(mat_info, dict):
                    mat_nom = mat_info["titre"]
                    mat_db_name = mat_info["db_name"]
                else:
                    mat_nom = mat_info
                    mat_db_name = mat_nom
                subj_content = make_subject_page(
                    level_key, level_data['titre'],
                    sem_key, sem_data['titre'],
                    mat_key, mat_nom, mat_db_name,
                    level_data['db_name'], sem_data['db_name'],
                    subj_beta_rel, css_path, js_path
                )
                subj_path = os.path.join(subj_dir, f'{mat_key}.php')
                with open(subj_path, 'w', encoding='utf-8') as f:
                    f.write(subj_content)
                print(f"[OK] {subj_path}")
                pages_generated += 1
    
    print(f"\n{'='*50}")
    print(f" Structure v4 generee : {pages_generated} fichiers PHP")
    print(f"{'='*50}")


if __name__ == '__main__':
    generate()
