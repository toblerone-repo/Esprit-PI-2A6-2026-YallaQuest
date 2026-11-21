<?php
/* Indicateur de chargement Yalla Quest (encre lilas qui se mélange), commun aux pages du front-office.
   À inclure juste après <body> :
       <?php require __DIR__ . '/../includes/spinner.php'; ?>
   - couvre la page jusqu'à l'événement « load », s'efface en 0,4 s puis envoie l'événement « yallaquest:loaded »
     (une page peut l'écouter pour lancer ses animations d'entrée) ;
   - sans JavaScript il n'apparaît pas ; dans tous les cas il disparaît au bout de 8 s ;
   - mouvement réduit : pas de rotation, fondu court.
   Couleurs : variables --ground, --accent et --muted de la page (Views/css/guilde.css ou le <style> de la page ;
   valeurs de repli si elles manquent). */
?>
<div class="yq-loader" id="yq-loader" role="status" aria-live="polite">
  <div class="yq-loader-ink" aria-hidden="true"><i></i><i></i><i></i></div>
  <p class="yq-loader-label">Chargement…</p>
  <svg class="yq-loader-defs" width="0" height="0" aria-hidden="true" focusable="false">
    <filter id="yq-goo"><feGaussianBlur in="SourceGraphic" stdDeviation="5"/><feColorMatrix values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 20 -8"/></filter>
  </svg>
</div>
<noscript><style>.yq-loader { display: none !important; }</style></noscript>
<style>
  .yq-loader { position: fixed; inset: 0; z-index: 100; display: grid; place-content: center; justify-items: center; gap: 20px; background: var(--ground, #000); opacity: 1; transition: opacity 400ms ease, visibility 0s linear 400ms; animation: yq-loader-failsafe 0s linear 8s forwards; }
  .yq-loader.done { opacity: 0; visibility: hidden; pointer-events: none; }
  @keyframes yq-loader-failsafe { to { opacity: 0; visibility: hidden; pointer-events: none; } }
  .yq-loader-ink { position: relative; width: 84px; height: 84px; filter: url(#yq-goo); }
  .yq-loader-ink i { position: absolute; left: 50%; top: 50%; width: 26px; height: 26px; margin: -13px 0 0 -13px; border-radius: 50%; background: var(--accent, #ebc1f2); }
  .yq-loader-ink i:nth-child(1) { width: 36px; height: 36px; margin: -18px 0 0 -18px; animation: yq-loader-pulse 1.4s ease-in-out infinite; }
  .yq-loader-ink i:nth-child(2) { animation: yq-loader-orbit 1.4s cubic-bezier(.5, 0, .5, 1) infinite; }
  .yq-loader-ink i:nth-child(3) { width: 18px; height: 18px; margin: -9px 0 0 -9px; animation: yq-loader-orbit 1.4s cubic-bezier(.5, 0, .5, 1) -0.7s infinite; }
  @keyframes yq-loader-orbit { from { transform: rotate(0deg) translateX(24px); } to { transform: rotate(360deg) translateX(24px); } }
  @keyframes yq-loader-pulse { 50% { transform: scale(.78); } }
  .yq-loader-label { margin: 0; color: var(--muted, #a8a8a8); font-size: 12px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; }
  .yq-loader-defs { position: absolute; }
  @media (prefers-reduced-motion: reduce) {
    .yq-loader-ink i { animation: none !important; }
    .yq-loader-ink i:nth-child(2) { transform: translateX(22px); }
    .yq-loader-ink i:nth-child(3) { transform: translateX(-20px); }
    .yq-loader { transition-duration: 150ms, 0s; transition-delay: 0s, 150ms; }
  }
</style>
<script>
  (function () {
    var el = document.getElementById('yq-loader'), root = document.documentElement, gone = false;
    function hide() {
      if (gone) return;
      gone = true;
      el.classList.add('done');
      root.classList.remove('yq-loading');
      document.dispatchEvent(new CustomEvent('yallaquest:loaded'));
      setTimeout(function () { el.style.display = 'none'; }, 450);
    }
    if (document.readyState === 'complete') { el.style.display = 'none'; return; }
    root.classList.add('yq-loading');
    window.addEventListener('load', hide, { once: true });
    setTimeout(hide, 8000);
  })();
</script>
