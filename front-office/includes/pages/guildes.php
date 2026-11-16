<?php
if (!defined('YQ_ROOT')) { header('Location: ../../../index.php?page=guildes'); exit; }
$pageTitle = 'Guildes · Yalla Quest';
$currentPage = 'guildes';
$pageScript = 'guildes.js';
$enableEffects = true; // effets de défilement et de transition (css/effects.css + js/effects/), sans effet de souris ; false = page d'origine
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once YQ_FRONT . '/includes/head-guildes.php'; ?>
</head>
<body>
<?php require YQ_FRONT . '/includes/modules-nav.php'; ?>
<?php require_once YQ_FRONT . '/includes/spinner.php'; ?>
<div class="app">
<?php require_once YQ_FRONT . '/includes/navbar.php'; ?>
  <main class="canvas" id="canvas" tabindex="-1"></main>
</div>

<?php require_once YQ_FRONT . '/includes/footer-guildes.php'; ?>

<?php require_once YQ_FRONT . '/includes/scripts.php'; ?>
</body>
</html>
