// Yalla Quest · effets de défilement et de souris (script classique, sans build : marche aussi en file://).
// Bibliothèques (scripts classiques -> globales) : GSAP + ScrollTrigger, Lenis, SplitType.
// PAS d'effet d'encre qui suit la souris, PAS de WebGL / Three.js.
//
// Ce que fait ce fichier :
//  1. Lenis : défilement fluide synchronisé avec gsap.ticker et ScrollTrigger, en pause quand une fenêtre est ouverte.
//  2. Transition entre pages : un rideau lilas puis noir coule sur la zone de contenu (le menu latéral ne bouge pas).
//  3. Entrées : lettres du titre, textes, panneaux, cartes révélées par un bord liquide, jauges et compteurs.
//  4. Micro-interactions : boutons magnétiques, inclinaison des cartes, pastille d'onglet qui glisse, toast élastique.
// La page marche exactement pareil sans ce fichier (ou si une erreur survient : les effets se coupent).

(() => {
/* ---------- sélecteurs (à adapter ici si une classe change) ---------- */
const SEL = {
  root: '[data-fx-root]',                       // zone animée et couverte par le rideau
  live: '[data-fx-live]',                       // conteneurs re-rendus par le JS de la page (pas de rejeu d'entrée)
  title: 'h1',
  text: '.kicker, .about, .sub, [data-fx-text]',
  panels: '.panel, .kpis, .sheet, .tabs, .filters, [data-fx-panel]',
  cards: '.obj-card, .portal-card',
  capFill: '.capbar i',
  counters: '.kpi-value',
  magnetic: '.btn.primary',
  tilt: '.obj-card, .portal-card',
  noTilt: 'button, input, select, textarea, .pfoot, [data-no-tilt]',
  tabs: '.tabs',
  toast: '#toast',
};

/* ---------- réglages ---------- */
const TUNE = {
  lenis: { lerp: 0.1 },
  curtain: { out: 0.45, in: 0.65, band: 70, drip: 90, wave: 18, enterDelay: 0.25 },
  entrance: { titleDuration: 0.85, titleStagger: 0.028, textStagger: 0.08, panelStagger: 0.1, cardDuration: 0.95, cardStagger: 0.12, bar: 0.9 },
  tilt: { max: 4, lerp: 0.12, perspective: 900 },
  magnetic: { max: 6, strength: 0.3, release: 0.8 },
  tabs: { duration: 0.5 },
};

const html = document.documentElement;
const gsap = window.gsap;
const ScrollTrigger = window.ScrollTrigger || null;
const env = {
  reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
  coarse: window.matchMedia('(hover: none)').matches || window.matchMedia('(max-width: 860px)').matches,
};
let lenis = null;
let root = null;
const tracked = { tweens: [], triggers: [], cards: [] };
const FLAG = 'yq-fx-enter';

const unboot = () => html.classList.remove('fx-boot');
const track = (t) => { tracked.tweens.push(t); return t; };

/* ======================================================================
   1. Lenis
   ====================================================================== */
function setupLenis() {
  if (env.reduced || !window.Lenis) return;
  lenis = new window.Lenis({ lerp: TUNE.lenis.lerp, autoRaf: false });
  if (ScrollTrigger) lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);
  document.querySelector('#sidebar')?.setAttribute('data-lenis-prevent', '');
  // toute fenêtre <dialog> (même créée plus tard) : défilement propre + Lenis en pause tant qu'elle est ouverte
  const sync = () => {
    const dialogs = [...document.querySelectorAll('dialog')];
    dialogs.forEach((d) => d.setAttribute('data-lenis-prevent', ''));
    const open = dialogs.some((d) => d.open);
    html.classList.toggle('fx-locked', open);
    if (open) lenis.stop(); else lenis.start();
  };
  new MutationObserver(sync).observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['open'] });
  sync();
}

/* ======================================================================
   2. Rideau entre les pages
   ====================================================================== */
