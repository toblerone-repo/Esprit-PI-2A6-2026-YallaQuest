// Yalla Quest · effets — micro-interactions : inclinaison des cartes, boutons magnétiques,
// indicateur d'onglet en goutte, éclaboussure dans le bouton de vote, toast élastique.
// Tout est délégué sur document : aucun écouteur à rebrancher après un re-rendu de #canvas.

let ctx = null;
let bound = false;
let tilts = new Map();     // carte -> { rx, ry, k, tx, ty, tk }
let magnet = null;         // élément actuellement attiré
let lastTab = null;        // { key, left, top, width, height } de l'onglet sélectionné précédent
let blobs = [];            // { tabs, blob, ro, tween }
let toastObserver = null;
const point = { x: 0, y: 0 };

const fine = () => ctx && !ctx.env.coarse && !ctx.env.reduced;

/* ---------- 1. inclinaison 3D des cartes + parallaxe de l'illustration ---------- */
function onMove(e) {
  point.x = e.clientX; point.y = e.clientY;
  if (!fine() || e.pointerType === 'touch') return;
  const S = ctx.SEL, T = ctx.TUNE.tilt;
  const t = e.target instanceof Element ? e.target : null;
  const card = t && t.closest(S.proposal);
  for (const [c, s] of tilts) if (c !== card) { s.tx = s.ty = s.tk = 0; }
  if (card && card.dataset.fxEntering !== '1' && ctx.root.contains(card)) {
    let s = tilts.get(card);
    if (!s) { s = { rx: 0, ry: 0, k: 0, tx: 0, ty: 0, tk: 0 }; tilts.set(card, s); }
    // à plat au-dessus des boutons de vote, de la liste des votants et de l'illustration (encre « reveal »)
    if (t.closest(S.noTilt)) { s.tx = s.ty = s.tk = 0; }
    else {
      const r = card.getBoundingClientRect();
      const nx = ((e.clientX - r.left) / r.width) * 2 - 1, ny = ((e.clientY - r.top) / r.height) * 2 - 1;
      s.ty = nx * T.max; s.tx = -ny * T.max; s.tk = 1;
      s.px = nx; s.py = ny;
    }
  }
  magnetMove(e, t);
}

function tiltTick() {
  if (!tilts.size) return;
  const { gsap, SEL } = ctx, T = ctx.TUNE.tilt;
  for (const [card, s] of tilts) {
    if (!card.isConnected) { tilts.delete(card); continue; }
    s.rx += (s.tx - s.rx) * T.lerp; s.ry += (s.ty - s.ry) * T.lerp; s.k += (s.tk - s.k) * T.lerp;
    const art = card.querySelector(SEL.art);
    const rest = Math.abs(s.rx) < 0.02 && Math.abs(s.ry) < 0.02 && s.k < 0.01;
    if (rest && !s.tk) {
      gsap.set(card, { clearProps: 'transform,willChange' });
      if (art) gsap.set(art, { clearProps: 'transform' });
      delete card.dataset.fxTilt;
      tilts.delete(card);
      continue;
    }
    card.dataset.fxTilt = '1';
    gsap.set(card, { rotationX: s.rx, rotationY: s.ry, transformPerspective: T.perspective, willChange: 'transform' });
    if (art) gsap.set(art, { x: -(s.px || 0) * T.parallax * s.k, y: -(s.py || 0) * T.parallax * s.k, scale: 1 + T.artScale * s.k });
  }
}

function flatten() {
  for (const s of tilts.values()) { s.tx = s.ty = s.tk = 0; }
  releaseMagnet();
}

/* ---------- 2. boutons magnétiques ---------- */
function magnetMove(e, t) {
  const el = t && t.closest(ctx.SEL.magnetic);
  if (el !== magnet) releaseMagnet();
  if (!el || el.disabled) return;
  magnet = el;
  const { gsap, TUNE } = ctx, M = TUNE.magnetic;
  const r = el.getBoundingClientRect();
  const cx = r.left + r.width / 2 - (+gsap.getProperty(el, 'x') || 0), cy = r.top + r.height / 2 - (+gsap.getProperty(el, 'y') || 0);
  const clamp = (v) => Math.max(-M.max, Math.min(M.max, v));
  gsap.to(el, { x: clamp((e.clientX - cx) * M.strength), y: clamp((e.clientY - cy) * M.strength), duration: 0.3, ease: 'power3.out', overwrite: 'auto' });
}
function releaseMagnet() {
  if (!magnet) return;
  const el = magnet; magnet = null;
  if (el.isConnected) ctx.gsap.to(el, { x: 0, y: 0, scale: 1, duration: ctx.TUNE.magnetic.release, ease: 'elastic.out(1, 0.35)', overwrite: 'auto', clearProps: 'transform' });
}
function onDown(e) {
  // l'attraction pose un transform en ligne : on rejoue ici le petit « appui » du :active d'origine
  if (magnet && e.target instanceof Element && magnet.contains(e.target)) ctx.gsap.to(magnet, { scale: 0.97, duration: 0.1, overwrite: 'auto' });
}
function onUp() { if (magnet) ctx.gsap.to(magnet, { scale: 1, duration: 0.25, ease: 'power2.out', overwrite: 'auto' }); }

