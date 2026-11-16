<?php
// Barre de navigation commune du Front-Office : un lien vers chaque module, sur toutes les pages.
// Incluse juste après <body>. $frontPages et $page sont des variables globales de index.php.
$yqCurrent = $GLOBALS['page'] ?? '';
?>
<link rel="stylesheet" href="front-office/css/modules-nav.css">
<nav class="yq-modnav" aria-label="Modules YallaQuest">
  <a class="yq-modnav-brand" href="index.php"><img src="front-office/img/logo.jpeg" alt="" width="26" height="26">YallaQuest</a>
  <div class="yq-modnav-links">
    <?php foreach ($GLOBALS['frontPages'] as $yqKey => $yqLabel) { ?>
      <a href="index.php?page=<?= htmlspecialchars($yqKey) ?>"<?php if ($yqKey === $yqCurrent) { ?> aria-current="page"<?php } ?>><?= htmlspecialchars($yqLabel) ?></a>
    <?php } ?>
  </div>
  <a class="yq-modnav-admin" href="index.php?admin=tableau-de-bord">Administration ↗</a>
</nav>
