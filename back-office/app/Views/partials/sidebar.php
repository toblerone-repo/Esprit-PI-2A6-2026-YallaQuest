<?php
if (!isset($currentPage)) {
    $currentPage = '';
}

function navActive(string $page, string $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <h4>BackOffice</h4>
    </div>

    <div class="sidebar-nav">
        <a href="#" class="<?= navActive('accueil', $currentPage) ?>">
            Vue d'ensemble
        </a>

        <a href="quetes-listes.php" class="<?= navActive('quetes', $currentPage) ?>">
            Quêtes
        </a>

        <a href="#" class="<?= navActive('activites', $currentPage) ?>">
            Activités
        </a>

        <a href="#" class="<?= navActive('guildes', $currentPage) ?>">
            Guildes
        </a>

        <a href="#" class="<?= navActive('utilisateurs', $currentPage) ?>">
            Utilisateurs
        </a>

        <a href="#" class="<?= navActive('publications', $currentPage) ?>">
            Publications
        </a>

        <a href="#" class="<?= navActive('signalements', $currentPage) ?>">
            Signalements
        </a>
    </div>
</div>