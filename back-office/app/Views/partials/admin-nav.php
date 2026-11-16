<?php
// Barre de navigation commune du Back-Office : un lien vers chaque module d'administration.
// Incluse juste après <body>. $adminPages et $adminPage viennent de index.php.
$yqCurrent = $GLOBALS['adminPage'] ?? '';
?>
<link rel="stylesheet" href="front-office/css/modules-nav.css">
<nav class="yq-modnav" aria-label="Administration YallaQuest">
  <a class="yq-modnav-brand" href="index.php?admin=tableau-de-bord"><img src="front-office/img/logo.jpeg" alt="" width="26" height="26">Admin</a>
  <div class="yq-modnav-links">
    <?php foreach ($GLOBALS['adminPages'] as $yqKey => $yqLabel) { ?>
      <a href="index.php?admin=<?= htmlspecialchars($yqKey) ?>"<?php if ($yqKey === $yqCurrent) { ?> aria-current="page"<?php } ?>><?= htmlspecialchars($yqLabel) ?></a>
    <?php } ?>
  </div>
  <a class="yq-modnav-admin" href="index.php">Voir le site ↗</a>
</nav>