function curtainRect() {
  const r = root ? root.getBoundingClientRect() : { left: 0, width: window.innerWidth, top: 0 };
  const top = Math.max(0, r.top);
  return { left: r.left, top, width: r.width, height: window.innerHeight - top };
}

// mode 'out' : le rideau descend et couvre ; mode 'in' : il part vers le bas et découvre la page
function curtain(mode, duration) {
  const { left, top, width: W, height: H } = curtainRect();
  if (W < 10 || H < 10) return null;
  const T = TUNE.curtain, ns = 'http://www.w3.org/2000/svg';
  const layer = document.createElement('div');
  layer.className = 'fx-curtain' + (mode === 'out' ? ' blocking' : '');
  layer.setAttribute('aria-hidden', 'true');
  layer.style.cssText = `left:${left}px;top:${top}px;width:${W}px;height:${H}px`;
  const svg = document.createElementNS(ns, 'svg');
  svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
  svg.setAttribute('preserveAspectRatio', 'none');
  const ink = document.createElementNS(ns, 'path'); ink.setAttribute('class', 'fx-curtain-ink');
  const ground = document.createElementNS(ns, 'path'); ground.setAttribute('class', 'fx-curtain-ground');
  svg.append(ink, ground);
  layer.appendChild(svg);
  document.body.appendChild(layer);

  let seed = 11 + Math.floor(Math.random() * 997);
  const rnd = () => { seed = (seed * 16807) % 2147483647; return seed / 2147483647; };
  const drips = Array.from({ length: Math.max(4, Math.round(W / 160)) }, () => ({ x: rnd() * W, w: 24 + rnd() * 50, len: 0.4 + rnd() * 0.6 }));
  const step = Math.max(10, W / 80), n = Math.ceil(W / step), ph = rnd() * 6.28;
  const from = -T.band - T.drip - 20, to = H + T.band + 20;
  const state = { t: 0 };
  const ease = gsap.parseEase('power2.inOut');
  const edge = (x, base, grow) => {
    let y = base + Math.sin(x * 0.012 + ph + state.t * 5) * T.wave;
    for (const d of drips) y += Math.exp(-(((x - d.x) / d.w) ** 2)) * d.len * T.drip * grow;
    return y;
  };
  const draw = () => {
    const base = from + (to - from) * ease(state.t), grow = Math.sin(state.t * Math.PI);
    const xs = Array.from({ length: n + 1 }, (_, i) => Math.min(W, i * step));
    const line = (off) => xs.map((x, i) => `${i ? 'L' : 'M'}${x.toFixed(1)} ${edge(x, base + off, grow).toFixed(1)}`).join('');
    if (mode === 'out') {
      // couvre : lilas jusqu'au bord, noir un peu au-dessus
      ink.setAttribute('d', `${line(0)}L${W} -2L0 -2Z`);
      ground.setAttribute('d', `${line(-T.band)}L${W} -2L0 -2Z`);
    } else {
      // découvre : lilas sous le bord, noir un peu en dessous
      ink.setAttribute('d', `${line(0)}L${W} ${H + 2}L0 ${H + 2}Z`);
      ground.setAttribute('d', `${line(T.band)}L${W} ${H + 2}L0 ${H + 2}Z`);
    }
  };
  draw();
  const tween = gsap.to(state, { t: 1, duration, ease: 'none', onUpdate: draw, onComplete: () => { if (mode === 'in') layer.remove(); } });
  return { layer, tween };
}

