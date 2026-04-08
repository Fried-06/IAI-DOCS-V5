# IAI DOCS V5

Plateforme web de centralisation, contribution et publication de ressources académiques (cours, devoirs, partiels, corrigés) pour les filières IAI.

> 📌 Pour une présentation narrative complète du projet, voir **[ABOUT.md](ABOUT.md)**.

---

## 1) Objectif du projet

IAI DOCS V5 sert à :

- **collecter** des documents académiques via une interface de contribution,
- **modérer** ces documents côté administrateur,
- **extraire et nettoyer** le contenu (PDF/DOCX/Markdown) via un pipeline Python + IA,
- **publier** le rendu final au format HTML statique via **Sphinx**,
- **rechercher et consulter** les ressources par niveau / semestre / matière,
- proposer un espace **Studio IA** pour l’étude assistée.

---

## 2) Stack technique (et rôle de chaque techno)

### Front-end

- **HTML/CSS/JS vanilla** : UI principale (landing, contribution, pages par niveau, etc.).
- **JavaScript Fetch API** : communication asynchrone avec les endpoints PHP (`session_check.php`, `form_options.php`, etc.).
- **Thèmes UI** (light/dark), animations, navigation mobile, transitions.

### Back-end applicatif

- **PHP 8+** : logique serveur (authentification, upload, administration, notifications, API de formulaires).
- **PDO MySQL** : couche d’accès base de données relationnelle.
- **Scripts batch/shell** : setup rapide dépendances (`setup.sh`, `setup.bat`).

### Données

- **MySQL** : stockage des utilisateurs, niveaux/semestres/matières, documents, notifications.
- **Schéma relationnel normalisé** (`levels`, `semesters`, `subjects`, `document_types`, `documents`, etc.).

### Pipeline documentaire

- **Python** pour l’orchestration : extraction, routage dans l’arborescence documentaire, build Sphinx.
- **Docling / MarkItDown / PyMuPDF** : extraction de contenu depuis fichiers bruts.
- **Google GenAI / autres providers IA** : reconstruction Markdown structurée de documents académiques.

### Publication documentaire

- **Sphinx** : compilation des contenus Markdown/RST vers HTML.
- **MyST Parser** : support Markdown moderne dans Sphinx.
- **Furo** : thème HTML docs.
- **MathJax + extensions MyST** : rendu des formules mathématiques.

### Studio IA

- Module séparé `studio/` avec interface dédiée.
- Intégrations front-end : **Marked**, **Mermaid**, **KaTeX**.
- Endpoints backend dédiés pour upload/recherche/proxy IA.

---

## 3) Architecture du dépôt (dossiers/fichiers principaux)

> Vue orientée “à quoi sert quoi” pour maintenance rapide.

```text
IAI-DOCS-V5/
├── index.html                  # Landing / portail principal
├── contribute.html             # Formulaire de contribution utilisateur
├── exams.php / search.html ... # Pages de consultation et navigation
├── css/                        # Styles globaux et thèmes
├── js/                         # JS global (auth state, UI dynamique, thème, etc.)
├── assets/                     # Logos, médias, visuels de marque
├── images/                     # Images d’illustration
├── pages/                      # Pages d’entrée par niveau/semestre
├── subjects/                   # Pages matières statiques (catalogue)
│
├── backend/                    # Noyau serveur PHP + pipeline Python
│   ├── db.php                  # Connexion PDO MySQL
│   ├── init.sql                # Schéma relationnel + seed
│   ├── setup_db.php            # Initialisation DB
│   ├── upload.php              # Upload et validation initiale utilisateur
│   ├── admin_action.php        # Modération + publication
│   ├── form_options.php        # API options dynamiques du formulaire
│   ├── session_check.php       # API état de session (navbar dynamique)
│   ├── extract.py              # Extraction/reconstruction Markdown (modes IA/docling)
│   ├── route.py                # Routage des .md vers arborescence Docs/
│   ├── build_docs.py           # Build Sphinx
│   └── sphinx_worker.php       # Worker asynchrone de compilation
│
├── Docs/                       # Source documentaire Sphinx (Markdown/RST)
│   ├── conf.py                # Config Sphinx (theme, extensions, MyST)
│   └── _build/html/           # Sortie HTML générée
│
├── drafts/                     # Brouillons Markdown en édition/admin
├── uploads/                    # Fichiers bruts uploadés (PDF/DOCX)
├── processed/                  # Fichiers markdown intermédiaires/extraits
│
├── studio/                     # Espace “AI Study Studio” (module distinct)
│   ├── index.php
│   ├── css/ js/
│   └── backend/
│
├── requirements.txt            # Dépendances Python
├── composer.json               # Dépendances PHP (plateforme)
├── setup.sh / setup.bat        # Script d’installation locale
└── *.py utilitaires            # Scripts maintenance/migration/fix divers
```

