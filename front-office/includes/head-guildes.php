<?php
// <head> des pages Guildes (Front-Office et administration des guildes).
// Ce fichier manquait dans restructuration-mvc (la page l'incluait sans qu'il soit versionné) :
// il est reconstitué à partir de la description de README-guildes-effects.md.
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Guildes · Yalla Quest') ?></title>
<link rel="icon" href="front-office/img/logo.png" type="image/png">
<link rel="stylesheet" href="front-office/css/guildes.css">
<?php if (!empty($enableEffects)): ?>
<link rel="stylesheet" href="front-office/css/effects.css">
<?php endif; ?>
