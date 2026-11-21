# Yalla Quest — Guildes

Projet Technologies Web 2A — ESPRIT (2026-2027). Partie « Guildes » : indicateur de chargement (front-office), en-tête commun du back-office, page Guildes et ses effets.

## Pages et includes
- `Views/front-office/includes/spinner.php` — indicateur de chargement : `<?php require __DIR__ . '/../includes/spinner.php'; ?>` juste après `<body>`
- `Views/back-office/partials/header.php` — en-tête commun du back-office ; chaque membre ajoute sa page dans `$adminPages`
- `Views/back-office/pages/guilde_admin.php` — administration des guildes : chiffres clés, tableau des guildes, tableau des propositions de quêtes (statique, sans effets) ; lien « Guildes » dans l'en-tête
- `Views/front-office/pages/guildes.php` — espace membre en HTML statique : guildes, pages des guildes (propositions de quêtes), quêtes ; une section par adresse (`#guildes`, `#g1`…`#g5`, `#quetes`), navigation sous le titre ; seul le JavaScript des effets reste

Lancer avec XAMPP/WAMP ou `php -S localhost:8000` à la racine, puis ouvrir `http://localhost:8000/Views/front-office/pages/guildes.php`.

Toutes les pages chargent une seule feuille : `Views/css/guilde.css` (`../../css/guilde.css` depuis une page de `pages/`).

## Arborescence
```
├── Controllers/        (vide pour l'instant)
├── Models/             (vide pour l'instant)
└── Views/
    ├── css/            guilde.css (feuille unique : commun, front, back, effets, page Guildes)
    ├── img/            logo.jpeg, competences/ (vignettes des compétences)
    ├── js/             effects/ (effets Guildes + README)
    ├── lib/            fonts/, gsap/, lenis/, splittype/, three/
    ├── front-office/
    │   ├── includes/   spinner.php (indicateur de chargement, commun au front-office)
    │   └── pages/      guildes.php
    └── back-office/
        ├── partials/   header.php (en-tête commun du back-office, tous les membres)
        └── pages/      guilde_admin.php (administration des guildes)
```

Détails, réglages et liste de tests : `Views/js/effects/README.md`.
