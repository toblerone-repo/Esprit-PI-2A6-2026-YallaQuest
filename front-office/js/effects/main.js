// Yalla Quest · Guildes — effets de défilement et de transition. Point d'entrée (module ES, sans build).
// Bibliothèques : GSAP + ScrollTrigger, Lenis, SplitType (scripts classiques -> globales).
// Les effets qui suivaient la souris (encre WebGL sous le curseur, emblème qui s'incline vers le curseur,
// cartes inclinées, boutons magnétiques) ont été retirés, avec Three.js qui ne servait qu'à eux.
// Le template n'est pas réécrit : on cible ses classes via SEL et on suit ses re-rendus via l'événement
// `yallaquest:render` émis à la fin de render() (repli : MutationObserver sur #canvas).
import * as Transitions from './Transitions.js';
import * as Entrances from './Entrances.js';
import * as Micro from './Micro.js';

/* ---------- sélecteurs du template (à adapter ici si une classe change) ---------- */
export const SEL = {
  canvas: '#canvas', sidebar: '#sidebar', dialog: '#dialog', toast: '.toast',
  navCurrent: '.nav-item[aria-current="page"]',
  titleRow: '.title-row', title: '.title-row h1', kicker: '.title-row .kicker', about: '.title-row .about', sub: '.title-row .sub',
  actions: '.title-row .actions', emblem: '.emblem',
  tabs: '.tabs', tabSelected: 'a[role="tab"][aria-selected="true"]',
  proposal: '.proposal', art: '.proposal .art', voteSeg: '.votebar i', legendItem: '.votelegend span',
  choiceBtn: '.choice button',
  railPanel: '.rail .panel', capFill: '.capbar i',
  tableRow: '.sheet table tbody tr', cellImg: '.gcell img',
  pagefoot: '.pagefoot',
};

/* ---------- réglages (voir le tableau du README) ---------- */
export const TUNE = {
  lenis: { lerp: 0.1 },
  wipe: { duration: 0.7, entranceDelay: 0.3, wave: 26, dripLength: 110 },
  entrance: { titleDuration: 0.85, titleStagger: 0.028, textStagger: 0.08, rowStagger: 0.05, cardDuration: 0.95, cardStagger: 0.12, panelStagger: 0.1, barDuration: 0.9, voteDuration: 0.6 },
  tabs: { duration: 0.5 },
  splash: { duration: 0.55, drops: 5 },
};

const html = document.documentElement;
const mq = {
  reduced: window.matchMedia('(prefers-reduced-motion: reduce)'),
};
const env = { reduced: false };
const readEnv = () => { env.reduced = mq.reduced.matches; };

const ctx = { SEL, TUNE, env, gsap: null, ScrollTrigger: null, SplitType: null, root: null, route: null, sameRoute: false, delay: 0, velocity: () => 0 };
let lenis = null;
let lenisTick = null;
let liveNodes = [];        // enfants actuels de #canvas : au prochain rendu, ce sont les « anciens » nœuds
let lastRoute = null;
let lastRouteAt = 0;
let wipeAt = 0;
let scrollY = 0;
let hookSeen = false;

/** Vitesse de défilement (px / image, signée). 0 sans Lenis. */
export const getScrollVelocity = () => (lenis ? lenis.velocity : 0);

/* ---------- 1. défilement fluide : Lenis synchronisé avec gsap.ticker et ScrollTrigger ---------- */
function setupLenis() {
  if (lenis || env.reduced || !window.Lenis) return;
  const { gsap, ScrollTrigger } = ctx;
  lenis = new window.Lenis({ lerp: TUNE.lenis.lerp, autoRaf: false });
  if (ScrollTrigger) lenis.on('scroll', ScrollTrigger.update);
  lenisTick = (time) => lenis.raf(time * 1000);
  gsap.ticker.add(lenisTick);
  gsap.ticker.lagSmoothing(0);
  ctx.velocity = getScrollVelocity;
  const dialog = document.querySelector(SEL.dialog);
  if (dialog && dialog.open) lenis.stop();
}
function teardownLenis() {
  if (!lenis) return;
  ctx.gsap.ticker.remove(lenisTick);
  lenis.destroy(); lenis = null; lenisTick = null;
  ctx.velocity = () => 0;
}
function scrollTop() {
  // remplace le window.scrollTo(0, 0) du template (qui reste sans effet derrière)
  if (lenis) lenis.scrollTo(0, { immediate: true, force: true });
  else window.scrollTo(0, 0);
}

/* ---------- dialogue : Lenis en pause, page verrouillée ---------- */
function setupDialog() {
  const dialog = document.querySelector(SEL.dialog);
  document.querySelector(SEL.sidebar)?.setAttribute('data-lenis-prevent', ''); // le menu défile seul
  if (!dialog) return;
  dialog.setAttribute('data-lenis-prevent', '');
  const sync = () => {
    const open = dialog.open;
    html.classList.toggle('fx-locked', open);
    if (lenis) { if (open) lenis.stop(); else lenis.start(); }
  };
  new MutationObserver(sync).observe(dialog, { attributes: true, attributeFilter: ['open'] });
  dialog.addEventListener('close', sync);
  sync();
}

