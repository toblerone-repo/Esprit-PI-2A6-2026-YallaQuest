<?php
/* Barre latérale commune à toutes les pages du back-office.
   À inclure juste après <body> d'une page de Views/back-office/pages/ :
       <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
   Elle charge son propre style (Views/css/bo-sidebar.css) et décale la page vers la droite.
   Le lien actif est trouvé à partir du nom du fichier ouvert : rien à définir avant l'include.
   Pour ajouter une page : une ligne dans $boSidebarPages, 'fichier.php' => 'Libellé'. */
$boSidebarPages = [
    'listuser.php'       => 'Aventuriers', // [DARINE · LISTE UTILISATEURS]
    'quetes-listes.php'  => 'Quêtes',
    'activites.php'      => 'Activités',
    'types-activite.php' => "Types d'activité",
    'competences.php'    => 'Compétences',
    'totems.php'         => 'Totems',
    'guilde_admin.php'   => 'Guildes',
    'back-objectifs.php' => 'Objectifs',
    'publications.php'   => 'Publications',
    'signalements.php'   => 'Signalements',
];
$boSidebarCurrent = basename($_SERVER['SCRIPT_NAME'] ?? '');
$boCssVer = file_exists(__DIR__ . '/../../css/bo-sidebar.css') ? filemtime(__DIR__ . '/../../css/bo-sidebar.css') : time();
?>
<link rel="stylesheet" href="../../css/bo-sidebar.css?v=<?= $boCssVer ?>">
<style>
/* Style de secours immédiat (anti-cache navigateur) */
.bo-sidebar .bo-sidebar-footer,
.bo-sidebar .bo-sidebar-bottom {
  margin-top: auto !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 8px !important;
  padding-top: 16px !important;
  border-top: 1px solid #2a2a33 !important;
}
.bo-sidebar .bo-sidebar-front,
.bo-sidebar .bo-sidebar-logout {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 8px !important;
  padding: 8px 12px !important;
  border-radius: 999px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  text-align: center !important;
  line-height: 1.4 !important;
  box-sizing: border-box !important;
}
.bo-sidebar .bo-sidebar-front {
  border: 1px solid #2a2a33 !important;
  background: #16161b !important;
  color: #f1f0f5 !important;
}
.bo-sidebar .bo-sidebar-front:hover {
  border-color: #cbb5f4 !important;
  background: #1d1d24 !important;
  color: #cbb5f4 !important;
}
.bo-sidebar .bo-sidebar-logout {
  border: 1px solid rgba(239, 68, 68, 0.4) !important;
  background: rgba(239, 68, 68, 0.1) !important;
  color: #fca5a5 !important;
}
.bo-sidebar .bo-sidebar-logout:hover {
  border-color: #ef4444 !important;
  background: rgba(239, 68, 68, 0.22) !important;
  color: #ffffff !important;
}
</style>
<nav class="bo-sidebar" aria-label="Back-office">
    <div class="bo-sidebar-brand">
        <img src="../../img/logo.jpeg" alt="" width="32" height="32">
        <span>
            <span class="bo-sidebar-name">Yalla Quest</span>
            <span class="bo-sidebar-tag">Back-office</span>
        </span>
    </div>

    <div class="bo-sidebar-links">
        <?php foreach ($boSidebarPages as $boFile => $boLabel): ?>
        <?php $boIsActive = ($boFile === $boSidebarCurrent) || ($boFile === 'listuser.php' && in_array($boSidebarCurrent, ['addUser.php', 'adduser.php', 'changeRole.php', 'changerole.php'], true)); ?>
        <a class="bo-sidebar-link" href="<?= htmlspecialchars($boFile) ?>"<?= $boIsActive ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($boLabel) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="bo-sidebar-footer">
        <a class="bo-sidebar-front" href="../../front-office/pages/communaute.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Voir le site</span>
        </a>
        <a class="bo-sidebar-logout" href="logout.php" title="Se déconnecter de la session">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Déconnexion</span>
        </a>
    </div>
</nav>