function setupNavigation() {
  // liens internes : le rideau couvre, puis on change de page
  window.YQ = window.YQ || {};
  let leaving = false;
  window.YQ.beforeNavigate = (url) => {
    if (leaving) return;
    if (env.reduced) { window.location.href = url; return; }
    leaving = true;
    try { sessionStorage.setItem(FLAG, '1'); } catch (e) { /* stockage indisponible */ }
    if (lenis) lenis.stop();
    const c = curtain('out', TUNE.curtain.out);
    const go = () => { window.location.href = url; };
    if (c) c.tween.eventCallback('onComplete', go); else go();
    setTimeout(go, 1200); // filet de sécurité
  };
  document.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    const a = e.target.closest('a[href]');
    if (!a || a.target === '_blank' || a.hasAttribute('download') || a.dataset.noFx !== undefined) return;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin || (url.pathname === location.pathname && url.hash)) return;
    if (url.href === location.href) return;
    e.preventDefault();
    window.YQ.beforeNavigate(url.href);
  });
  // retour arrière (cache du navigateur) : retirer le rideau resté affiché
  window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    leaving = false;
    document.querySelectorAll('.fx-curtain').forEach((l) => l.remove());
    if (lenis) lenis.start();
  });
}

/* ======================================================================
   3. Entrées
   ====================================================================== */
function numberNode(el) {
  for (const n of el.childNodes) if (n.nodeType === 3 && /\d+/.test(n.nodeValue)) return n;
  return null;
}
function countUp(el, delay) {
  const node = numberNode(el); if (!node) return;
  const final = node.nodeValue, to = parseInt(final.match(/\d+/)[0], 10);
  if (!to) return;
  const o = { v: 0 };
  const put = (v) => { node.nodeValue = final.replace(/\d+/, String(Math.round(v))); };
  put(0);
  track(gsap.to(o, { v: to, duration: TUNE.entrance.bar, delay, ease: 'power2.out', onUpdate: () => put(o.v), onComplete: () => { if (node.isConnected) node.nodeValue = final; } }));
}
function liquidClip(p) {
  const N = 12, amp = 9 * Math.sin(Math.PI * p), base = (1 - p) * 104 - 4;
  let s = '';
  for (let i = 0; i <= N; i++) s += `${((i / N) * 100).toFixed(1)}% ${(base + Math.sin(i * 1.25 + p * 7) * amp + Math.sin(i * 0.6 - p * 4) * amp * 0.5).toFixed(2)}%,`;
  return `polygon(${s}100% 100%,0% 100%)`;
}
function revealCard(card, delay) {
  const o = { p: 0 }, E = TUNE.entrance;
  track(gsap.to(o, {
    p: 1, duration: E.cardDuration, delay, ease: 'power3.out',
    onUpdate: () => { card.style.clipPath = liquidClip(o.p); },
    onComplete: () => { card.style.clipPath = ''; delete card.dataset.fxEntering; },
  }));
  track(gsap.fromTo(card, { y: 28 }, { y: 0, duration: E.cardDuration, delay, ease: 'power3.out', clearProps: 'transform' }));
}
function settleCards() {
  // fin immédiate des révélations (re-rendu de la liste, ou arrêt des effets)
  tracked.triggers.forEach((t) => t.kill()); tracked.triggers = [];
  document.querySelectorAll('[data-fx-entering]').forEach((c) => { c.style.clipPath = ''; delete c.dataset.fxEntering; gsap.set(c, { clearProps: 'transform' }); });
}

