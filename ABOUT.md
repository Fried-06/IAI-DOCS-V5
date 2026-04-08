# About — IAI DOCS V5

## Vision
**IAI DOCS V5** est une plateforme académique orientée communauté qui centralise, structure et publie des ressources universitaires (cours, devoirs, partiels, corrigés) pour les étudiants IAI.  
Son ambition est double :
1. **Préserver la connaissance académique** de promotion en promotion.
2. **Rendre l’apprentissage plus efficace** grâce à une expérience moderne (recherche, structuration, studio IA).

---

## Ce que le projet résout

Dans beaucoup d’environnements universitaires, les documents sont dispersés (WhatsApp, Drive, clés USB, archives privées), souvent mal classés, et parfois inutilisables.  
IAI DOCS V5 apporte :

- une **source unique** de ressources,
- une **classification académique stricte** (niveau → semestre → matière → type → année),
- un **workflow de validation** avec administration,
- une **publication web propre** via Sphinx,
- un **module IA** pour l’étude active.

---

## Fonctionnement global (de bout en bout)

1. **Contribution** : un étudiant upload un PDF/DOCX (ou colle du Markdown) depuis l’interface de contribution.
2. **Stockage + modération** : l’entrée est enregistrée en base avec statut `pending`.
3. **Extraction/Reconstruction** : l’admin lance le pipeline (Docling / IA Gemini / cascade IA) pour obtenir un Markdown propre.
4. **Routage documentaire** : le Markdown validé est déplacé dans l’arborescence `Docs/` selon la taxonomie académique.
5. **Compilation** : un worker déclenche Sphinx pour générer les pages HTML.
6. **Publication** : le document devient consultable via son chemin public relatif.
7. **Consommation** : les étudiants explorent/recherchent les ressources, et peuvent utiliser le Studio IA.

---

## Architecture en 5 blocs

## 1) Interface Web (Front)
- Pages HTML/PHP publiques : accueil, recherche, examens, contribution, profil.
- CSS/JS maison : thème, responsive, transitions, navigation dynamique.
- API frontend via `fetch()` pour états de session et options de formulaire.

## 2) Coeur Applicatif (PHP)
- Authentification/session utilisateurs.
- Upload contrôlé + validations métier.
- Back-office admin (acceptation, rejet, génération, publication).
- Notifications utilisateurs/admin.

## 3) Données (MySQL)
- Modèle relationnel normalisé : `users`, `levels`, `semesters`, `subjects`, `document_types`, `years`, `documents`, `notifications`.
- Séparation claire entre taxonomie académique et cycle de vie documentaire.

## 4) Pipeline Documentaire (Python + IA)
- Extraction de contenu brut depuis documents.
- Nettoyage/reconstruction Markdown orientée pédagogie.
- Routage automatique vers `Docs/`.

## 5) Publication (Sphinx)
- Génération HTML statique fiable et lisible.
- Support Markdown (MyST), équations (MathJax), thème Furo.
- Sortie versionnable et facilement déployable.

---

## Les technologies et leur rôle

- **PHP 8 + PDO** : logique métier serveur et communication base.
- **MySQL** : persistance principale.
- **Python** : orchestration du pipeline documentaire.
- **Docling / PyMuPDF / MarkItDown** : extraction de contenu documentaire.
- **Google GenAI (+ providers IA selon mode)** : reconstruction intelligente et nettoyage Markdown.
- **Sphinx + MyST + Furo** : publication documentaire HTML.
- **JS (vanilla) + Fetch** : interactions client dynamiques.
- **KaTeX / Mermaid / Marked** (dans Studio) : rendu pédagogique enrichi.

---

## Différenciation du projet

Ce qui distingue IAI DOCS V5 d’un simple dépôt de fichiers :

- **workflow structuré** (soumission → validation → publication),
- **qualité éditoriale** (pipeline de reconstruction),
- **normalisation académique** (classement rigoureux),
- **scalabilité documentaire** (Sphinx + arborescence),
- **couche IA d’apprentissage** (Studio).

---

## Public cible

- Étudiants IAI (consultation/révision/contribution),
- Administrateurs pédagogiques (modération/publication),
- Contributeurs techniques (maintenance, évolution, DevOps).

---

## État actuel et maturité

Le projet est déjà exploitable en environnement réel avec un pipeline complet.  
Les axes d’industrialisation prioritaires restent :

- CI/CD,
- conteneurisation,
- couverture de tests,
- observabilité (logs/monitoring worker),
- sécurité renforcée (uploads, secrets, permissions).

---

## Résumé

**IAI DOCS V5 est une plateforme de gouvernance documentaire académique augmentée par l’IA** : elle transforme des documents bruts en ressources publiées, classées, vérifiables et utiles à toute une communauté étudiante.
