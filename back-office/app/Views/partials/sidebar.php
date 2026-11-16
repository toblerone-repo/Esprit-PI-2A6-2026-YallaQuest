<?php
// Menu latéral du Back-Office (pages Quêtes et Guildes) : un lien vers chaque module d'administration.
// $adminPages et $adminPage viennent de index.php.
$sidebarCurrent = $GLOBALS['adminPage'] ?? '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h4>BackOffice</h4>
    </div>

    <div class="sidebar-nav">
        <?php foreach ($GLOBALS['adminPages'] as $sidebarKey => $sidebarLabel) { ?>
        <a href="index.php?admin=<?= htmlspecialchars($sidebarKey) ?>" class="<?= $sidebarKey === $sidebarCurrent ? 'active' : '' ?>">
            <?= htmlspecialchars($sidebarLabel) ?>
        </a>
        <?php } ?>
        <a href="index.php">Voir le site ↗</a>
    </div>
</div>
