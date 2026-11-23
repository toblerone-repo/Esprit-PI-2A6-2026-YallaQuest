<?php
/* Barre de navigation commune à toutes les pages du front-office.
   À inclure juste après <body> d'une page de Views/front-office/pages/ :
       <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
   Elle charge son propre style (Views/css/communaute-navbar.css), qui ne dépend d'aucune autre feuille.
   Le lien actif est trouvé à partir du nom du fichier ouvert : rien à définir avant l'include.
   'fichier.php' => 'Libellé' ; $navbarAlias rattache une sous-page à la rubrique de sa page principale. */
$navbarPages = [
    'quetes-programmation.php' => 'Quêtes',
    'journal.php'              => "Journal d'activité",
    'competences.php'          => 'Compétences et totems',
    'front-objectifs.php'      => 'Objectifs',
    'guildes.php'              => 'Guildes',
    'communaute.php'           => 'Communauté',
    'page-profil.php'          => 'Profils',
];
$navbarAlias = [
    'modifier-profil.php' => 'page-profil.php',
];
$navbarCurrent = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navbarCurrent = $navbarAlias[$navbarCurrent] ?? $navbarCurrent;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isUserConnected = !empty($_SESSION['user_id']) || !empty($_SESSION['utilisateur']);
$isAdminUser = (($_SESSION['role'] ?? ($_SESSION['utilisateur']['role'] ?? '')) === 'admin');
?>
<link rel="stylesheet" href="../../css/communaute-navbar.css">
<header class="yq-community-navbar">
  <div class="community-navbar-inner">
    <div class="community-navbar-brand" aria-label="YallaQuest">
      <img class="community-navbar-logo" src="../../img/communaute-logo.jpg" width="32" height="32" alt="">
      <span>YALLAQUEST</span>
    </div>

    <nav class="community-navbar-navigation" aria-label="Navigation principale">
      <ul class="community-navbar-links">
        <?php foreach ($navbarPages as $navbarFile => $navbarLabel): ?>
        <li><a class="community-navbar-link" href="<?= htmlspecialchars($navbarFile) ?>"<?= $navbarFile === $navbarCurrent ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($navbarLabel) ?></a></li>
        <?php endforeach; ?>
        <?php if ($isAdminUser): ?>
        <li><a class="community-navbar-link community-navbar-admin" href="../../back-office/pages/listuser.php" title="Accéder au panneau d'administration">Back-office</a></li>
        <?php endif; ?>
        <?php if ($isUserConnected): ?>
        <li><a class="community-navbar-link community-navbar-logout" href="logout.php" title="Se déconnecter de votre compte">Déconnexion</a></li>
        <?php else: ?>
        <li><a class="community-navbar-link community-navbar-login" href="login.php">Connexion</a></li>
        <?php endif; ?>
      </ul>
    </nav>

  </div>
</header>
