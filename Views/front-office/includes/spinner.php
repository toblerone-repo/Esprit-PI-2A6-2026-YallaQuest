<?php
/* Indicateur de chargement Yalla Quest (encre lilas qui se mélange), commun aux pages du front-office.
   À inclure juste après <body> d'une page de Views/front-office/pages/ :
       <?php require __DIR__ . '/../includes/spinner.php'; ?>
   Ce fichier ne contient que le balisage. Styles : Views/css/guilde.css (partie « indicateur de chargement »,
   la page doit charger guilde.css). Script : Views/js/spinner.js.
   - couvre la page jusqu'à l'événement « load », s'efface en 0,4 s puis envoie « yallaquest:loaded » ;
   - sans JavaScript il n'apparaît pas ; dans tous les cas il disparaît au bout de 8 s ;
   - mouvement réduit : pas de rotation, fondu court. */
?>
<div class="yq-loader" id="yq-loader" role="status" aria-live="polite">
  <div class="yq-loader-ink" aria-hidden="true"><i></i><i></i><i></i></div>
  <p class="yq-loader-label">Chargement…</p>
  <svg class="yq-loader-defs" width="0" height="0" aria-hidden="true" focusable="false">
    <filter id="yq-goo"><feGaussianBlur in="SourceGraphic" stdDeviation="5"/><feColorMatrix values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 20 -8"/></filter>
  </svg>
</div>
<script src="../../js/spinner.js"></script>
