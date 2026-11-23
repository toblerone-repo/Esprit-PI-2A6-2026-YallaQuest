// Yalla Quest · Guildes — effets de scroll et de souris (point d'entrée, module ES, sans build).
// Bibliothèques : GSAP 3 + ScrollTrigger, Lenis, SplitType (scripts classiques -> globales) et Three.js
// (chargé à la demande par l'importmap « three », CDN en secours).
//
// Ce fichier : détection des capacités, sélecteurs, réglages, Lenis + GSAP (une seule boucle : gsap.ticker),
// et le cycle de vie des vues : la page est statique, une <section class="yq-view"> par adresse (#guildes, #g1…,
// #quetes) ; à chaque changement d'adresse les effets se détachent de l'ancienne vue et s'attachent à la nouvelle.
// La page marche exactement pareil sans ce fichier : aucune ligne du template n'en dépend.

import * as Entrances from './Entrances.js';
import * as Transitions from './Transitions.js';
import * as Micro from './Micro.js';
import * as InkField from './InkField.js';
import * as FloatingEmblem from './FloatingEmblem.js';

/* ---------- sélecteurs : les classes du template (à adapter ici seulement si une classe change) ---------- */
export const SEL = {
  canvas: '#canvas',
  nav: '#guildnav',
  dialogs: 'dialog',
  toast: '#toast',
  navCurrent: '.nav-item[data-route][aria-current="page"]',
  view: '.yq-view',
  defaultView: '.yq-default',
  titleRow: '.title-row',
  title: '.title-row h1',
  titleText: '.title-row .kicker, .title-row .about, .title-row .sub',
  actions: '.title-row .actions',
  emblem: '.title-row .emblem',
  tabs: '.tabs[role="tablist"]',
  tab: 'a[role="tab"]',
  proposal: '.proposal',
  art: '.art',
  voteSeg: '.votebar i',
  legend: '.votelegend > span',
  choice: '.choice button',
  noTilt: '.choice, details.voters',
  rail: '.rail > section',
  capbar: '.capbar i',
  rows: '.sheet table tbody tr',
  rowImg: '.gcell img',
  magnetic: '.btn.primary, .chip',
  blocks: '.sheet, .stack > .empty-state, .stack > .tabs, .yq-view > .tabs',
};

/* ---------- réglages (voir « Effets visuels des guildes » dans le README du projet) ---------- */
export const TUNE = {
  lenis: { lerp: 0.1 },
  echoWindow: 700,          // ms : un re-rendu sur la même route juste après un changement de route en fait partie
  actionWindow: 1500,       // ms : durée de validité du dernier clic pour la petite réponse locale
  threeCdn: 'https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.min.js',
  wipe: { duration: 0.7, revealAt: 0.3, drips: 7, dripDepth: 11, wave: 1.6, points: 64 },
  enter: {
    titleDuration: 0.9, titleStagger: 0.022, textDelay: 0.28, textStagger: 0.08, textY: 14, textDuration: 0.7,
    blockDelay: 0.36, blockStagger: 0.08, blockY: 16,
    cardDuration: 0.95, cardStagger: 0.12, cardWave: 7, cardStart: 'top 92%',
    rowY: 16, rowStagger: 0.05, rowDuration: 0.6, imgScale: 0.6,
    barDelay: 0.3, barDuration: 0.9, countDuration: 0.9, capDuration: 1.0, railStagger: 0.1,
    reducedFade: 0.15, swap: 0.35, updateBar: 0.6,
  },
  ink: {
    simScale: 0.35, maxSim: 512, fade: 0.96, life: 2.2,
    radius: 30, radiusMin: 16, radiusMax: 80, radiusSpeed: 0.03,
    amount: 0.5, amountSpeed: 0.0014, amountMax: 1.25,
    threshold: 0.32, soft: 0.07, noise: 0.11, noiseScale: 2.3, drip: 0.22, drag: 0.08,
    feather: 14, edge: 14, capTitle: 0.42, capSub: 0.4, capMuted: 0.2,
    titleReach: 28, tapBurst: 6, emblemDripEvery: 0.09,
  },
  emblem: { tilt: 15, lerp: 0.08, bob: 0.025, bobSpeed: 1.1, margin: 0.25, depth: 0.05, bevel: 0.025, radius: 0.18, sheen: 1, chroma: 0.004, introDuration: 1.1, reach: 0.35 },
  tilt: { max: 4, lerp: 0.12, perspective: 900, parallax: 6, artScale: 1.06 },
  magnetic: { max: 6, strength: 0.3, follow: 0.35, release: 0.9 },
  tabs: { lead: 0.32, trail: 0.44, lag: 0.07, squash: 0.86 },
  splash: { duration: 0.42, inkAt: 0.12 },
  toast: { sx: 1.16, sy: 0.8, duration: 0.75 },
};