/* ---------- 3. éclaboussure d'encre dans le bouton de vote ---------- */
function onClick(e) {
  if (!ctx || ctx.env.reduced || !(e.target instanceof Element)) return;
  const btn = e.target.closest(ctx.SEL.choiceBtn);
  if (!btn) return;
  const id = btn.dataset.vote, answer = btn.dataset.answer, was = btn.getAttribute('aria-pressed') === 'true';
  const keyboard = e.detail === 0, x = e.clientX, y = e.clientY;
  // après le traitement du clic par le template : le bouton a pu être re-rendu
  setTimeout(() => {
    const now = [...document.querySelectorAll(ctx.SEL.choiceBtn)].find((b) => b.dataset.vote === id && b.dataset.answer === answer);
    if (!now) return;
    if (was) { ctx.gsap.fromTo(now, { scale: 0.94 }, { scale: 1, duration: 0.5, ease: 'elastic.out(1, 0.4)', clearProps: 'transform' }); return; }
    if (now.getAttribute('aria-pressed') === 'true') splash(now, keyboard ? null : { x, y });
  }, 0);
}

function splash(btn, at) {
  const { gsap, TUNE } = ctx, D = TUNE.splash;
  const r = btn.getBoundingClientRect();
  const x = at ? Math.max(0, Math.min(r.width, at.x - r.left)) : r.width / 2, y = at ? Math.max(0, Math.min(r.height, at.y - r.top)) : r.height / 2;
  const reach = Math.hypot(Math.max(x, r.width - x), Math.max(y, r.height - y)) + 6;
  const other = [...btn.parentElement.querySelectorAll('button')].find((b) => b !== btn);
  const from = other ? getComputedStyle(other).color : getComputedStyle(btn).color, to = getComputedStyle(btn).color;

  const wrap = document.createElement('span');
  wrap.className = 'fx-splash'; wrap.setAttribute('aria-hidden', 'true');
  const dot = (px, py, size) => { const d = document.createElement('i'); d.style.cssText = `left:${px - size / 2}px;top:${py - size / 2}px;width:${size}px;height:${size}px`; wrap.appendChild(d); return d; };
  const main = dot(x, y, reach * 2);
  const sats = Array.from({ length: D.drops }, (_, i) => { const a = (i / D.drops) * 6.283 + Math.random(), d = reach * (0.35 + Math.random() * 0.3); return dot(x + Math.cos(a) * d, y + Math.sin(a) * d * 0.6, reach * (0.5 + Math.random() * 0.4)); });
  btn.appendChild(wrap);
  btn.classList.add('fx-splashing');
  const done = () => { btn.classList.remove('fx-splashing'); gsap.to(wrap, { opacity: 0, duration: 0.2, onComplete: () => wrap.remove() }); };
  gsap.fromTo(btn, { color: from }, { color: to, duration: D.duration * 0.22, delay: D.duration * 0.04, ease: 'none', clearProps: 'color' });
  gsap.fromTo(sats, { scale: 0 }, { scale: 1, duration: D.duration, ease: 'back.out(2.2)', stagger: 0.03 });
  gsap.fromTo(main, { scale: 0 }, { scale: 1, duration: D.duration, ease: 'power3.out', onComplete: done });
}

/* ---------- 4. onglets : une goutte qui glisse d'un onglet à l'autre ---------- */
function tabKey(tabs) {
  const first = tabs.querySelector('[role="tab"]');
  return (ctx.route || '') + ':' + (first ? Object.keys(first.dataset).join(',') : '');
}
function rectOf(tab) { return { left: tab.offsetLeft, top: tab.offsetTop, width: tab.offsetWidth, height: tab.offsetHeight }; }
function place(blob, r) { blob.style.left = r.left + 'px'; blob.style.top = r.top + 'px'; blob.style.width = r.width + 'px'; blob.style.height = r.height + 'px'; }