---

## 4) Comment les composants communiquent entre eux

## 4.1 Flux côté utilisateur (contribution)

1. L’utilisateur ouvre `contribute.html`.
2. Le front appelle `backend/form_options.php` pour charger dynamiquement niveaux, semestres, matières, types, années.
3. Le formulaire envoie vers `backend/upload.php`.
4. `upload.php` :
   - valide les données,
   - vérifie les doublons,
   - stocke le fichier brut (ou markdown direct),
   - crée une entrée `documents` en statut `pending`.

## 4.2 Flux côté admin (validation/publication)

1. L’admin agit via l’interface back-office.
2. `backend/admin_action.php` peut :
   - **reject** : marquer refus + notifier,
   - **generate*** : lancer `extract.py` (mode docling / IA),
   - **publish** : enregistrer brouillon, appeler `route.py`, marquer `approved` et mettre en file worker.
3. `backend/sphinx_worker.php` détecte les docs `worker_status = pending`.
4. Le worker lance `build_docs.py` (Sphinx).
5. En cas de succès : statut `success`, notifications admin; sinon `error` + message d’erreur.

## 4.3 Flux de rendu/documentation

1. Les `.md` approuvés sont placés dans `Docs/<Level>/<Semestre>/<Matiere>/<Type>/<Annee>.md`.
2. `build_docs.py` compile via Sphinx + conf `Docs/conf.py`.
3. Le HTML est publié dans `Docs/_build/html/...`.
4. Le `file_path` relatif est conservé en base pour exposition dans l’application.

## 4.4 Flux session/auth côté front

- `js/main.js` appelle `backend/session_check.php` au chargement des pages.
- La réponse JSON pilote l’affichage dynamique navbar : login/register vs profil/studio/admin/logout.

---

## 5) Base de données (résumé métier)

Tables clés :

- `users` : comptes + rôle (`student`, `admin`),
- `levels` / `semesters` / `subjects` : taxonomie académique,
- `document_types` / `years` : axes de classification,
- `documents` : coeur du cycle de vie documentaire,
- `notifications` : alertes utilisateur/admin.

Statuts documentaires principaux :

- `pending` → en attente,
- `approved` → publié,
- `rejected` → refusé.

Le worker ajoute un second niveau de statut technique (`worker_status`) pour le build Sphinx asynchrone.

---

## 6) Installation & lancement (local)

## Pré-requis

- PHP 8+
- MySQL/MariaDB
- Python 3.10+
- Composer
- pip

## Installation rapide

### Linux / macOS

```bash
./setup.sh
```

### Windows

```bat
setup.bat
```

## Initialisation DB

```bash
php backend/setup_db.php
```

> Vérifier/adapter les credentials dans `backend/db.php` si nécessaire.

## Build documentation Sphinx (manuel)

```bash
python backend/build_docs.py
```

## Worker de publication

Exécuter périodiquement :

```bash
php backend/sphinx_worker.php
```

(à planifier via cron / tâche planifiée)

---

## 7) Bonnes pratiques d’exploitation

- Vérifier que `Docs/_build/html` est accessible en lecture par le serveur web.
- Sécuriser les dossiers d’upload (`uploads/`, `studio/private_uploads/`).
- Isoler les clés API IA dans `.env` (non versionné).
- Mettre en place rotation des logs et monitoring du worker.
- Sauvegarder la base MySQL + arborescence `Docs/`.

---

## 8) Points d’attention techniques

- Le projet mélange pages `.html` et `.php` : prévoir un serveur PHP correctement configuré.
- `backend/db.php` cible MySQL; le fichier `backend/database.sqlite` présent dans le repo ne représente pas la config principale active.
- Plusieurs scripts utilitaires `v*_updater.py`, `fix_*.py` existent : utiles pour maintenance/migration, mais non requis pour le run nominal.

---

## 9) Roadmap suggérée (professionnalisation)

- Ajouter un vrai `README` “Dev Onboarding” séparé (normes code + conventions commits).
- Containeriser (Docker Compose : PHP + Nginx + MySQL + worker).
- Mettre une CI (lint PHP/JS, tests, build Sphinx) avant merge.
- Exposer une API REST documentée pour les interactions front/back.
- Renforcer tests de non-régression sur pipeline extraction/publication.

---

## 10) Résumé exécutif

IAI DOCS V5 est une plateforme hybride **PHP + Python + Sphinx** orientée cycle de vie documentaire académique complet : **contribution → modération → extraction IA → publication HTML**. Son architecture est pragmatique, déjà orientée production, et extensible vers une industrialisation DevOps/CI/CD.
