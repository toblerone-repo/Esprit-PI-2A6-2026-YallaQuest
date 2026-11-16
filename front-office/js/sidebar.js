// Bouton en haut à gauche : masque / affiche le menu latéral sur écran large. L'état est retenu d'une visite à l'autre.
(() => {
  const btn = document.querySelector('.sidebar-toggle');
  const app = document.querySelector('.app');
  if (!btn || !app) return;
  const KEY = 'yq-sidebar-hidden';
  const set = (hidden) => {
    app.classList.toggle('sidebar-hidden', hidden);
    const label = hidden ? 'Afficher le menu latéral' : 'Masquer le menu latéral';
    btn.setAttribute('aria-expanded', String(!hidden));
    btn.setAttribute('aria-label', label);
    btn.title = label;
  };
  let saved = false;
  try { saved = localStorage.getItem(KEY) === '1'; } catch (e) { /* stockage indisponible */ }
  if (saved) {
    app.classList.add('sidebar-instant'); // état retenu : pas d'animation au chargement
    set(true);
    requestAnimationFrame(() => requestAnimationFrame(() => app.classList.remove('sidebar-instant')));
  }
  btn.addEventListener('click', () => {
    const hidden = !app.classList.contains('sidebar-hidden');
    set(hidden);
    try { localStorage.setItem(KEY, hidden ? '1' : '0'); } catch (e) { /* stockage indisponible */ }
  });
})();
