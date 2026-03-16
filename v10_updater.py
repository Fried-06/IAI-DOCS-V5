import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
docs_dir = os.path.join(base_dir, "Docs")

# ═══════════════════════════════════════════════════════
# 1. SYNC LOGO: Replace ALL instances of old logos with logoiai.png
# ═══════════════════════════════════════════════════════
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)
count = 0
for f in html_files:
    with open(f, "r", encoding="utf-8") as fh:
        content = fh.read()
    original = content
    content = content.replace("IAI_DOCS2.png", "logoiai.png")
    content = content.replace("logo_iai_clean.png", "logoiai.png")
    content = content.replace("logo_iai.png", "logoiai.png")
    # Fix height inconsistency (about.html had 60px)
    content = re.sub(
        r'src="([^"]*logoiai\.png)"([^>]*)height:\s*60px',
        r'src="\1"\2height: 200px',
        content
    )
    if content != original:
        with open(f, "w", encoding="utf-8") as fh:
            fh.write(content)
        count += 1
print(f"Logo synced on {count} files.")

# ═══════════════════════════════════════════════════════
# 2. SPHINX DOCS STRUCTURE
# ═══════════════════════════════════════════════════════

subjects = {
    "L1": {
        "Semestre1": [
            "Algorithmique",
            "Langage_C",
            "Architecture_et_Maintenance",
            "Electronique_Numerique",
            "Mathematiques_Discretes",
            "Analyse_Mathematique",
            "Suite_IC3_Microsoft",
            "GNU_Linux",
            "Anglais_Informatique",
            "Expression_Ecrite_et_Orale",
        ],
        "Semestre2": [
            "Programmation_Web",
            "Programmation_Objet",
            "Conception_Systemes_Distribues",
            "Cisco_CCNA_1a",
            "Cisco_CCNA_1b",
            "Bases_de_Donnees",
            "SQL_Pratique",
            "Environnement_Economique",
            "Comptabilite_Generale",
            "Projet_Professionnel",
        ],
    },
    "L2": {
        "Semestre3": [
            "Merise",
            "Methodes_Agiles",
            "UML",
            "Administration_BD",
            "XML_et_Web_Services",
            "Probabilites_et_Statistiques",
            "Recherche_Operationnelle",
            "Theorie_des_Graphes",
            "Algebre_Lineaire",
            "Cryptographie",
            "CCNA2",
            "Securite_Informatique",
            "PHP_Web",
            "Python_Developpement",
            "Java_POO",
        ],
        "Semestre4": [
            "Anglais_Scientifique",
            "Techniques_de_Communication",
            "Redaction_Scientifique",
            "Maintenance_Informatique",
            "Electronique_Appliquee",
            "Programmation_Mobile",
            "CSharp_DotNET",
            "Cloud_Computing",
            "TIC_et_Management",
            "Droit_des_TIC",
        ],
    },
    "L3_GLSI": {
        "Semestre5": [
            "Genie_Logiciel",
            "Architecture_Logicielle",
            "JEE",
            "Big_Data",
            "Intelligence_Artificielle",
            "DevOps",
            "Analyse_Numerique",
            "Anglais_Professionnel",
            "Management_de_Projet",
            "Entrepreneuriat",
        ],
        "Semestre6": [
            "Stage_et_Memoire",
            "Projet_de_Fin_Etudes",
            "Seminaires_Professionnels",
            "Droit_du_Numerique",
            "Ethique_Informatique",
        ],
    },
    "L3_ASR": {
        "Semestre5": [
            "Administration_Systeme_Avancee",
            "Securite_Reseaux",
            "CCNA3",
            "Virtualisation",
            "Services_Reseaux",
            "Supervision_Reseaux",
            "Anglais_Professionnel",
            "Management_de_Projet",
            "Entrepreneuriat",
            "Analyse_Numerique",
        ],
        "Semestre6": [
            "Stage_et_Memoire",
            "Projet_de_Fin_Etudes",
            "Seminaires_Professionnels",
            "Droit_du_Numerique",
            "Ethique_Informatique",
        ],
    },
}

resource_types = ["devoir", "partiel", "exercice", "cours", "corrige"]
years = ["2020", "2021", "2022", "2023", "2024", "2025", "2026"]

