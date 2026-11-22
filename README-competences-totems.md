# Yalla Quest — Compétences et Totems

## Description

**Yalla Quest** est une application web de suivi d'habitudes saines (*« Chaque habitude est une quête. »*), réalisée dans le cadre du **Projet Technologies Web 2A** à **ESPRIT** (2026-2027).

Ce module gère les **compétences** et les **totems** :

- **Objectif :** montrer au membre sa progression et le récompenser.
- **Problème résolu :** l'XP gagnée en validant des quêtes devient visible : on sait quelle compétence progresse et ce qu'il reste à faire pour débloquer le prochain totem.
- **Principales fonctionnalités :**
  - XP total et niveau du membre ;
  - répartition de l'XP entre cinq compétences (Force, Endurance, Mobilité, Focus, Nutrition) selon leur part ;
  - barre de progression de chaque compétence vers le seuil de son totem ;
  - totem porté par le membre (le dernier débloqué) et tableau des totems *atteints* / *verrouillés* ;
  - administration des compétences et des totems (liste + formulaire d'ajout).

## Table des matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. Clonez le repository :
   ```bash
   git clone https://github.com/toblerone-repo/Esprit-PI-2A6-2026-YallaQuest.git
   ```
1. Placez le dossier dans le répertoire du serveur local :
   * XAMPP : `C:\xampp\htdocs\`
   * WAMP : `C:\wamp64\www\`
1. Démarrez **Apache** et **MySQL** depuis le panneau de contrôle XAMPP (ou WAMP).
1. Créez la base de données `yalla_quest` dans phpMyAdmin (onglet SQL) avec le script du groupe.
   * Tables utilisées par ce module : `competence`, `totem`, `exp_competence` (XP par membre et par compétence) et, dans `utilisateur`, les colonnes `id_totem` et `date_obtention_totem`.
1. Vérifiez la connexion dans `config.php` :
   ```php
   $dbname = "yalla_quest";
   ```

## Utilisation

### Technologies

- **PHP 8** — inclusion des parties communes (`require_once`), architecture MVC.
- **HTML5 / CSS3** — mise en page avec Flexbox et Grid.
- **MySQL** — base `yalla_quest`.
- Polices **Inter** et **Playfair Display** ([Google Fonts](https://fonts.google.com/)).

### Pages

Une fois Apache et MySQL démarrés, ouvrez dans le navigateur :

- **Mes compétences** (front-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/front-office/pages/competences.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/front-office/pages/competences.php)
  XP total, totem porté, une carte par compétence, tableau des totems. Lien *Compétences et totems* dans la barre de navigation.
- **Compétences** (back-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/competences.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/competences.php)
  Tableau des compétences (visuel, nom, description, part de l'XP, totem) et formulaire d'ajout.
- **Totems** (back-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/totems.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/totems.php)
  Tableau des totems (visuel, nom, compétence, seuil d'XP, description) et formulaire d'ajout.

### Données d'exemple

| Compétence | Part de l'XP | Totem | Seuil |
| --- | --- | --- | --- |
| Force | 30 % | Le Bélier | 300 XP |
| Endurance | 30 % | La Voile | 600 XP |
| Mobilité | 10 % | La Plume | 200 XP |
| Focus | 20 % | L'Arbre | 400 XP |
| Nutrition | 10 % | La Graine | 250 XP |

Total des parts : **100 %**. Un seul totem par compétence.

### Arborescence

```text
├── config.php                  connexion PDO (base yalla_quest)
├── Controllers/                (à ajouter : competenceC.php, totemC.php)
├── Models/                     (à ajouter : competence.php, totem.php)
└── Views/
    ├── css/                    competences.css (front), competences-admin.css (back)
    ├── img/
    │   ├── competences/        force, endurance, mobilite, focus, nutrition (.jpg)
    │   └── totems/             belier, voile, plume, arbre, graine (.jpg)
    ├── front-office/
    │   ├── includes/           head.php, navbar.php, footer.php
    │   └── pages/              competences.php
    └── back-office/
        ├── partials/           head.php, sidebar.php, header.php, footer.php
        └── pages/              competences.php, totems.php
```

### Intégration du template

Les parties communes sont incluses avec `require_once` (*Atelier Intégration Template*) :

- **Back-office :** chaque page définit `$title` et `$currentPage`, puis inclut `partials/head.php` (head, barre latérale, en-tête) ; `partials/footer.php` avant `</body>`.
- **Front-office :** dans `<head>…</head>`, `$pageTitle` et `$currentPage`, puis `includes/head.php` ; `includes/navbar.php` après `<body>`, `includes/footer.php` avant `</body>`.
- Les feuilles de style sont reliées dans `head.php` selon `$currentPage` (`competences`, `totems`).

### Images

- `Views/img/competences/` : une vignette par compétence.
- `Views/img/totems/` : un visuel par totem (600 × 600, affiché en cercle), style plat et géométrique comme le logo, aux couleurs de sa compétence. Un totem non atteint est affiché en gris (classe `competences-grise`).

### À faire

- Créer les **Modèles** et **Contrôleurs** (CRUD des compétences et des totems).
- Remplacer les données d'exemple par les données de la base et traiter les formulaires.

## Contribution

Chaque membre du groupe travaille sur **sa propre branche**, puis fusionne dans `main`.

1. Placez-vous sur votre branche (exemple : `Darine`) :
   ```bash
   git checkout Darine
   git pull origin main
   ```
1. Faites vos modifications, puis ajoutez, validez et envoyez-les :
   ```bash
   git add .
   git commit -m "Description de la modification"
   git push origin Darine
   ```
1. Ouvrez une **Pull Request** vers `main` sur GitHub et prévenez le groupe.
   * Ne modifiez pas les pages des autres membres sans les prévenir.
   * Les parties communes (`head.php`, `sidebar.php`, `header.php`, `footer.php`) servent à tout le monde : ajoutez votre page sans casser celles des autres.

## Licence

Projet académique réalisé dans le cadre du module **Projet Technologies Web 2A** à **ESPRIT** (année universitaire 2026-2027), à des fins pédagogiques. Toute réutilisation du code doit citer l'équipe du projet.
