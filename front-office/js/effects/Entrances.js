// Yalla Quest · effets — entrées (changement de route uniquement) et retour local après un vote.
// Ne touche jamais au texte : SplitType est annulé (revert) dès la fin de l'animation du titre.

let ctx = null;
let tl = null;
let triggers = [];
let tweens = [];
let split = null;
let restore = [];          // fonctions qui remettent le DOM dans son état d'origine
let delayed = [];

const CLEAR = 'transform,opacity,clipPath,willChange';

function track(t) { tweens.push(t); return t; }

/* le premier nombre d'un nœud texte (« 3 pour », « 1 n'a pas voté ») */
function numberNode(span) {
  for (const n of span.childNodes) if (n.nodeType === 3 && /\d+/.test(n.nodeValue)) return n;
  return null;
}
function countTo(span, from, duration, delay) {
  const node = numberNode(span); if (!node) return;
  const final = node.nodeValue, to = parseInt(final.match(/\d+/)[0], 10);
  if (from === to) return;
  const o = { v: from };
  const put = (v) => { node.nodeValue = final.replace(/\d+/, String(Math.round(v))); };
  put(from);
  restore.push(() => { node.nodeValue = final; });
  track(ctx.gsap.to(o, { v: to, duration, delay, ease: 'power2.out', onUpdate: () => put(o.v), onComplete: () => { node.nodeValue = final; } }));
}
function growBar(el, from, duration, delay) {
  const final = el.style.width;
  if (!final || from === final) return;
  restore.push(() => { el.style.width = final; });
  track(ctx.gsap.fromTo(el, { width: from }, { width: final, duration, delay, ease: 'power3.out', onComplete: () => { el.style.width = final; } }));
}

/* bord supérieur ondulé qui monte : polygon() en % */
function liquidClip(p) {
  const N = 12, amp = 9 * Math.sin(Math.PI * p), base = (1 - p) * 104 - 4;
  let s = '';
  for (let i = 0; i <= N; i++) {
    const y = base + Math.sin(i * 1.25 + p * 7) * amp + Math.sin(i * 0.6 - p * 4) * amp * 0.5;
    s += `${((i / N) * 100).toFixed(1)}% ${y.toFixed(2)}%,`;
  }
  return `polygon(${s}100% 100%,0% 100%)`;
}

function revealCard(card, delay) {
  const { gsap, TUNE, SEL } = ctx, E = TUNE.entrance, o = { p: 0 };
  track(gsap.to(o, {
    p: 1, duration: E.cardDuration, delay, ease: 'power3.out',
    onUpdate: () => { card.style.clipPath = liquidClip(o.p); },
    onComplete: () => { card.style.clipPath = ''; delete card.dataset.fxEntering; },
  }));
  track(gsap.fromTo(card, { y: 28 }, { y: 0, duration: E.cardDuration, delay, ease: 'power3.out', clearProps: 'transform' }));
  card.querySelectorAll(SEL.voteSeg).forEach((i) => growBar(i, '0%', E.barDuration, delay + 0.25));
  card.querySelectorAll(SEL.legendItem).forEach((s) => countTo(s, 0, E.barDuration, delay + 0.25));
}