function entrances(delay) {
  const E = TUNE.entrance, q = (s) => [...root.querySelectorAll(s)];
  const tl = track(gsap.timeline({ delay }));

  // titre : les lettres montent depuis le bas de leur ligne, puis le texte d'accompagnement
  const h1 = root.querySelector(SEL.title);
  if (h1 && window.SplitType) {
    h1.setAttribute('aria-label', h1.textContent.trim());
    const split = new window.SplitType(h1, { types: 'lines,words,chars', lineClass: 'fx-line', wordClass: 'fx-word', charClass: 'fx-char' });
    const undo = () => { split.revert(); h1.removeAttribute('aria-label'); };
    if (split.chars && split.chars.length) tl.from(split.chars, { yPercent: 110, duration: E.titleDuration, ease: 'power4.out', stagger: Math.min(E.titleStagger, 0.5 / split.chars.length), onComplete: undo, onInterrupt: undo }, 0);
    else undo();
  } else if (h1) tl.from(h1, { y: 24, opacity: 0, duration: 0.6, ease: 'power3.out', clearProps: 'all' }, 0);
  const text = q(SEL.text).filter((el) => !el.closest(SEL.cards));
  if (text.length) tl.from(text, { y: 14, opacity: 0, duration: 0.6, ease: 'power3.out', stagger: E.textStagger, clearProps: 'transform,opacity' }, h1 ? 0.22 : 0);
  const actions = q('.title-row .actions');
  if (actions.length) tl.from(actions, { y: 12, opacity: 0, duration: 0.6, ease: 'power3.out', clearProps: 'transform,opacity' }, 0.3);

  const startAt = performance.now() + delay * 1000;
  const wait = (extra) => Math.max(0, (startAt - performance.now()) / 1000) + extra;

  // panneaux, KPI, tableaux : montée en fondu au défilement ; jauges et compteurs
  const panels = q(SEL.panels).filter((el) => !el.closest(SEL.cards) && !el.closest('dialog'));
  if (panels.length && ScrollTrigger) {
    gsap.set(panels, { opacity: 0, y: 20 });
    tracked.triggers.push(...ScrollTrigger.batch(panels, {
      start: 'top 94%', once: true,
      onEnter: (batch) => batch.forEach((p, i) => {
        const d = wait(0.2 + i * E.panelStagger);
        track(gsap.to(p, { opacity: 1, y: 0, duration: 0.6, delay: d, ease: 'power3.out', clearProps: 'transform,opacity' }));
        p.querySelectorAll(SEL.capFill).forEach((b) => { const w = b.style.width; if (w) track(gsap.fromTo(b, { width: '0%' }, { width: w, duration: E.bar, delay: d + 0.15, ease: 'power3.out', onComplete: () => { b.style.width = w; } })); });
        p.querySelectorAll(SEL.counters).forEach((c) => countUp(c, d + 0.1));
      }),
    }));
  }

  // cartes : révélation par un bord liquide qui monte (clip-path : les boutons restent atteignables au clavier)
  const cards = q(SEL.cards);
  if (cards.length && ScrollTrigger) {
    cards.forEach((c) => { c.dataset.fxEntering = '1'; c.style.clipPath = 'inset(100% 0 0 0)'; });
    tracked.triggers.push(...ScrollTrigger.batch(cards, {
      start: 'top 92%', once: true,
      onEnter: (batch) => batch.forEach((c, i) => revealCard(c, wait(0.3 + i * E.cardStagger))),
    }));
  }

  // filet de sécurité : ce qui est à l'écran et encore masqué au bout de 3 s s'affiche (rien ne reste caché)
  setTimeout(() => {
    const inView = (el) => { const r = el.getBoundingClientRect(); return r.top < window.innerHeight && r.bottom > 0; };
    panels.forEach((p) => { if (p.isConnected && inView(p) && getComputedStyle(p).opacity === '0') gsap.set(p, { clearProps: 'transform,opacity' }); });
    root.querySelectorAll('[data-fx-entering]').forEach((c) => { if (inView(c) && c.style.clipPath.startsWith('inset')) { c.style.clipPath = ''; delete c.dataset.fxEntering; } });
  }, delay * 1000 + 3000);
}

// listes re-rendues par la page (filtre, accomplir, supprimer) : pas de rejeu, un léger fondu des nouvelles cartes
function watchLive() {
  root.querySelectorAll(SEL.live).forEach((box) => {
    new MutationObserver((muts) => {
      settleCards();
      const added = [];
      muts.forEach((m) => m.addedNodes.forEach((n) => { if (n.nodeType === 1) added.push(...(n.matches(SEL.cards) ? [n] : n.querySelectorAll(SEL.cards))); }));
      if (added.length && !env.reduced) gsap.fromTo(added, { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.3, stagger: 0.03, ease: 'power2.out', clearProps: 'transform,opacity' });
      if (ScrollTrigger) ScrollTrigger.refresh();
    }).observe(box, { childList: true });
  });
}

/* ======================================================================
   4. Micro-interactions
   ====================================================================== */
