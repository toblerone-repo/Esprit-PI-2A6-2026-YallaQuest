<?php
// Back-Office · gestion des guildes (CRUD par l'administrateur).
// Interface seulement : Model/GuildeModel.js est un échantillon en mémoire, Controller/GuildeController.js agit dessus.
// À brancher plus tard sur le contrôleur PHP et la base (voir README-effects.md, « Administration »).
$pageTitle = 'Administration · Guildes · Yalla Quest';
$currentPage = 'admin-guildes';
$pageScript = 'admin-guildes.js';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once __DIR__ . '/includes/head.php'; ?>
</head>
<body class="admin" data-base="<?= htmlspecialchars($shared) ?>">
<?php require_once __DIR__ . '/includes/spinner.php'; ?>
<div class="app">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <main class="canvas" id="canvas" tabindex="-1"></main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php require_once __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
