# YallaQuest

Projet PHP organisé en Front-Office, Back-Office et fichiers partagés, avec les notions des chapitres 4, 5 et 6 du cours.

## Arborescence

```text
projet_web/
├── config.php
├── index.php
├── logo.jpeg
├── shared/
│   ├── css/
│   │   ├── common.css
│   │   ├── front.css
│   │   └── admin.css
│   ├── images/
│   ├── icons.svg
│   └── options.php
├── Front-Office/
│   ├── index.php
│   ├── communaute.php
│   ├── Controller/
│   │   └── CommunauteController.php
│   ├── Model/
│   │   ├── Publication.php
│   │   └── Signalement.php
│   └── View/
│       ├── communaute.php
│       └── unavailable.php
└── Back-Office/
    ├── index.php
    ├── Controller/
    │   ├── PublicationsController.php
    │   └── SignalementsController.php
    ├── Model/
    │   ├── Publication.php
    │   └── Signalement.php
    └── View/
        ├── layout.php
        ├── publications.php
        └── signalements.php
```

Le back-office contient 8 fichiers PHP. Les formulaires, détails et listes sont regroupés dans la vue de chaque module. Le tableau de bord fait partie du module Publications.

## Accès directs

Démarrer Apache, puis ouvrir :

- Communauté : [http://localhost/projet_web/Front-Office/communaute.php](http://localhost/projet_web/Front-Office/communaute.php)
- Administration : [http://localhost/projet_web/Back-Office/](http://localhost/projet_web/Back-Office/)

L’adresse [http://localhost/projet_web/](http://localhost/projet_web/) redirige directement vers la communauté.

Le back-office utilise un seul fichier d’entrée, `index.php`, qui choisit le contrôleur avec un simple `if`. Le front utilise son fichier `communaute.php`. Les contrôleurs lisent les formulaires et préparent les données des vues. Les modèles exécutent les requêtes PDO. Les deux espaces utilisent les mêmes modèles de publications et de signalements.

Le front contient la communauté sans navbar, footer ni écran de chargement. Les styles sont du CSS classique dans `shared/css/`.

## Pages du back-office

| Adresse dans Back-Office | Contenu |
| --- | --- |
| `index.php` | Liste, recherche et filtres |
| `index.php?action=tableau-de-bord` | Compteurs, récits récents et signalements ouverts |
| `index.php?action=voir&id=1` | Détail d’une publication |
| `index.php?action=ajouter` | Formulaire de création |
| `index.php?action=modifier&id=1` | Formulaire de modification |
| `index.php?action=supprimer&id=1` | Confirmation de suppression |
| `index.php?module=signalements` | Signalements à examiner |
| `index.php?module=signalements&statut=closed` | Historique des décisions |
| `index.php?module=signalements&action=voir&id=1` | Détail et traitement d’un signalement |

Remplacer `1` par l’identifiant du contenu concerné. Les enregistrements, suppressions et décisions passent par des formulaires POST.

## PHP utilisé

- Chapitre 4 : variables, tableaux, `isset`, `if` / `elseif`, `switch`, `foreach`, fonctions et formulaires GET / POST.
- Chapitre 5 : classes, constructeurs, méthodes publiques et privées, organisation MVC.
- Chapitre 6 : connexion PDO, `prepare()`, `execute()`, `fetch()`, `fetchAll()` et `try` / `catch`.

Les formulaires transmettent leurs valeurs directement aux contrôleurs. Les champs obligatoires, les limites de longueur et les vérifications de saisie PHP et HTML sont retirés. Les messages après une modification utilisent un code simple dans l’adresse de redirection.

Quelques fonctions PHP complètent le cours : `htmlspecialchars()` affiche le texte saisi comme du texte HTML, les fonctions `mb_*` gèrent les lettres accentuées, `urlencode()` conserve les mots recherchés dans les liens, `date()` et `strtotime()` affichent les dates. `header()` effectue les redirections.

## Connexion MySQL

Le nom de la base dans `config.php` est vide. Pour connecter ta propre base, renseigner `$host`, `$database`, `$user` et `$password` dans ce fichier.

Sans connexion, les listes et compteurs sont vides, la recherche et l’aperçu public restent utilisables. L’enregistrement est indisponible. Le formulaire public produit un aperçu ; les publications enregistrées sont gérées dans l’administration.

Les requêtes des modèles utilisent les champs suivants ; les adapter si ta base utilise d’autres noms :

| Table | Champs |
| --- | --- |
| `publications` | `id`, `author`, `initials`, `category`, `type`, `title`, `content`, `more`, `image`, `status`, `created_at`, `updated_at` |
| `signalements` | `id`, `publication_id`, `publication_title`, `publication_author`, `publication_content`, `reason`, `details`, `status`, `moderator_note`, `decision`, `created_at`, `handled_at` |

Les champs `image`, `decision` et `handled_at` peuvent être nuls. `publication_id` peut être nul après une suppression. Les identifiants sont auto-incrémentés. Les catégories, statuts et motifs sont définis dans `shared/options.php`.

La suppression d’une publication conserve les copies des récits dans les signalements et clôture les signalements encore ouverts. L’administration reste accessible localement grâce à son fichier `.htaccess`.