const tilts = new Map();
let magnet = null;
function onMove(e) {
  if (e.pointerType === 'touch') return;
  const t = e.target instanceof Element ? e.target : null;
  // inclinaison des cartes (à plat au-dessus des boutons et des champs)
  const card = t && t.closest(SEL.tilt);
  for (const [c, s] of tilts) if (c !== card) { s.tx = s.ty = 0; }
  if (card && card.dataset.fxEntering !== '1') {
    let s = tilts.get(card);
    if (!s) { s = { rx: 0, ry: 0, tx: 0, ty: 0 }; tilts.set(card, s); }
    if (t.closest(SEL.noTilt) && t.closest(SEL.noTilt) !== card) { s.tx = s.ty = 0; }
    else {
      const r = card.getBoundingClientRect();
      s.ty = (((e.clientX - r.left) / r.width) * 2 - 1) * TUNE.tilt.max;
      s.tx = -(((e.clientY - r.top) / r.height) * 2 - 1) * TUNE.tilt.max;
    }
  }
  // boutons magnétiques
  const m = t && t.closest(SEL.magnetic);
  if (m !== magnet) release();
  if (m && !m.disabled) {
    magnet = m;
    const M = TUNE.magnetic, r = m.getBoundingClientRect();
    const cx = r.left + r.width / 2 - (+gsap.getProperty(m, 'x') || 0), cy = r.top + r.height / 2 - (+gsap.getProperty(m, 'y') || 0);
    const clamp = (v) => Math.max(-M.max, Math.min(M.max, v));
    gsap.to(m, { x: clamp((e.clientX - cx) * M.strength), y: clamp((e.clientY - cy) * M.strength), duration: 0.3, ease: 'power3.out', overwrite: 'auto' });
  }
}
function release() {
  if (!magnet) return;
  const el = magnet; magnet = null;
  if (el.isConnected) gsap.to(el, { x: 0, y: 0, scale: 1, duration: TUNE.magnetic.release, ease: 'elastic.out(1, 0.35)', overwrite: 'auto', clearProps: 'transform' });
}
function tiltTick() {
  for (const [card, s] of tilts) {
    if (!card.isConnected) { tilts.delete(card); continue; }
    s.rx += (s.tx - s.rx) * TUNE.tilt.lerp; s.ry += (s.ty - s.ry) * TUNE.tilt.lerp;
    if (!s.tx && !s.ty && Math.abs(s.rx) < 0.02 && Math.abs(s.ry) < 0.02) { gsap.set(card, { clearProps: 'transform' }); tilts.delete(card); continue; }
    gsap.set(card, { rotationX: s.rx, rotationY: s.ry, transformPerspective: TUNE.tilt.perspective });
  }
}
function flatten() { for (const s of tilts.values()) { s.tx = s.ty = 0; } release(); }

// pastille d'onglet : la couleur de l'onglet sélectionné glisse et s'étire vers le nouvel onglet
function setupTabs() {
  document.querySelectorAll(SEL.tabs).forEach((tabs) => {
    const selected = () => tabs.querySelector('[role="tab"][aria-selected="true"]');
    const first = selected();
    if (!first) return;
    const blob = document.createElement('span');
    blob.className = 'fx-tabblob'; blob.setAttribute('aria-hidden', 'true');
    tabs.prepend(blob);
    tabs.classList.add('fx-tabs');
    const rect = (el) => ({ l: el.offsetLeft, t: el.offsetTop, w: el.offsetWidth, h: el.offsetHeight });
    const place = (r) => Object.assign(blob.style, { left: r.l + 'px', top: r.t + 'px', width: r.w + 'px', height: r.h + 'px' });
    let cur = rect(first), tl = null;
    place(cur);
    new MutationObserver(() => {
      const sel = selected(); if (!sel) return;
      const to = rect(sel);
      if (to.l === cur.l && to.t === cur.t) return;
      if (tl) tl.kill();
      const o = { l: cur.l, r: cur.l + cur.w, t: cur.t, h: cur.h }, right = to.l > cur.l, D = TUNE.tabs.duration;
      const fast = { duration: D * 0.55, ease: 'power3.out' }, slow = { duration: D, ease: 'power3.inOut' };
      const draw = () => place({ l: o.l, t: o.t, w: Math.max(8, o.r - o.l), h: o.h });
      tl = gsap.timeline({ onUpdate: draw, onComplete: () => place(rect(sel)) })
        .to(o, { l: to.l, ...(right ? slow : fast) }, 0)
        .to(o, { r: to.l + to.w, ...(right ? fast : slow) }, 0)
        .to(o, { t: to.t, h: to.h, ...slow }, 0);
      cur = to;
    }).observe(tabs, { subtree: true, attributes: true, attributeFilter: ['aria-selected'] });
    if (window.ResizeObserver) new ResizeObserver(() => { if (!tl || !tl.isActive()) { const s = selected(); if (s) { cur = rect(s); place(cur); } } }).observe(tabs);
  });
}

