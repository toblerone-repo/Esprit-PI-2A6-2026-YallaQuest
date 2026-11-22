<?php
/* Pied de page commun à toutes les pages du front-office.
   À inclure juste avant </body> d'une page de Views/front-office/pages/ :
       <?php include_once __DIR__ . '/../includes/footer.php'; ?>
   Il charge son propre style (Views/css/front-footer.css), qui ne dépend d'aucune autre feuille,
   et reprend les couleurs de la barre de navigation (includes/navbar.php). */
?>
<link rel="stylesheet" href="../../css/front-footer.css">
<footer class="yq-front-footer">
  <div class="yq-front-footer-inner">
    <div class="yq-front-footer-brand">
      <img class="yq-front-footer-logo" src="../../img/communaute-logo.jpg" width="28" height="28" alt="">
      <span class="yq-front-footer-name">YALLAQUEST</span>
      <span class="yq-front-footer-motto">Chaque habitude est une quête.</span>
    </div>

    <address class="yq-front-footer-contact">
      <span>Ariana, Tunisie</span>
      <a href="mailto:toblerone@gmail.com">toblerone@gmail.com</a>
    </address>

    <p class="yq-front-footer-copy">&copy; <?= htmlspecialchars((string) (date('Y')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> YallaQuest · Réalisé par Toblerone</p>
  </div>
</footer>