total_files = 0
total_dirs = 0

for level, semesters in subjects.items():
    for semester, subj_list in semesters.items():
        for subject in subj_list:
            for rtype in resource_types:
                folder = os.path.join(docs_dir, level, semester, subject, rtype)
                os.makedirs(folder, exist_ok=True)
                total_dirs += 1
                for year in years:
                    filepath = os.path.join(folder, f"{year}.md")
                    if not os.path.exists(filepath):
                        # Readable subject name
                        subj_nice = subject.replace("_", " ")
                        rtype_nice = rtype.capitalize()
                        with open(filepath, "w", encoding="utf-8") as f:
                            f.write(f"# {subj_nice} — {rtype_nice} {year}\n\n")
                            f.write(f"> {level} / {semester} / {subj_nice}\n\n")
                            f.write(f"*Aucun contenu disponible pour le moment.*\n\n")
                            f.write(f"---\n\n")
                            f.write(f"📌 **Contribuer :** Si vous disposez de ce document, ")
                            f.write(f"ajoutez-le via la page [Contribuer](../../../../../../contribute.html).\n")
                        total_files += 1

# Generate Sphinx conf.py
conf_path = os.path.join(docs_dir, "conf.py")
if not os.path.exists(conf_path):
    with open(conf_path, "w", encoding="utf-8") as f:
        f.write("""# Configuration file for Sphinx documentation builder.
project = 'IAI DOCS'
copyright = '2024-2026, IAI Togo'
author = 'IAI Community'
release = '1.0'

extensions = ['myst_parser']
source_suffix = {'.rst': 'restructuredtext', '.md': 'markdown'}
templates_path = ['_templates']
exclude_patterns = []
html_theme = 'sphinx_rtd_theme'
html_static_path = ['_static']
""")

# Generate index.rst
index_path = os.path.join(docs_dir, "index.rst")
if not os.path.exists(index_path):
    with open(index_path, "w", encoding="utf-8") as f:
        f.write("""IAI DOCS — Documentation Académique
=====================================

Bienvenue sur la documentation centralisée de l'IAI Togo.

.. toctree::
   :maxdepth: 3
   :caption: Niveaux Académiques

""")
        for level in subjects:
            f.write(f"   {level}/index\n")

# Generate level-specific index files
for level, semesters in subjects.items():
    level_index = os.path.join(docs_dir, level, "index.rst")
    os.makedirs(os.path.dirname(level_index), exist_ok=True)
    with open(level_index, "w", encoding="utf-8") as f:
        nice = level.replace("_", " ")
        f.write(f"{nice}\n{'=' * len(nice)}\n\n")
        f.write(f".. toctree::\n   :maxdepth: 2\n   :caption: Semestres\n\n")
        for sem in semesters:
            f.write(f"   {sem}/index\n")

    for sem, subj_list in semesters.items():
        sem_index = os.path.join(docs_dir, level, sem, "index.rst")
        os.makedirs(os.path.dirname(sem_index), exist_ok=True)
        with open(sem_index, "w", encoding="utf-8") as f:
            f.write(f"{sem}\n{'=' * len(sem)}\n\n")
            f.write(f".. toctree::\n   :maxdepth: 2\n   :caption: Matières\n\n")
            for subj in subj_list:
                f.write(f"   {subj}/index\n")

        for subj in subj_list:
            subj_index = os.path.join(docs_dir, level, sem, subj, "index.rst")
            os.makedirs(os.path.dirname(subj_index), exist_ok=True)
            subj_nice = subj.replace("_", " ")
            with open(subj_index, "w", encoding="utf-8") as f:
                f.write(f"{subj_nice}\n{'=' * len(subj_nice)}\n\n")
                f.write(f"Ressources disponibles :\n\n")
                for rtype in resource_types:
                    rnice = rtype.capitalize()
                    f.write(f"**{rnice}**\n\n")
                    for year in years:
                        f.write(f"- `{year} <{rtype}/{year}.html>`_\n")
                    f.write(f"\n")

print(f"Sphinx structure created: {total_dirs} directories, {total_files} markdown files.")
