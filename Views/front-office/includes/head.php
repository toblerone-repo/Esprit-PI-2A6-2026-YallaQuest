<?php
/* =====================================================================
   Contenu du <head> commun à toutes les pages du front-office
   (Atelier Intégration Template, partie 1, étape 3 : includes/head.php).
   Comme dans l'atelier, les balises <head> et </head> restent dans la page.
   ---------------------------------------------------------------------
   Utilisation dans une page de Views/front-office/pages/ :

       <head>
         <?php
           $pageTitle   = 'Mes compétences · YallaQuest';  // texte de l'onglet
           $currentPage = 'competences';                   // nom de la page
           require_once __DIR__ . '/../includes/head.php';
         ?>
       </head>

   Les feuilles de style de chaque page sont écrites plus bas, regroupées
   selon $currentPage. Nouvelle page : ajouter un bloc
       <?php if ($currentPage === 'ma-page'): ?> ...<link>... <?php endif; ?>
   ===================================================================== */
$currentPage = $currentPage ?? '';
?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($pageTitle ?? 'YallaQuest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Template Stylesheet -->
<?php if ($currentPage === 'competences'): ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display&display=swap">
<link rel="stylesheet" href="../../css/competences.css">
<?php endif; ?>

<?php if ($currentPage === 'front-objectifs' || $currentPage === 'join_now' || $currentPage === 'login'): ?>
<link rel="stylesheet" href="../../css/front-objectifs-styles.css">
<?php endif; ?>

<?php if ($currentPage === 'modifier-profil'): ?>
<link rel="stylesheet" href="../../css/profil.css">
<?php endif; ?>

<?php if ($currentPage === 'page-profil'): ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../css/profil.css">
<?php endif; ?>

<?php if ($currentPage === 'guildes'): ?>
<!-- Feuille unique du projet : styles de la page (partie « page Guildes »), règles communes et effets. -->
<link rel="stylesheet" href="../../css/guilde.css">
<?php endif; ?>

<?php if ($currentPage === 'journal'): ?>
<meta name="description" content="Journal d'activité YallaQuest : suis la progression de tes quêtes.">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/journal.css">
<?php endif; ?>

<?php if ($currentPage === 'quetes-programmation'): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=DM+Serif+Display:ital@0;1&family=Dancing+Script:wght@400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&family=Manrope:wght@200..800&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Patrick+Hand&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sacramento&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.2.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="../../css/quetes.css">
<?php endif; ?>

<?php if ($currentPage === 'communaute'): ?>
<link rel="stylesheet" href="../../css/communaute-base.css">
<link rel="stylesheet" href="../../css/communaute.css">
<?php endif; ?>
