// Entrées (changement de route seulement) : lettres du titre (SplitType), textes, cartes révélées par un bord
// liquide au défilement (ScrollTrigger, once), barres de vote et compteurs, lignes de tableau, jauge de membres.
// Re-rendu sur la même route : aucune entrée rejouée ; seule la barre de la carte votée glisse de ses anciennes
// largeurs vers les nouvelles (et ses compteurs suivent). Changement d'onglet : la liste apparaît en fondu court.
// Tout élément masqué qui reçoit le focus clavier est révélé aussitôt.

let cfg = null, gsap = null;
let entries = [];          // { els, tween, run? }
let triggers = [];
let splits = [];
let focusRoot = null, onFocus = null;

export function setup(c) { cfg = c; gsap = c.gsap; }

/* ---------- lecture des valeurs cibles écrites par le template ---------- */
const pct = (el) => (el ? parseFloat(el.style.width) || parseFloat(el.dataset.w) || 0 : 0);
function legendNodes(card) {
  // « <b class="k pour"></b>3 pour » et « … contre » : le nombre est en tête du nœud texte
  return [...card.querySelectorAll(cfg.SEL.legend)].slice(0, 2)
    .map((s) => s.lastChild).filter((n) => n && n.nodeType === 3 && /^\d+/.test(n.data));
}
function readCard(card) {
  const [p, c] = card.querySelectorAll(cfg.SEL.voteSeg);
  return { pour: pct(p), contre: pct(c), counts: legendNodes(card).map((n) => parseInt(n.data, 10)) };
}
export function measure(root) {
  const map = new Map();
  if (!cfg || !root) return map;
  root.querySelectorAll(cfg.SEL.proposal).forEach((card) => {
    const id = card.getAttribute('aria-labelledby');
    if (id) map.set(id, readCard(card));
  });
  return map;
}

/* ---------- utilitaires ---------- */
const $$ = (root, s) => [...root.querySelectorAll(s)];
function track(els, tween, run) { const e = { els: [].concat(els).filter(Boolean), tween, run }; entries.push(e); return e; }
function countTo(node, from, to, duration, delay) {
  const rest = node.data.replace(/^\d+/, '');
  const o = { v: from };
  node.data = from + rest;
  return gsap.to(o, { v: to, duration, delay, ease: 'power2.out', onUpdate: () => { node.data = Math.round(o.v) + rest; }, onComplete: () => { node.data = to + rest; } });
}
// bord liquide qui monte depuis le bas : p = 0 rien n'est visible, p = 1 tout est visible
function liquidPoly(p, seed, wave) {
  const amp = wave * (1 - p), base = (1 - p) * (100 + amp * 1.6 + 2), n = 18, pts = [];
  for (let i = 0; i <= n; i++) {
    const x = i / n;
    const y = base + Math.sin(x * Math.PI * 2.2 + seed + p * 5) * amp + Math.sin(x * Math.PI * 5.3 - seed * 1.3) * amp * 0.45;
    pts.push(`${(x * 100).toFixed(2)}% ${y.toFixed(2)}%`);
  }
  return `polygon(${pts.join(',')}, 100% 100%, 0% 100%)`;
}

/* ---------- init / destroy ---------- */
export function init(root, ctx) {
  if (!cfg || !root) return;
  focusRoot = root;
  onFocus = (e) => finishFor(e.target);
  root.addEventListener('focusin', onFocus);
  if (ctx.mode === 'update') return update(root, ctx);
  if (cfg.env.reduced) {
    const kids = [...root.children];
    track(kids, gsap.fromTo(kids, { opacity: 0 }, { opacity: 1, duration: cfg.TUNE.enter.reducedFade, ease: 'none', clearProps: 'opacity' }));
    return;
  }
  enter(root, ctx.delay || 0);
}

