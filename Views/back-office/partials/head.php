<?php
/* =====================================================================
   <head> + preloader communs à toutes les pages du back-office
   (Atelier Intégration Template, partie 2, étape 3 : partials/head.php).
   ---------------------------------------------------------------------
   Utilisation, tout en haut d'une page de Views/back-office/pages/ :

       <?php
       $title       = 'Compétences | YallaQuest Admin';   // texte de l'onglet
       $currentPage = 'competences';                       // nom de la page
       require_once __DIR__ . '/../partials/head.php';
       ?>

   Les feuilles de style de chaque page sont écrites plus bas, regroupées
   selon $currentPage. Nouvelle page : ajouter un bloc
       <?php if ($currentPage === 'ma-page'): ?> ...<link>... <?php endif; ?>
   Ce fichier affiche ensuite la barre latérale et l'en-tête communs :
   la page n'a plus qu'à écrire son contenu, puis footer.php, </body></html>.
   ===================================================================== */
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $title ?? 'YallaQuest') ?></title>

    <!-- ========== All CSS files linkup ========= -->
    <?php if ($currentPage === 'competences' || $currentPage === 'totems'): ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/competences-admin.css">
    <?php endif; ?>

    <?php if ($currentPage === 'activites'): ?>
    <meta name="description" content="Gestion des activités de chaque quête sur YallaQuest.">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/journal-admin.css">
    <?php endif; ?>

    <?php if ($currentPage === 'types-activite'): ?>
    <meta name="description" content="Gestion des types d'activité de YallaQuest.">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/journal-admin.css">
    <?php endif; ?>

    <?php if ($currentPage === 'back-objectifs'): ?>
    <link rel="stylesheet" href="../../css/back-objectifs-styles.css">
    <?php endif; ?>

    <?php if ($currentPage === 'guildes'): ?>
    <link rel="stylesheet" href="../../css/guilde.css">
    <?php endif; ?>

    <?php if ($currentPage === 'utilisateurs'): ?>
    <!-- [DARINE · LISTE] page listuser.php -->
    <meta name="description" content="Liste des aventuriers de Yalla Quest (administration).">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/utilisateurs-admin.css">
    <?php endif; ?>

    <?php if ($currentPage === 'quetes'): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=DM+Serif+Display:ital@0;1&family=Dancing+Script:wght@400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&family=Manrope:wght@200..800&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Patrick+Hand&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sacramento&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/quetes.css">
    <?php endif; ?>

    <?php if ($currentPage === 'publications' || $currentPage === 'signalements'): ?>
    <link rel="stylesheet" href="../../css/communaute-base.css">
    <link rel="stylesheet" href="../../css/communaute-admin.css">
    <?php endif; ?>
  </head>

  <?php if ($currentPage === 'publications' || $currentPage === 'signalements'): ?>
  <body class="yq-community-page">
  <?php else: ?>
  <body>
  <?php endif; ?>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    <?php include_once __DIR__ . '/../partials/header.php'; ?>
