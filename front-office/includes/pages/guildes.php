<?php
$pageTitle = 'Guildes · Yalla Quest';
$currentPage = 'guildes';
$pageScript = 'guildes.js';
$enableEffects = true; // effets de défilement et de souris (css/effects.css + js/effects/) ; false = page d'origine
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/includes/spinner.php'; ?>
<div class="app">
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
  <main class="canvas" id="canvas" tabindex="-1"></main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php require_once __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