export function destroy(hard = false) {
  entries.forEach((e) => {
    if (e.tween) e.tween.kill();
    if (hard) {
      e.els.forEach((el) => { el.style.clipPath = ''; gsap.set(el, { clearProps: 'opacity,transform,visibility' }); });
      if (e.restore) e.restore();
    }
  });
  if (hard) splits.forEach((s) => s.done());
  triggers.forEach((t) => t.kill());
  splits.forEach((s) => { if (s.split) s.done(); });
  if (focusRoot && onFocus) focusRoot.removeEventListener('focusin', onFocus);
  entries = []; triggers = []; splits = []; focusRoot = null; onFocus = null;
}

function finishFor(target) {
  entries.forEach((e) => {
    if (!e.els.some((el) => el.contains(target))) return;
    if (e.run && !e.tween) e.run(0);
    if (e.tween) e.tween.progress(1);
  });
}

/* ---------- changement de route ---------- */
function enter(root, d) {
  const S = cfg.SEL, T = cfg.TUNE.enter;

  // titre : SplitType, lettres qui montent ; on rend le DOM d'origine à la fin (revert)
  const h1 = root.querySelector(S.title);
  if (h1 && window.SplitType) {
    const label = h1.textContent;
    const split = new window.SplitType(h1, { types: 'words, chars', tagName: 'span' });
    h1.classList.add('fx-split');
    h1.setAttribute('aria-label', label);
    const s = { split, done: () => { if (!s.split) return; s.split.revert(); s.split = null; h1.classList.remove('fx-split'); h1.removeAttribute('aria-label'); } };
    splits.push(s);
    track(h1, gsap.fromTo(split.chars || [], { yPercent: 110, opacity: 0 }, {
      yPercent: 0, opacity: 1, duration: T.titleDuration, ease: 'power4.out', stagger: T.titleStagger, delay: d, onComplete: s.done,
    }));
  } else if (h1) {
    track(h1, gsap.fromTo(h1, { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: T.titleDuration, ease: 'power4.out', delay: d, clearProps: 'transform,opacity' }));
  }

  // kicker, about, sub, puis les actions ; l'image d'emblème si l'emblème 3D n'est pas (encore) là
  const texts = $$(root, S.titleText);
  if (texts.length) track(texts, gsap.fromTo(texts, { y: T.textY, opacity: 0 }, { y: 0, opacity: 1, duration: T.textDuration, ease: 'power3.out', stagger: T.textStagger, delay: d + T.textDelay, clearProps: 'transform,opacity' }));
  const actions = root.querySelector(S.actions);
  if (actions) track(actions, gsap.fromTo(actions, { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.6, ease: 'power3.out', delay: d + T.textDelay + 0.12, clearProps: 'transform,opacity' }));
  const emblem = root.querySelector(S.emblem);
  if (emblem && !cfg.stageReady()) track(emblem, gsap.fromTo(emblem, { scale: 0.82, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.9, ease: 'back.out(1.6)', delay: d + 0.05, clearProps: 'transform,opacity' }));

  // blocs : onglets, tableau, réglages, état vide
  const blocks = $$(root, S.blocks);
  if (blocks.length) track(blocks, gsap.fromTo(blocks, { y: T.blockY, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: T.blockStagger, delay: d + T.blockDelay, clearProps: 'transform,opacity' }));

  // lignes de tableau et vignettes
  const rows = $$(root, S.rows);
  if (rows.length) track(rows, gsap.fromTo(rows, { y: T.rowY, opacity: 0 }, { y: 0, opacity: 1, duration: T.rowDuration, ease: 'power3.out', stagger: T.rowStagger, delay: d + T.blockDelay + 0.08, clearProps: 'transform,opacity' }));
  const imgs = $$(root, S.rowImg);
  if (imgs.length) track(imgs, gsap.fromTo(imgs, { scale: T.imgScale, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.7, ease: 'back.out(1.7)', stagger: T.rowStagger, delay: d + T.blockDelay + 0.16, clearProps: 'transform,opacity' }));

  // colonne de droite : panneaux puis jauge de membres
  const rail = $$(root, S.rail);
  if (rail.length) track(rail, gsap.fromTo(rail, { y: T.blockY, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: T.railStagger, delay: d + T.blockDelay + 0.1, clearProps: 'transform,opacity' }));
  $$(root, S.capbar).forEach((bar) => {
    const w = pct(bar);
    const e = track(bar, gsap.fromTo(bar, { width: '0%' }, { width: w + '%', duration: T.capDuration, ease: 'power3.out', delay: d + T.blockDelay + 0.3 }));
    e.restore = () => { bar.style.width = w + '%'; };
  });


  cards(root, d);
}