/** Changement de route : joue les entrées. c.delay = attente (s) pour laisser passer le rideau. */
export function init(root, c) {
  destroy();
  ctx = c;
  const { gsap, ScrollTrigger, SEL, TUNE } = c, E = TUNE.entrance, d0 = c.delay || 0;
  const q = (s) => [...root.querySelectorAll(s)];
  tl = gsap.timeline({ delay: d0 });
  const touched = [];
  restore.push(() => touched.length && gsap.set(touched, { clearProps: CLEAR }));

  // --- titre : les lettres montent, puis kicker / about / sub ---
  const h1 = root.querySelector(SEL.title);
  if (h1) {
    if (c.SplitType) {
      h1.setAttribute('aria-label', h1.textContent);
      split = new c.SplitType(h1, { types: 'lines,words,chars', lineClass: 'fx-line', wordClass: 'fx-word', charClass: 'fx-char' });
      const undo = () => { if (split) { split.revert(); split = null; h1.removeAttribute('aria-label'); } };
      restore.push(undo);
      if (split.chars && split.chars.length) {
        tl.from(split.chars, { yPercent: 110, duration: E.titleDuration, ease: 'power4.out', stagger: Math.min(E.titleStagger, 0.5 / split.chars.length), onComplete: undo }, 0);
      } else undo();
    } else {
      touched.push(h1);
      tl.from(h1, { y: 24, opacity: 0, duration: 0.6, ease: 'power3.out', clearProps: CLEAR }, 0);
    }
  }
  const after = q(`${SEL.kicker}, ${SEL.about}, ${SEL.sub}`);
  touched.push(...after);
  if (after.length) tl.from(after, { y: 14, opacity: 0, duration: 0.6, ease: 'power3.out', stagger: E.textStagger, clearProps: CLEAR }, h1 ? 0.22 : 0);
  // sans WebGL, l'emblème entre comme une simple image
  const emblem = root.querySelector(SEL.emblem);
  if (emblem && !emblem.classList.contains('fx-emblem-live') && !(c.env.webgl && !c.env.coarse)) {
    touched.push(emblem);
    tl.from(emblem, { scale: 0.8, opacity: 0, duration: 0.7, ease: 'power3.out', clearProps: CLEAR }, 0);
  }

  // --- tableaux : les lignes arrivent en cascade, les vignettes grossissent ---
  const rows = q(SEL.tableRow);
  if (rows.length) {
    touched.push(...rows);
    tl.from(rows, { y: 16, opacity: 0, duration: 0.55, ease: 'power3.out', stagger: E.rowStagger, clearProps: CLEAR }, 0.18);
    const thumbs = q(SEL.cellImg);
    touched.push(...thumbs);
    if (thumbs.length) tl.from(thumbs, { scale: 0.5, opacity: 0, duration: 0.6, ease: 'back.out(1.6)', stagger: E.rowStagger, clearProps: CLEAR }, 0.24);
  }

  // --- cartes de proposition : révélation liquide par le bas, au défilement ---
  const cards = q(SEL.proposal);
  if (cards.length && ScrollTrigger) {
    const startAt = performance.now() + (d0 + 0.12) * 1000;
    // clip-path (et non visibility) : les boutons restent atteignables au clavier, le focus fait défiler et déclenche la révélation
    cards.forEach((card) => { card.dataset.fxEntering = '1'; card.style.clipPath = 'inset(100% 0 0 0)'; });
    restore.push(() => cards.forEach((card) => { card.style.clipPath = ''; delete card.dataset.fxEntering; gsap.set(card, { clearProps: 'transform' }); }));
    triggers.push(...ScrollTrigger.batch(cards, {
      start: 'top 92%', once: true,
      onEnter: (batch) => {
        const wait = Math.max(0, (startAt - performance.now()) / 1000);
        batch.forEach((card, i) => revealCard(card, wait + i * E.cardStagger));
      },
    }));
  }

  // --- rail : les panneaux montent, la jauge de capacité se remplit ---
  const panels = q(SEL.railPanel);
  if (panels.length && ScrollTrigger) {
    const startAt = performance.now() + (d0 + 0.2) * 1000;
    gsap.set(panels, { opacity: 0, y: 20 });
    restore.push(() => gsap.set(panels, { clearProps: CLEAR }));
    triggers.push(...ScrollTrigger.batch(panels, {
      start: 'top 94%', once: true,
      onEnter: (batch) => {
        const wait = Math.max(0, (startAt - performance.now()) / 1000);
        batch.forEach((panel, i) => {
          track(gsap.to(panel, { opacity: 1, y: 0, duration: 0.6, delay: wait + i * E.panelStagger, ease: 'power3.out', clearProps: CLEAR }));
          panel.querySelectorAll(SEL.capFill).forEach((b) => growBar(b, '0%', E.barDuration, wait + i * E.panelStagger + 0.2));
        });
      },
    }));
  }
}

/**
 * Même route (vote, onglet, adhésion) : pas de rejeu d'entrée. Seule la carte qui a changé réagit :
 * sa barre passe de ses anciennes largeurs aux nouvelles et la légende recompte.
 * @param {Map<string,{widths:string[],nums:number[]}>} prev  état des cartes avant le re-rendu
 */
export function update(root, c, prev) {
  destroy();
  ctx = c;
  if (!prev || !prev.size) return;
  const { SEL, TUNE } = c;
  root.querySelectorAll(SEL.proposal).forEach((card) => {
    const before = prev.get(card.getAttribute('aria-labelledby'));
    if (!before) return;
    const segs = [...card.querySelectorAll(SEL.voteSeg)];
    if (!segs.some((s, i) => before.widths[i] !== undefined && parseFloat(before.widths[i]) !== parseFloat(s.style.width))) return;
    segs.forEach((s, i) => { if (before.widths[i] !== undefined) growBar(s, before.widths[i], TUNE.entrance.voteDuration, 0); });
    card.querySelectorAll(SEL.legendItem).forEach((s, i) => { if (before.nums[i] !== undefined) countTo(s, before.nums[i], TUNE.entrance.voteDuration, 0); });
  });
}

/** Lit, sur d'anciens nœuds détachés, l'état des barres de vote (largeurs et nombres affichés). */
export function snapshot(nodes, SEL) {
  const map = new Map();
  for (const n of nodes) {
    if (n.nodeType !== 1) continue;
    const cards = n.matches(SEL.proposal) ? [n] : n.querySelectorAll(SEL.proposal);
    cards.forEach((card) => {
      map.set(card.getAttribute('aria-labelledby'), {
        widths: [...card.querySelectorAll(SEL.voteSeg)].map((i) => i.style.width),
        nums: [...card.querySelectorAll(SEL.legendItem)].map((s) => { const t = numberNode(s); return t ? parseInt(t.nodeValue.match(/\d+/)[0], 10) : undefined; }),
      });
    });
  }
  return map;
}

export function destroy() {
  if (tl) { tl.kill(); tl = null; }
  tweens.forEach((t) => t.kill()); tweens = [];
  triggers.forEach((t) => t.kill()); triggers = [];
  delayed.forEach((t) => t.kill()); delayed = [];
  const r = restore; restore = [];
  r.forEach((fn) => { try { fn(); } catch (e) { /* nœud déjà retiré */ } });
  split = null;
}
