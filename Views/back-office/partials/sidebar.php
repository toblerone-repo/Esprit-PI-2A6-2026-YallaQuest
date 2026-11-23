<?php
// Darine : structure commune. Amine : gabarit du routeur. Houssem : sorties échappées.
/* Barre latérale commune à toutes les pages du back-office.
   À inclure juste après <body> d'une page de Views/back-office/pages/ :
       <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
   Elle charge son propre style (Views/css/bo-sidebar.css) et décale la page vers la droite.
   Le lien actif est trouvé à partir du nom du fichier ouvert : rien à définir avant l'include.
   Pour ajouter une page : une ligne dans $boSidebarPages, 'fichier.php' => 'Libellé'. */
$boSidebarPages = [
    'listuser.php'       => 'Aventuriers', // [DARINE · LISTE UTILISATEURS]
    'liste_utilisateurs.php' => 'Suppression des comptes',
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
?>
<link rel="stylesheet" href="../../css/bo-sidebar.css">
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
        <?php // Mohamed : addUser.php et changeRole.php restent rattachées à « Aventuriers ».
        $boIsActive = ($boFile === $boSidebarCurrent) || ($boFile === 'listuser.php' && in_array($boSidebarCurrent, ['addUser.php', 'changeRole.php'], true)); ?>
        <a class="bo-sidebar-link" href="<?= htmlspecialchars($boFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"<?= $boIsActive ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($boLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Mohamed : retour au site et déconnexion (POST + jeton CSRF de Houssem). -->
    <div class="bo-sidebar-footer">
        <a class="bo-sidebar-front" href="../../front-office/pages/communaute.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Voir le site</span>
        </a>
        <form method="post" action="logout.php" class="bo-sidebar-logout-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthController::getCsrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <button type="submit" class="bo-sidebar-logout" title="Se déconnecter de la session">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
</nav>