// cartes « proposition » : révélées une à une quand elles entrent dans l'écran
function cards(root, d) {
  const S = cfg.SEL, T = cfg.TUNE.enter, ST = cfg.ScrollTrigger;
  const list = $$(root, S.proposal);
  if (!list.length) return;
  const byCard = new Map();
  list.forEach((card) => {
    const seed = Math.random() * 6.283;
    const segs = [...card.querySelectorAll(S.voteSeg)];
    const widths = segs.map(pct);
    const nodes = legendNodes(card);
    const counts = nodes.map((n) => parseInt(n.data, 10));
    card.style.clipPath = liquidPoly(0, seed, T.cardWave);
    segs.forEach((s) => { s.style.width = '0%'; });
    nodes.forEach((n) => { n.data = '0' + n.data.replace(/^\d+/, ''); });
    const entry = track(card, null, (delay) => {
      if (entry.tween) return;
      const o = { p: 0 };
      const tl = gsap.timeline({ delay, onComplete: () => { card.style.clipPath = ''; } });
      tl.to(o, { p: 1, duration: T.cardDuration, ease: 'power3.out', onUpdate: () => { card.style.clipPath = liquidPoly(o.p, seed, T.cardWave); } }, 0);
      segs.forEach((s, i) => tl.fromTo(s, { width: '0%' }, { width: widths[i] + '%', duration: T.barDuration, ease: 'power3.out' }, T.barDelay + i * 0.08));
      nodes.forEach((n, i) => tl.add(countTo(n, 0, counts[i], T.countDuration, 0), T.barDelay + i * 0.08));
      entry.tween = tl;
    });
    entry.restore = () => {
      segs.forEach((s, i) => { s.style.width = widths[i] + '%'; });
      nodes.forEach((n, i) => { n.data = counts[i] + n.data.replace(/^\d+/, ''); });
    };
    byCard.set(card, entry);
  });

  const t0 = performance.now();
  const wait = () => Math.max(0, d + 0.2 - (performance.now() - t0) / 1000);
  const run = (batch) => { const w = wait(); batch.forEach((card, i) => { const e = byCard.get(card); if (e) e.run(w + i * T.cardStagger); }); };
  if (ST) triggers.push(...ST.batch(list, { start: T.cardStart, once: true, onEnter: run }));
  else run(list);
}

/* ---------- même route : réponse locale seulement ---------- */
function update(root, ctx) {
  if (cfg.env.reduced) return;
  const S = cfg.SEL, T = cfg.TUNE.enter;
  root.querySelectorAll(S.proposal).forEach((card) => {
    const id = card.getAttribute('aria-labelledby');
    const old = id && ctx.prevBars && ctx.prevBars.get(id);
    if (!old) return;
    const now = readCard(card);
    if (old.pour === now.pour && old.contre === now.contre && String(old.counts) === String(now.counts)) return;
    const segs = [...card.querySelectorAll(S.voteSeg)];
    const from = [old.pour, old.contre], to = [now.pour, now.contre];
    segs.forEach((s, i) => track(s, gsap.fromTo(s, { width: from[i] + '%' }, { width: to[i] + '%', duration: T.updateBar, ease: 'power3.inOut' })));
    legendNodes(card).forEach((n, i) => {
      if (old.counts[i] !== undefined && old.counts[i] !== now.counts[i]) track(n.parentNode, countTo(n, old.counts[i], now.counts[i], T.updateBar, 0));
    });
  });
  if (ctx.action && ctx.action.kind === 'tab') {
    const items = $$(root, `${S.proposal}, ${S.rows}, .stack > .empty-state`);
    if (items.length) track(items, gsap.fromTo(items, { y: 8, opacity: 0 }, { y: 0, opacity: 1, duration: T.swap, ease: 'power2.out', stagger: 0.04, clearProps: 'transform,opacity' }));
  }
}