/* ---------- 2. cycle de re-rendu ---------- */
function currentRoute(detail) {
  // la route réellement affichée (un hash vide ou inconnu retombe sur la guilde du membre)
  return document.querySelector(SEL.navCurrent)?.dataset.route || detail?.route || location.hash.slice(1) || 'guildes';
}

function onRender(detail) {
  const root = ctx.root, now = performance.now();
  const oldNodes = liveNodes;
  liveNodes = [...root.childNodes];
  const route = currentRoute(detail);
  const first = lastRoute === null;
  const changed = route !== lastRoute;
  // `location.hash = x; render()` puis hashchange -> render() : le 2e rendu prolonge le changement de route
  const echo = !changed && now - lastRouteAt < 300;
  const previousScroll = scrollY;
  lastRoute = route; ctx.route = route;
  if (changed) lastRouteAt = now;

  if (env.reduced) {
    // mouvement réduit : ni Lenis, ni encre, ni rideau. Un fondu d'opacité de 150 ms, c'est tout.
    Entrances.destroy();
    if (changed) { scrollTop(); if (!first) Transitions.fade(root, ctx); }
    return;
  }

  if (changed || echo) {
    // --- changement de route : rideau d'encre + entrées ---
    if (changed) {
      scrollTop();
      Entrances.destroy(); // remet l'ancienne page dans son état final avant de la passer au rideau
      Micro.destroy();
      if (!first && Transitions.play(root, ctx, oldNodes, previousScroll)) wipeAt = now;
    }
    ctx.sameRoute = false;
    ctx.delay = first ? 0.05 : Math.max(0, (wipeAt + TUNE.wipe.entranceDelay * 1000 - now) / 1000);
    Entrances.init(root, ctx);
    Micro.init(root, ctx);
  } else {
    // --- même route (vote, onglet, adhésion) : pas de rejeu, juste un retour local ---
    const prev = Entrances.snapshot(oldNodes, SEL); // largeurs des barres AVANT le re-rendu
    ctx.sameRoute = true;
    ctx.delay = 0;
    Entrances.update(root, ctx, prev);
    Micro.init(root, ctx);
  }
}

function listen() {
  document.addEventListener('yallaquest:render', (e) => { hookSeen = true; safe(() => onRender(e.detail)); });
  // repli si la ligne ajoutée dans render() est absente : on observe le remplacement du contenu de #canvas
  new MutationObserver(() => {
    if (hookSeen) { hookSeen = false; return; }
    safe(() => onRender({ route: location.hash.slice(1) || 'guildes' }));
  }).observe(ctx.root, { childList: true });
  window.addEventListener('scroll', () => { scrollY = window.scrollY; }, { passive: true });
  // la mise en page change (votants dépliés, redimensionnement) : ScrollTrigger recalcule
  if (window.ResizeObserver && ctx.ScrollTrigger) {
    let t = 0;
    new ResizeObserver(() => { clearTimeout(t); t = setTimeout(() => ctx.ScrollTrigger.refresh(), 200); }).observe(ctx.root);
  }
  const reconfigure = () => safe(() => {
    // préférence de mouvement modifiée en cours de route : on repart proprement, sans rejouer les entrées
    Entrances.destroy(); Transitions.kill(); Micro.dispose();
    readEnv();
    if (env.reduced) teardownLenis(); else setupLenis();
    if (env.reduced) return;
    ctx.sameRoute = true; ctx.delay = 0;
    Micro.init(ctx.root, ctx);
  });
  for (const m of Object.values(mq)) m.addEventListener ? m.addEventListener('change', reconfigure) : m.addListener(reconfigure);
}

/* une erreur dans un effet ne doit jamais casser la page : on coupe les effets et on rend la main */
function safe(fn) {
  try { fn(); } catch (err) {
    console.warn('[Yalla Quest FX] effets désactivés :', err);
    try { Entrances.destroy(); Transitions.kill(); Micro.dispose(); } catch (e) { /* rien */ }
    html.classList.remove('fx-boot');
  }
}

function boot() {
  const gsap = window.gsap;
  ctx.root = document.querySelector(SEL.canvas);
  if (!gsap || !ctx.root) { html.classList.remove('fx-boot'); return; } // sans GSAP : la page d'origine, intacte
  ctx.gsap = gsap;
  ctx.ScrollTrigger = window.ScrollTrigger || null;
  ctx.SplitType = window.SplitType || null;
  if (ctx.ScrollTrigger) gsap.registerPlugin(ctx.ScrollTrigger);
  readEnv();

  html.classList.add('fx-on');
  setupLenis();
  setupDialog();
  listen();
  scrollY = window.scrollY;
  window.YallaQuestFX = { SEL, TUNE, env, get lenis() { return lenis; }, get scrollVelocity() { return getScrollVelocity(); } };
  safe(() => onRender({ route: location.hash.slice(1) || 'guildes' })); // premier rendu : déjà fait par le template
  html.classList.remove('fx-boot');
}

boot();
