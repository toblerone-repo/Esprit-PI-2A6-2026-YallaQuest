<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'YallaQuest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/competences-admin.css">
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    <?php include_once __DIR__ . '/../partials/header.php'; ?>
