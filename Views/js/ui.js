/**
 * Yalla Quest · interface commune (toutes les pages) : menu latéral, fenêtres <dialog>, confirmation, toast.
 * Remplace Bootstrap (modals) et les alert() / confirm() du navigateur. Aucune dépendance.
 * API : YQ.openDialog(dialog, déclencheur), YQ.closeDialog(dialog), YQ.confirm({...}) -> Promise<boolean>,
 *       YQ.toast(message), YQ.go(url) (navigation, avec la transition si les effets sont chargés).
 */
(function () {
  const $ = (s, el) => (el || document).querySelector(s);
  const $$ = (s, el) => Array.from((el || document).querySelectorAll(s));
  const YQ = window.YQ = window.YQ || {};

  /* ---------- toast ---------- */
  let toastTimer;
  YQ.toast = function (msg) {
    let t = $('#toast');
    if (!t) { t = document.createElement('div'); t.id = 'toast'; t.className = 'toast'; t.setAttribute('role', 'status'); t.setAttribute('aria-live', 'polite'); document.body.appendChild(t); }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
  };

  /* ---------- fenêtres ---------- */
  // Le focus revient au bouton qui a ouvert la fenêtre, de façon synchrone (sans attendre l'événement close).
  const triggers = new WeakMap();
  YQ.openDialog = function (dlg, trigger) {
    if (!dlg || dlg.open) return;
    triggers.set(dlg, trigger || document.activeElement);
    dlg.showModal();
    const first = $('[autofocus], input:not([type=hidden]), select, textarea, button', dlg);
    if (first) first.focus();
  };
  function restore(dlg) {
    const t = triggers.get(dlg);
    triggers.delete(dlg);
    if (t && t.isConnected && typeof t.focus === 'function') t.focus();
  }
  YQ.closeDialog = function (dlg) {
    if (!dlg || !dlg.open) return;
    dlg.close();
    restore(dlg);
  };
  // fermeture par Échap
  document.addEventListener('close', (e) => { if (e.target.tagName === 'DIALOG' && triggers.has(e.target)) restore(e.target); }, true);
  // [data-open="#id"] ouvre une fenêtre, [data-close] la ferme
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open]');
    if (opener) { e.preventDefault(); YQ.openDialog($(opener.dataset.open), opener); return; }
    const closer = e.target.closest('[data-close]');
    if (closer) { e.preventDefault(); YQ.closeDialog(closer.closest('dialog')); }
  });

  /* ---------- confirmation (remplace confirm()) ---------- */
  YQ.confirm = function (opts) {
    const o = Object.assign({ title: 'Confirmer ?', body: '', confirmLabel: 'Confirmer', cancelLabel: 'Annuler', danger: true, trigger: null }, opts);
    return new Promise((resolve) => {
      let dlg = $('#yq-confirm');
      if (!dlg) { dlg = document.createElement('dialog'); dlg.id = 'yq-confirm'; dlg.setAttribute('aria-labelledby', 'yq-confirm-title'); document.body.appendChild(dlg); }
      dlg.innerHTML = '<form><h2 id="yq-confirm-title"></h2><p class="lead"></p><div class="foot">'
        + '<button class="btn" type="button" data-answer="no"></button><button class="btn primary' + (o.danger ? ' destructive' : '') + '" type="submit"></button></div></form>';
      $('h2', dlg).textContent = o.title;
      $('.lead', dlg).textContent = o.body;
      $('[data-answer="no"]', dlg).textContent = o.cancelLabel;
      $('[type="submit"]', dlg).textContent = o.confirmLabel;
      let done = false;
      const finish = (v) => { if (done) return; done = true; YQ.closeDialog(dlg); resolve(v); };
      $('form', dlg).addEventListener('submit', (e) => { e.preventDefault(); finish(true); });
      $('[data-answer="no"]', dlg).addEventListener('click', () => finish(false));
      dlg.addEventListener('cancel', () => finish(false), { once: true });
      YQ.openDialog(dlg, o.trigger);
      $('[data-answer="no"]', dlg).focus(); // le choix sûr reçoit le focus
    });
  };

  /* ---------- navigation (les effets peuvent jouer une transition avant) ---------- */
  YQ.go = function (url) {
    if (typeof YQ.beforeNavigate === 'function') YQ.beforeNavigate(url);
    else window.location.href = url;
  };

  /* ---------- menu latéral ---------- */
  function initSidebar() {
    const app = $('.app'), sb = $('#sidebar');
    if (!app || !sb) return;
    // téléphone : feuille plein écran
    const tg = $('.menu-toggle');
    const menu = (open) => { sb.classList.toggle('open', open); if (tg) tg.setAttribute('aria-expanded', String(open)); };
    if (tg) tg.addEventListener('click', () => menu(true));
    $$('.close-menu', sb).forEach((b) => b.addEventListener('click', () => menu(false)));
    sb.addEventListener('click', (e) => { if (e.target.closest('a.nav-item')) menu(false); });
    // écran large : bouton en haut à gauche, état retenu
    const btn = $('.sidebar-toggle');
    if (!btn) return;
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
      app.classList.add('sidebar-instant');
      set(true);
      requestAnimationFrame(() => requestAnimationFrame(() => app.classList.remove('sidebar-instant')));
    }
    btn.addEventListener('click', () => {
      const hidden = !app.classList.contains('sidebar-hidden');
      set(hidden);
      try { localStorage.setItem(KEY, hidden ? '1' : '0'); } catch (e) { /* stockage indisponible */ }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSidebar);
  else initSidebar();
})();
