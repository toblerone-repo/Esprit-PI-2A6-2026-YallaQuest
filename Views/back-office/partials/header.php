<?php
/* En-tête commun des pages Back-Office (tous les membres).
   À inclure juste après la barre latérale, au début de <body> :
       <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
       <?php include_once __DIR__ . '/../partials/header.php'; ?>
   Il charge son propre style (Views/css/bo-header.css) et ne dépend d'aucune autre feuille :
   il s'affiche de la même façon sur toutes les pages, quel que soit leur CSS.
   Pas de logo ici : il est déjà dans la barre latérale.
   Le lien dont la clé vaut $currentPage (à définir avant l'include, facultatif) est marqué comme actif.
   Chaque membre ajoute sa page ci-dessous : 'clé' => ['Libellé', 'fichier.php'] (fichiers de Views/back-office/pages/). */
$adminPages = [
    'utilisateurs' => ['Aventuriers', 'utilisateurs.php'],
];
?>
<link rel="stylesheet" href="../../css/bo-header.css">
<header class="bo-header">
    <div class="bo-header-brand">
        <strong>Yalla Quest</strong><span class="bo-header-tag">ADMINISTRATION</span>
    </div>
    <nav aria-label="Administration">
        <?php foreach ($adminPages as $boHeaderKey => [$boHeaderLabel, $boHeaderHref]): ?>
        <a class="bo-header-link" href="<?= htmlspecialchars($boHeaderHref) ?>"<?= ($currentPage ?? '') === $boHeaderKey ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($boHeaderLabel) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
