<?php
/* Barre latérale commune à toutes les pages du back-office.
   À inclure juste après <body> d'une page de Views/back-office/pages/ :
       <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
   Elle charge son propre style (Views/css/bo-sidebar.css) et décale la page vers la droite.
   Le lien actif est trouvé à partir du nom du fichier ouvert : rien à définir avant l'include.
   Pour ajouter une page : une ligne dans $boSidebarPages, 'fichier.php' => 'Libellé'. */
$boSidebarPages = [
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
        <a class="bo-sidebar-link" href="<?= htmlspecialchars($boFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"<?= $boFile === $boSidebarCurrent ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($boLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </div>

    <a class="bo-sidebar-front" href="../../front-office/pages/communaute.php">← Voir le site</a>
</nav>
