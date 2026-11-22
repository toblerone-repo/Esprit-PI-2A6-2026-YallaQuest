# YallaQuest — template étudiant 6

Ce dossier contient le template Communauté / conseils, ses styles, ses images, sa police et sa validation JavaScript. Aucun traitement de base de données n’est inclus.

## Structure

```text
projet_web/
├── README-module-6.md
├── Controllers/ (vide)
├── Models/ (vide)
└── Views/
    ├── css/ (communaute-*)
    ├── img/ (communaute-*)
    ├── js/
    │   └── communaute-validation.js
    ├── lib/
    │   └── communaute-fonts/
    │       ├── PlayfairDisplay.ttf
    │       └── OFL.txt
    ├── front-office/
    │   ├── includes/
    │   │   └── navbar.php
    │   └── pages/
    │       └── communaute.php
    └── back-office/
        ├── partials/ (vide)
        └── pages/
            ├── publications.php
            └── signalements.php
```

Models/ et Controllers/ sont vides. Le module contient uniquement le template. CommunauteAssets.php est supprimé.

## Affichage sur localhost

Les pages s’ouvrent directement, sans index.php :

- [Communauté](http://localhost/projet_web/Views/front-office/pages/communaute.php)
- [Publications](http://localhost/projet_web/Views/back-office/pages/publications.php)
- [Signalements](http://localhost/projet_web/Views/back-office/pages/signalements.php)

Les adresses des images, styles, scripts et pages sont écrites directement dans les attributs HTML href, src et action. Les valeurs comme les identifiants et les filtres restent insérées dans les paramètres des liens pour afficher les exemples.

Les données d’exemple sont intégrées directement dans les trois pages du template. Les paramètres GET permettent d’afficher les différents états : liste, détail, ajout, modification, suppression et historique. Ils servent aussi aux filtres des exemples.

Les formulaires possèdent les contrôles JavaScript, mais aucun enregistrement, suppression ou traitement de signalement n’est exécuté. Le traitement des formulaires sera à ajouter plus tard dans les contrôleurs.

## Styles et validation

Les grands titres utilisent Playfair Display, incluse localement avec sa licence SIL Open Font License.

La navbar du front-office est dans includes/navbar.php et est incluse, juste après `<body>`, par toutes les pages du front-office sauf join_now.php et login.php (pages de compte, sans navbar ni pied de page). Elle charge elle-même son style, css/communaute-navbar.css, avec des classes propres au module. Les sept rubriques sont Quêtes, Journal d'activité, Compétences et totems, Objectifs, Guildes, Communauté et Profils ; chacune pointe vers sa page, et la rubrique de la page ouverte est marquée active.

Les styles ciblent le module .yq-community. L’ouverture directe utilise body.yq-community-page pour le fond sombre et l’absence de marge blanche. Le back-office possède 32 px de padding latéral, réduits à 16 px sur mobile.

Chaque champ est récupéré par document.getElementById(). Les conditions if vérifient les valeurs et les longueurs, sans paramètre required ni attribut HTML de validation.

| Champ | Contrôle JavaScript |
| --- | --- |
| Titre | Texte non vide, de 3 à 150 caractères |
| Message | Texte non vide, de 10 à 5 000 caractères |
| Auteur | De 2 à 100 caractères |
| Catégorie, type, statut, motif | Choix dans la liste correspondante |
| Complément | Facultatif, jusqu’à 5 000 caractères |
| Précisions et note | Facultatifs, jusqu’à 1 000 caractères |

## Intégration du groupe

Importer uniquement les fichiers de l’archive du template, en conservant leur structure. Aucun index.php, layout.php, config.php ou .htaccess n’est fourni.

Les pages restent des fragments lorsqu’elles sont incluses dans un layout. Le bandeau du back-office est intégré directement à publications.php et apparaît en ouverture directe. Lors de l’intégration, la vue utilise le header du layout du groupe. Aucun fichier header.php indépendant n’est fourni.

Les chemins HTML sont relatifs aux dossiers pages/ ou partials/includes de ces fichiers. Si le groupe affiche le contenu depuis une autre adresse, adapter les liens HTML au routeur commun. Aucun calcul automatique d’URL n’est utilisé.

Le diff Git local contient les suppressions de l’ancienne structure. Pour l’import dans le dépôt partagé, utiliser les fichiers de l’archive sans appliquer les suppressions des fichiers communs ou des autres modules.
