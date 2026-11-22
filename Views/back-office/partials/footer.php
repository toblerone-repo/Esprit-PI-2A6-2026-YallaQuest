<?php
/* Pied de page commun à toutes les pages du back-office.
   À inclure juste avant </body> d'une page de Views/back-office/pages/ :
       <?php include_once __DIR__ . '/../partials/footer.php'; ?>
   Il charge son propre style (Views/css/bo-footer.css), qui ne dépend d'aucune autre feuille,
   et reprend l'apparence de l'en-tête commun (partials/header.php). */
?>
<link rel="stylesheet" href="../../css/bo-footer.css">
<footer class="bo-footer">
    <p>&copy; <?= date('Y') ?> YallaQuest · Espace administration</p>
    <p>Réalisé par Toblerone</p>
</footer>
