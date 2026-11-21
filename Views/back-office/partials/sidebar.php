<?php
if (!isset($currentPage)) {
    $currentPage = '';
}

function navActive(string $page, string $currentPage)
{
    return $page === $currentPage ? 'active' : '';
}
?>

<div class="sidebar">
            <div class="sidebar-brand ">
                <h4>BackOffice</h4>
            </div>

            <div class="sidebar-nav">
                <a href="#" class="<?php echo navActive('vue-ensemble', $currentPage); ?>">Vue d'ensemble</a>
                <a href="#" class="<?php echo navActive('utilisateurs', $currentPage); ?>">Utilisateurs</a>
                <a href="quetes-listes.php" class="<?php echo navActive('quetes', $currentPage); ?>">Quêtes</a>
                <a href="#" class="<?php echo navActive('competences', $currentPage); ?>">Compétences</a>
                <a href="#" class="<?php echo navActive('guildes', $currentPage); ?>">Guildes</a>
                <a href="#" class="<?php echo navActive('objectifs', $currentPage); ?>">Objectifs</a>
                <a href="#" class="<?php echo navActive('publications', $currentPage); ?>">Publications</a>
            </div>

</div>
