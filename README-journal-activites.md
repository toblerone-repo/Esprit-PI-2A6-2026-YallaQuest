# Yalla Quest — Journal d'activité, Types d'activité et Activités

## Description

**Yalla Quest** est une application web de suivi d'habitudes saines (*« Chaque habitude est une quête. »*), réalisée dans le cadre du **Projet Technologies Web 2A** à **ESPRIT** (2026-2027).

Ce module (module 2) découpe chaque **quête** en **activités** concrètes et permet au membre de les cocher dans son **journal** :

- **Objectif :** transformer une quête en petites étapes mesurables et garder une trace de ce que le membre a réalisé.
- **Problème résolu :** une quête comme *« Préparer une salade saine »* devient une liste claire (*Laver la laitue — 1 pièce*, *Râper les carottes — 100 grammes*…). Le membre voit où il en est et l'administrateur suit l'avancement de chacun.
- **Principales fonctionnalités :**
  - **Types d'activité** : catégorie et unité de mesure (*Préparation alimentaire — grammes*, *Course — km*, *Méditation — minutes*…) ;
  - **Activités** : nom, description et quantité à atteindre, rattachées à une quête et à un type ;
  - **Journal** du membre : ses quêtes en cours, la barre de progression de chacune (activités terminées / total), le formulaire *Terminer une activité* (ressenti de 1 à 5, note ou preuve) et l'historique ;
  - validation d'une quête quand toutes ses activités sont terminées (gain d'XP) ;
  - administration : liste et formulaire d'ajout ou de modification des types et des activités, avancement des membres et dernières activités terminées.

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
   * Tables utilisées par ce module :
     * `type_act` (`id_type_act`, `nom`, `unite_mesure`) ;
     * `activite_quete` (`id_activite`, `nom`, `quantite`, `description`, `id_quete`, `id_type_act`) ;
     * `realiser` : activité terminée par un membre (`id_utilisateur`, `id_activite`, `ressenti`, `date_validation`, `note_preuve`) ;
     * et `quete`, `utilisateur` (tables des autres modules).
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

- **Mon journal** (front-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/front-office/pages/journal.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/front-office/pages/journal.php)
  Résumé (quêtes en cours, activités terminées, quêtes à valider), une carte par quête avec sa progression, formulaire *Terminer une activité* et historique. Lien *Journal d'activité* dans la barre de navigation.
- **Activités** (back-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/activites.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/activites.php)
  Activités par quête (nom, description, quantité, unité, type), formulaire d'ajout ou de modification, avancement des membres et dernières activités terminées.
- **Types d'activité** (back-office) : [http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/types-activite.php](http://localhost/Esprit-PI-2A6-2026-YallaQuest/Views/back-office/pages/types-activite.php)
  Liste des types (nom, unité de mesure, nombre d'activités et de quêtes) et formulaire d'ajout ou de modification.

### Données d'exemple

| Quête | Activité | Quantité | Type |
| --- | --- | --- | --- |
| Préparer une salade saine | Laver la laitue | 1 pièce | Préparation alimentaire |
| Préparer une salade saine | Râper les carottes | 100 grammes | Préparation alimentaire |
| Bien-être du soir | Méditer | 10 minutes | Méditation |
| Bien-être du soir | Écrire des choses positives | 3 éléments | Écriture |
| Séance sportive express | Squats | 20 répétitions | Renforcement |
| Séance sportive express | Courir | 2 km | Course |

Progression d'une quête = activités terminées ÷ activités de la quête (ex. : 3/6 → **50 %**).

### Arborescence

```text
├── config.php                  connexion PDO (base yalla_quest)
├── Controllers/                (à ajouter : typeActiviteC.php, activiteC.php, journalC.php)
├── Models/                     quete.php ; à ajouter : typeActivite.php, activite.php
└── Views/
    ├── css/                    journal.css (front), journal-admin.css (back)
    ├── front-office/
    │   ├── includes/           head.php, navbar.php, footer.php
    │   └── pages/              journal.php
    └── back-office/
        ├── partials/           head.php, sidebar.php, header.php, footer.php
        └── pages/              activites.php, types-activite.php
```

### Intégration du template

Les parties communes sont incluses avec `require_once` (*Atelier Intégration Template*) :

- **Back-office :** chaque page définit `$title` et `$currentPage`, puis inclut `partials/head.php` (head, barre latérale, en-tête) ; `partials/footer.php` avant `</body>`.
- **Front-office :** dans `<head>…</head>`, `$pageTitle` et `$currentPage`, puis `includes/head.php` ; `includes/navbar.php` après `<body>`, `includes/footer.php` avant `</body>`.
- Les feuilles de style sont reliées dans `head.php` selon `$currentPage` (`journal`, `activites`, `types-activite`).

### À faire

- Créer les **Modèles** et **Contrôleurs** (CRUD des types d'activité et des activités, enregistrement dans `realiser`).
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
