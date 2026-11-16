<?php
if (!isset($pageTitle)) {
    header('Location: ../../../index.php?page=communaute');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" href="front-office/img/logo.jpeg" type="image/jpeg">
  <link rel="stylesheet" href="front-office/css/common.css">
  <link rel="stylesheet" href="front-office/css/front.css">
</head>
<body class="community-page">
<?php require YQ_FRONT . '/includes/modules-nav.php'; ?>
  <main class="page-shell page-section">
    <h1 class="section-title">La communauté est momentanément indisponible.</h1>
    <p>Les publications n’ont pas pu être chargées. Réessaie dans quelques instants.</p>
    <a href="index.php?page=communaute" class="primary-button">Réessayer</a>
  </main>
</body>
</html>
