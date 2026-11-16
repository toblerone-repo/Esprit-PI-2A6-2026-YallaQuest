<?php
// Back-Office · gestion des guildes (CRUD par l'administrateur).
// Interface seulement : Models/GuildeModel.js est un échantillon en mémoire, Controllers/GuildeController.js agit dessus.
// À brancher plus tard sur le contrôleur PHP et la base (voir README-guildes-effects.md, « Administration »).
if (!defined('YQ_ROOT')) { header('Location: ../../../../index.php?admin=guildes'); exit; }
$pageTitle = 'Administration · Guildes · Yalla Quest';
$currentPage = 'admin-guildes';
$pageScript = 'admin-guildes.js';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once YQ_FRONT . '/includes/head-guildes.php'; ?>
</head>
<body class="admin" data-base="front-office/">
<?php require YQ_BACK . '/app/Views/partials/admin-nav.php'; ?>
<?php require_once YQ_FRONT . '/includes/spinner.php'; ?>
<div class="app">
<?php require_once YQ_BACK . '/app/Views/partials/sidebar.php'; ?>

  <main class="canvas" id="canvas" tabindex="-1"></main>
</div>

<?php require_once YQ_FRONT . '/includes/footer-guildes.php'; ?>

<?php require_once YQ_BACK . '/app/Views/partials/scripts.php'; ?>
</body>
</html>