const html = document.documentElement;
const gsap = window.gsap;
const ScrollTrigger = window.ScrollTrigger || null;
const canvasEl = document.querySelector(SEL.canvas);
const unboot = () => html.classList.remove('fx-boot');

function boot() {
  if (!gsap || !canvasEl) { unboot(); return; }   // bibliothèques absentes : la page reste celle du template
  if (ScrollTrigger) gsap.registerPlugin(ScrollTrigger);

  /* ---------- capacités ---------- */
  const fineQ = matchMedia('(hover: hover) and (pointer: fine) and (min-width: 861px)');
  const env = {
    reduced: matchMedia('(prefers-reduced-motion: reduce)').matches,
    get fine() { return fineQ.matches; },        // souris + écran large : encre qui suit, inclinaison, aimant
    webgl: (() => { try { return !!document.createElement('canvas').getContext('webgl2'); } catch (e) { return false; } })(),
  };

  /* ---------- pointeur partagé (lu dans la boucle, jamais d'écriture DOM ici) ---------- */
  const pointer = { x: -1e4, y: -1e4, inside: false, type: 'mouse', taps: [] };
  addEventListener('pointermove', (e) => { pointer.x = e.clientX; pointer.y = e.clientY; pointer.type = e.pointerType; pointer.inside = true; }, { passive: true });
  addEventListener('pointerdown', (e) => {
    pointer.x = e.clientX; pointer.y = e.clientY; pointer.type = e.pointerType;
    if (e.pointerType !== 'mouse' || !env.fine) pointer.taps.push({ x: e.clientX, y: e.clientY });
  }, { passive: true });
  document.addEventListener('pointerout', (e) => { if (!e.relatedTarget) pointer.inside = false; }, { passive: true });

  /* ---------- Lenis + GSAP : une seule boucle ---------- */
  let lenis = null;
  if (!env.reduced && window.Lenis) {
    lenis = new window.Lenis({ lerp: TUNE.lenis.lerp, smoothWheel: true });
    if (ScrollTrigger) lenis.on('scroll', ScrollTrigger.update);
  }
  const scrollTop = () => { if (lenis) lenis.scrollTo(0, { immediate: true, force: true }); else scrollTo(0, 0); };
  // le template fait window.scrollTo(0, 0) au changement d'adresse : on aligne Lenis (sinon il repartirait de l'ancienne position)
  addEventListener('hashchange', () => { if (lenis) lenis.scrollTo(0, { immediate: true, force: true }); });

  let stage = null;            // canvas WebGL partagé (encre + emblème), créé quand Three.js est chargé
  gsap.ticker.lagSmoothing(0);
  gsap.ticker.add((time, deltaMs) => {
    if (lenis) lenis.raf(time * 1000);
    if (stage && !stage.dead) {
      try { stage.frame(Math.min(deltaMs / 1000, 1 / 15), time); }
      catch (err) { console.warn('[Yalla Quest fx] WebGL arrêté :', err); stage.kill(); FloatingEmblem.destroy(); }
    }
  });

  /* ---------- fenêtres <dialog> : Lenis en pause, défilement bloqué ---------- */
  let locked = false;
  const syncDialogs = () => {
    const open = document.querySelectorAll('dialog[open]');
    open.forEach((d) => d.setAttribute('data-lenis-prevent', ''));
    const on = open.length > 0;
    if (on === locked) return;
    locked = on;
    if (on) {
      html.style.setProperty('--fx-scrollbar', Math.max(0, innerWidth - html.clientWidth) + 'px');
      html.classList.add('fx-locked');
      if (lenis) lenis.stop();
    } else {
      html.classList.remove('fx-locked');
      html.style.removeProperty('--fx-scrollbar');
      if (lenis) lenis.start();
    }
  };
  const dialogWatch = new MutationObserver(syncDialogs);
  document.querySelectorAll(SEL.dialogs).forEach((d) => dialogWatch.observe(d, { attributes: true, attributeFilter: ['open'] }));
  document.addEventListener('close', syncDialogs, true);

  /* ---------- configuration partagée par les modules ---------- */
  const cfg = { SEL, TUNE, env, gsap, ScrollTrigger, pointer, getVelocity: () => (lenis ? lenis.velocity : 0), stageReady: () => !!(stage && !stage.dead) };
  [Entrances, Transitions, Micro].forEach((m) => m.setup(cfg));

  const safe = (name, fn) => {
    try { fn(); }
    catch (err) {
      console.warn(`[Yalla Quest fx] ${name} désactivé :`, err);
      if (name === 'entrances') { try { Entrances.destroy(true); } catch (e) { /* rien */ } }
    }
  };

  /* ---------- dernier geste (capturé AVANT le gestionnaire du template, donc avant le re-rendu) ---------- */
  let action = null;
  document.addEventListener('click', (e) => {
    const t = e.target.closest && e.target.closest('button, a[role="tab"], a[href^="#"]');
    if (!t) return;
    const ds = t.dataset;
    action = {
      kind: ds.vote ? 'vote' : (ds.gtab || ds.gltab || ds.qtab) ? 'tab' : 'other',
      data: Object.assign({}, ds), x: e.clientX, y: e.clientY, keyboard: e.detail === 0,
      at: performance.now(), tabs: Micro.measureTabs(activeView()),
    };
  }, true);

  /* ---------- cycle de vie du rendu ---------- */
  const life = { route: null, changedAt: -1e9, revealAt: 0, bars: new Map() };
  // la vue affichée : la section ciblée par l'adresse, sinon la vue par défaut (#g1)
  const activeView = () => {
    let el = null;
    try { el = canvasEl.querySelector(SEL.view + ':target'); } catch (e) { el = null; }
    return el || canvasEl.querySelector(SEL.defaultView) || canvasEl;
  };
  const effectiveRoute = () => activeView().id || 'guildes';
  const destroyAll = () => {
    safe('entrances', () => Entrances.destroy());
    safe('micro', () => Micro.destroy());
    safe('ink', () => InkField.destroy());
    safe('emblem', () => FloatingEmblem.destroy());
  };

  function onRender(detail, isBoot = false) {
    const route = effectiveRoute(detail);
    const now = performance.now();
    const changed = isBoot || route !== life.route;
    const echo = !changed && now - life.changedAt < TUNE.echoWindow;
    const act = action && now - action.at < TUNE.actionWindow ? action : null;
    action = null;
    const prevBars = life.bars;

    destroyAll();                                   // ScrollTriggers, SplitType, écouteurs de l'ancien #canvas
    const root = activeView();
    life.bars = Entrances.measure(root);        // largeurs cibles du nouveau rendu, lues avant toute animation

    let delay = 0;
    if (changed) {
      life.changedAt = now;
      if (!isBoot) scrollTop();
      try { delay = Transitions.play(); } catch (err) { console.warn('[Yalla Quest fx] transition :', err); delay = 0; }
      life.revealAt = now + delay * 1000;
    } else if (echo) {
      delay = Math.max(0, (life.revealAt - now) / 1000);
    }
    life.route = route;
    const ctx = { mode: changed || echo ? 'enter' : 'update', delay, prevBars, action: act, route };

    safe('entrances', () => Entrances.init(root, ctx));
    safe('micro', () => Micro.init(root, ctx));
    if (cfg.stageReady()) {
      safe('ink', () => InkField.init(root, ctx));
      safe('emblem', () => FloatingEmblem.init(root, ctx));
    }
    if (ScrollTrigger) ScrollTrigger.refresh();
    unboot();
  }

  // changement d'adresse : la vue ciblée change (CSS :target) ; les effets suivent
  addEventListener('hashchange', () => onRender(null));

  html.classList.add('fx');
  if (env.reduced) html.classList.add('fx-reduced');
  Micro.setupToast(document.querySelector(SEL.toast));

  // premier rendu : le template a déjà rendu #canvas avant que ce module ne charge. Si l'indicateur de
  // chargement (Views/front-office/includes/spinner.php) couvre encore la page, l'entrée attend qu'il s'efface (4 s au plus).
  let booted = false;
  const firstRender = () => { if (booted) return; booted = true; onRender(null, true); };
  if (html.classList.contains('yq-loading')) {
    document.addEventListener('yallaquest:loaded', firstRender, { once: true });
    setTimeout(firstRender, 4000);
  } else firstRender();

  /* ---------- WebGL : chargé après coup, n'empêche jamais le reste ---------- */
  if (env.webgl && !env.reduced) {
    import('three').catch(() => import(TUNE.threeCdn)).then((THREE) => {
      stage = InkField.createStage(THREE, cfg);
      if (!stage) return;
      FloatingEmblem.setup(THREE, stage, cfg);
      const entering = performance.now() - life.changedAt < 1200;
      const ctx = { mode: entering ? 'enter' : 'update', delay: Math.max(0, (life.revealAt - performance.now()) / 1000), prevBars: life.bars, action: null, route: life.route };
      safe('ink', () => InkField.init(activeView(), ctx));
      safe('emblem', () => FloatingEmblem.init(activeView(), ctx));
    }).catch((err) => console.warn('[Yalla Quest fx] Three.js indisponible, encre et emblème 3D désactivés :', err));
  }

  /* ---------- API exposée (lecture) ---------- */
  window.YallaQuestFX = {
    SEL, TUNE, env,
    get lenis() { return lenis; },
    get velocity() { return lenis ? lenis.velocity : 0; },   // vitesse de défilement (px par image)
  };
}

try { boot(); }
catch (err) { console.warn('[Yalla Quest fx] effets désactivés :', err); unboot(); }
