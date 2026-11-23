/* Yalla Quest · indicateur de chargement (script de Views/front-office/includes/spinner.php).
   Chargé juste après le balisage de l'indicateur : il l'affiche (classe yq-loading sur <html>), puis au « load »
   le fait disparaître en 0,4 s et envoie l'événement « yallaquest:loaded » (les effets l'attendent).
   Sans JavaScript l'indicateur reste masqué ; dans tous les cas il disparaît au bout de 8 s.
   Styles : Views/css/guilde.css, partie « indicateur de chargement ». */
(function () {
  var el = document.getElementById('yq-loader'), root = document.documentElement, gone = false;
  if (!el) return;
  function hide() {
    if (gone) return;
    gone = true;
    el.classList.add('done');
    root.classList.remove('yq-loading');
    document.dispatchEvent(new CustomEvent('yallaquest:loaded'));
    setTimeout(function () { el.hidden = true; }, 450);
  }
  if (document.readyState === 'complete') { el.hidden = true; return; }
  root.classList.add('yq-loading');
  window.addEventListener('load', hide, { once: true });
  setTimeout(hide, 8000);
})();
