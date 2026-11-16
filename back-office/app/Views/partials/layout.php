<?php
if (!isset($title, $module)) {
    header('Location: ../../../../index.php?admin=publications');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — Administration YallaQuest</title>
  <link rel="icon" href="front-office/img/logo.jpeg" type="image/jpeg">
  <link rel="stylesheet" href="front-office/css/common.css">
  <link rel="stylesheet" href="back-office/src/css/admin.css">
</head>
<body class="admin-page">
  <?php require __DIR__ . '/admin-nav.php'; ?>
  <a href="#main-content" class="skip-link">Aller au contenu</a>
  <aside class="admin-navigation">
    <a href="index.php?admin=tableau-de-bord" class="admin-brand"><img src="front-office/img/logo.jpeg" alt="" width="42" height="42">YallaQuest</a>
    <p class="navigation-caption">Administration</p>
    <nav aria-label="Administration">
      <a href="index.php?admin=publications&amp;action=tableau-de-bord" <?php if ($activePage == 'dashboard') { ?>aria-current="page"<?php } ?>>Tableau de bord</a>
      <a href="index.php?admin=publications" <?php if ($activePage == 'publications') { ?>aria-current="page"<?php } ?>>Publications</a>
      <a href="index.php?admin=signalements" <?php if ($activePage == 'signalements') { ?>aria-current="page"<?php } ?>>Signalements</a>
    </nav>
    <a href="index.php?page=communaute" class="community-link">Voir la communauté ↗</a>
  </aside>
  <div class="admin-content">
    <header class="admin-header"><span>Espace administration</span><span>YallaQuest</span></header>
    <main id="main-content">
      <?php if ($flash) { ?>
        <p role="status" class="feedback <?php if ($flash['type'] == 'error') { ?>feedback-error<?php } ?>"><?= htmlspecialchars($flash['message']) ?></p>
      <?php } ?>
      <?php if ($pageError != '') { ?>
        <section class="panel empty-state">
          <h1><?= htmlspecialchars($title) ?>.</h1>
          <p><?= htmlspecialchars($pageError) ?></p>
          <a href="index.php?admin=<?= $module == 'signalements' ? 'signalements' : 'publications' ?>" class="button">Revenir à la liste</a>
        </section>
      <?php } elseif ($module == 'publications') {
          require __DIR__ . '/../pages/publications.php';
      } elseif ($module == 'signalements') {
          require __DIR__ . '/../pages/signalements.php';
      } ?>
    </main>
    <footer class="admin-footer">© <?= date('Y') ?> YallaQuest · Chaque habitude est une quête.</footer>
  </div>
</body>
</html>
