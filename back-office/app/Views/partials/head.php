<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'YallaQuest') ?></title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="back-office/src/css/competences-admin.css">
  </head>
  <body>
    <?php require YQ_BACK . '/app/Views/partials/admin-nav.php'; ?>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->
