<?php
/* En-tête commun des pages Back-Office (tous les membres). Définir avant l'include :
       $currentPage = 'utilisateurs';                          // clé du lien actif dans $adminPages
       $pageTitle   = 'Aventuriers · Administration · Yalla Quest';
       require __DIR__ . '/../partials/header.php';
   Ouvre le document (Views/css/guilde.css, aucun effet de mouvement) et affiche la barre d'administration.
   Chaque membre ajoute sa page ci-dessous : 'clé' => ['Libellé', 'fichier.php'] (fichiers de Views/back-office/pages/). */
$adminPages = [
    'utilisateurs' => ['Aventuriers', 'utilisateurs.php'],
    'guildes'      => ['Guildes', 'guilde_admin.php'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration · Yalla Quest') ?></title>
    <link rel="stylesheet" href="../../css/guilde.css">
    <style>
        .bo-header { display: flex; align-items: center; gap: 12px 24px; flex-wrap: wrap; padding: 18px 32px; border-bottom: 1px solid var(--rule); }
        .bo-header .brand { padding: 0; }
        .bo-header nav { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .bo-header .nav-item { width: auto; white-space: nowrap; }
        @media (max-width: 860px) {
            .bo-header { padding: 14px 16px; }
            .bo-header nav { flex-wrap: nowrap; overflow-x: auto; width: 100%; scrollbar-width: none; }
        }
    </style>
</head>
<body>
<header class="bo-header">
    <div class="brand">
        <img src="../../img/logo.jpeg" alt="" width="30" height="30">
        <strong>Yalla Quest</strong><span class="tag">ADMINISTRATION</span>
    </div>
    <nav aria-label="Administration">
        <?php foreach ($adminPages as $key => [$label, $href]): ?>
        <a class="nav-item" href="<?= htmlspecialchars($href) ?>"<?= ($currentPage ?? '') === $key ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
