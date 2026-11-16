<?php
// Spinner de chargement (atelier « Intégration Template », étape 4) : placé juste après <body>, avant la navigation,
// sur toutes les pages. Même principe que le template : #spinner porte la classe .show au chargement,
// puis on la retire dès que la page est prête (fondu de 0,5 s). Styles dans shared/css/guildes.css.
// Sans JavaScript, le CSS le retire seul au bout de 4 s : la page ne reste jamais bloquée.
?>
<div id="spinner" class="show" role="status" aria-live="polite">
  <div class="spinner-ring" aria-hidden="true"></div>
  <span class="sr">Chargement…</span>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
      var s = document.getElementById('spinner');
      if (s) s.classList.remove('show');
    }, 1);
  });
</script>