function initTabs(root) {
  if (ctx.env.reduced) return;
  const { gsap, SEL, TUNE } = ctx;
  root.querySelectorAll(SEL.tabs).forEach((tabs) => {
    const sel = tabs.querySelector(SEL.tabSelected);
    if (!sel) return;
    const blob = document.createElement('span');
    blob.className = 'fx-tabblob'; blob.setAttribute('aria-hidden', 'true'); blob.setAttribute('role', 'presentation');
    const cs = getComputedStyle(sel);
    blob.style.background = cs.backgroundColor;   // la couleur de l'onglet sélectionné, lue avant de le rendre transparent
    blob.style.borderRadius = cs.borderTopLeftRadius;
    tabs.insertBefore(blob, tabs.firstChild);
    tabs.classList.add('fx-tabs');
    const key = tabKey(tabs), to = rectOf(sel), entry = { tabs, blob, tween: null, ro: null };
    const from = ctx.sameRoute && lastTab && lastTab.key === key ? lastTab : null;
    if (from && (from.left !== to.left || from.top !== to.top)) {
      // le bord avant part vite, le bord arrière suit : la goutte s'étire puis se ramasse
      const o = { l: from.left, r: from.left + from.width, t: from.top, h: from.height }, right = to.left > from.left;
      const fast = { duration: TUNE.tabs.duration * 0.55, ease: 'power3.out' }, slow = { duration: TUNE.tabs.duration, ease: 'power3.inOut' };
      const draw = () => place(blob, { left: o.l, top: o.t, width: Math.max(8, o.r - o.l), height: o.h });
      draw();
      entry.tween = gsap.timeline({ onUpdate: draw, onComplete: () => place(blob, rectOf(sel)) })
        .to(o, { l: to.left, ...(right ? slow : fast) }, 0)
        .to(o, { r: to.left + to.width, ...(right ? fast : slow) }, 0)
        .to(o, { t: to.top, h: to.height, ...slow }, 0)
        .fromTo(blob, { scaleY: 1 }, { scaleY: 0.82, duration: TUNE.tabs.duration * 0.4, ease: 'power2.out', yoyo: true, repeat: 1 }, 0);
    } else place(blob, to);
    lastTab = { key, ...to };
    if (window.ResizeObserver) {
      entry.ro = new ResizeObserver(() => { if (!entry.tween || !entry.tween.isActive()) { const r = rectOf(sel); place(blob, r); if (lastTab && lastTab.key === key) lastTab = { key, ...r }; } });
      entry.ro.observe(tabs);
    }
    blobs.push(entry);
  });
}
function destroyTabs() {
  for (const b of blobs) {
    if (b.tween) b.tween.kill();
    if (b.ro) b.ro.disconnect();
    b.blob.remove();
    b.tabs.classList.remove('fx-tabs');
  }
  blobs = [];
}

/* ---------- 5. toast : petit écrasement / étirement liquide (CSS, voir effects.css) ---------- */
function watchToast() {
  const toast = document.querySelector(ctx.SEL.toast);
  if (!toast || toastObserver || !window.MutationObserver) return;
  toastObserver = new MutationObserver(() => {
    toast.classList.remove('fx-pop');
    void toast.offsetWidth; // relance l'animation à chaque nouveau message
    toast.classList.add('fx-pop');
  });
  toastObserver.observe(toast, { childList: true, characterData: true, subtree: true });
}

/* ---------- cycle de vie ---------- */
export function init(root, c) {
  ctx = c;
  destroyTabs();
  initTabs(root);
  watchToast();
  if (bound) return;
  bound = true;
  document.addEventListener('pointermove', onMove, { passive: true });
  document.addEventListener('pointerdown', onDown, { passive: true });
  document.addEventListener('pointerup', onUp, { passive: true });
  document.addEventListener('click', onClick, true);
  document.documentElement.addEventListener('pointerleave', flatten);
  window.addEventListener('blur', flatten);
  c.gsap.ticker.add(tiltTick);
}

/** Avant un re-rendu : rien à débrancher (délégation), on retire seulement ce qu'on a ajouté au DOM. */
export function destroy() {
  destroyTabs();
}

export function dispose() {
  destroyTabs();
  if (toastObserver) { toastObserver.disconnect(); toastObserver = null; }
  document.querySelector(ctx?.SEL.toast || '.toast')?.classList.remove('fx-pop');
  for (const card of tilts.keys()) {
    if (!card.isConnected) continue;
    ctx.gsap.set(card, { clearProps: 'transform,willChange' });
    const art = card.querySelector(ctx.SEL.art); if (art) ctx.gsap.set(art, { clearProps: 'transform' });
    delete card.dataset.fxTilt;
  }
  tilts.clear();
  if (magnet) { ctx.gsap.set(magnet, { clearProps: 'transform' }); magnet = null; }
  lastTab = null;
  if (!bound) return;
  bound = false;
  document.removeEventListener('pointermove', onMove);
  document.removeEventListener('pointerdown', onDown);
  document.removeEventListener('pointerup', onUp);
  document.removeEventListener('click', onClick, true);
  document.documentElement.removeEventListener('pointerleave', flatten);
  window.removeEventListener('blur', flatten);
  ctx.gsap.ticker.remove(tiltTick);
}