// toast : petit écrasement / étirement à chaque message
function setupToast() {
  const watch = (t) => new MutationObserver(() => { t.classList.remove('fx-pop'); void t.offsetWidth; t.classList.add('fx-pop'); }).observe(t, { childList: true, characterData: true, subtree: true });
  const t = document.querySelector(SEL.toast);
  if (t) watch(t);
  else new MutationObserver((m, obs) => { const n = document.querySelector(SEL.toast); if (n) { watch(n); obs.disconnect(); } }).observe(document.body, { childList: true });
}

function setupMicro() {
  setupTabs();
  setupToast();
  if (env.coarse || env.reduced) return; // pas d'inclinaison ni d'aimant au toucher ou sur petit écran
  document.addEventListener('pointermove', onMove, { passive: true });
  document.addEventListener('pointerdown', (e) => { if (magnet && magnet.contains(e.target)) gsap.to(magnet, { scale: 0.97, duration: 0.1, overwrite: 'auto' }); }, { passive: true });
  document.addEventListener('pointerup', () => { if (magnet) gsap.to(magnet, { scale: 1, duration: 0.25, ease: 'power2.out', overwrite: 'auto' }); }, { passive: true });
  html.addEventListener('pointerleave', flatten);
  window.addEventListener('blur', flatten);
  gsap.ticker.add(tiltTick);
}

/* ======================================================================
   démarrage
   ====================================================================== */
function boot() {
  root = document.querySelector(SEL.root);
  if (!gsap || !root) { unboot(); return; }
  if (ScrollTrigger) gsap.registerPlugin(ScrollTrigger);
  let arriving = false;
  try { arriving = sessionStorage.getItem(FLAG) === '1'; sessionStorage.removeItem(FLAG); } catch (e) { /* stockage indisponible */ }

  html.classList.add('fx-on');
  setupNavigation();
  if (env.reduced) {
    // mouvement réduit : ni Lenis, ni rideau, ni travelling : un fondu de 150 ms
    unboot();
    gsap.fromTo(root, { opacity: 0 }, { opacity: 1, duration: 0.15, ease: 'none', clearProps: 'opacity' });
    setupMicro();
    return;
  }
  setupLenis();
  let delay = 0.05;
  if (arriving) { curtain('in', TUNE.curtain.in); delay = TUNE.curtain.enterDelay; }
  entrances(delay);
  watchLive();
  setupMicro();
  unboot();
}

function safeBoot() {
  try { boot(); } catch (err) {
    console.warn('[Yalla Quest FX] effets désactivés :', err);
    try { settleCards(); tracked.tweens.forEach((t) => t.kill()); document.querySelectorAll('.fx-curtain').forEach((l) => l.remove()); } catch (e) { /* rien */ }
    unboot();
  }
}

// après le premier rendu de la page (ses scripts écoutent aussi DOMContentLoaded et passent avant ce module)
if (document.readyState === 'complete') setTimeout(safeBoot, 0);
else document.addEventListener('DOMContentLoaded', () => setTimeout(safeBoot, 0), { once: true });
})();
