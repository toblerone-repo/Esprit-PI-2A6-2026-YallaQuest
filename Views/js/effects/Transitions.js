// Transition de route : une vague d'encre lilas (var(--accent)) puis noire (var(--ground)) coule de haut en bas
// sur #canvas, avec un bord qui fond (gouttes) au lieu d'une ligne droite. La barre de navigation ne bouge pas.
// Purement décoratif : pointer-events: none, rien n'est retardé (le nouveau contenu est déjà dans le DOM).

let cfg = null, gsap = null;
let el = null, layerA = null, layerG = null, tween = null, drips = [];

export function setup(c) { cfg = c; gsap = c.gsap; }
export function init() { /* rien à rattacher au contenu de #canvas */ }
export function destroy() {
  if (tween) tween.kill();
  tween = null;
  if (el) el.classList.remove('on');
}

function ensure() {
  if (el) return;
  el = document.createElement('div');
  el.className = 'fx-wipe';
  el.setAttribute('aria-hidden', 'true');
  layerA = document.createElement('i'); layerA.className = 'a';
  layerG = document.createElement('i'); layerG.className = 'g';
  el.append(layerA, layerG);
  document.body.appendChild(el);
}

function place() {
  const S = cfg.SEL;
  // la vague part du haut de #canvas : la barre de navigation au-dessus reste visible
  const canvas = document.querySelector(S.canvas).getBoundingClientRect();
  Object.assign(el.style, { left: Math.max(0, canvas.left) + 'px', top: Math.max(0, canvas.top) + 'px', right: '0px', bottom: '0px' });
}

const clamp01 = (t) => (t < 0 ? 0 : t > 1 ? 1 : t);
const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);

// un bord : position de base (en % de la hauteur) + ondulation + gouttes qui s'allongent puis se résorbent
function edge(p, start, len, dripScale) {
  const W = cfg.TUNE.wipe;
  const k = clamp01((p - start) / len);
  const base = -30 + 165 * easeInOut(k);
  const grow = Math.sin(Math.PI * k);
  const out = new Array(W.points + 1);
  for (let i = 0; i <= W.points; i++) {
    const x = i / W.points;
    let y = base + Math.sin(x * 7 + p * 9) * W.wave * grow + Math.sin(x * 17 - p * 6) * W.wave * 0.4 * grow;
    for (const dp of drips) y += W.dripDepth * dripScale * grow * dp.a * Math.exp(-(((x - dp.c) / dp.w) ** 2));
    out[i] = y;
  }
  return out;
}
function band(top, bottom) {
  const n = top.length - 1, pts = [];
  for (let i = 0; i <= n; i++) pts.push(`${((i / n) * 100).toFixed(2)}% ${top[i].toFixed(2)}%`);
  for (let i = n; i >= 0; i--) pts.push(`${((i / n) * 100).toFixed(2)}% ${Math.max(bottom[i], top[i]).toFixed(2)}%`);
  return `polygon(${pts.join(',')})`;
}
function draw(p) {
  const lead = edge(p, 0, 0.5, 1);            // bord avant de l'encre lilas, gouttes vers le bas
  const trailA = edge(p, 0.16, 0.52, 0.55);   // arrière de l'encre lilas = avant de l'encre noire
  const trailG = edge(p, 0.34, 0.62, 0.45);   // arrière de l'encre noire : le contenu apparaît derrière
  layerA.style.clipPath = band(trailA, lead);
  layerG.style.clipPath = band(trailG, trailA);
}

// renvoie le délai (s) après lequel les entrées peuvent commencer
export function play() {
  if (!cfg || cfg.env.reduced) return 0;
  ensure();
  place();
  const W = cfg.TUNE.wipe;
  drips = Array.from({ length: W.drips }, () => ({ c: Math.random(), w: 0.018 + Math.random() * 0.035, a: 0.45 + Math.random() * 0.55 }));
  if (tween) tween.kill();
  el.classList.add('on');
  draw(0);
  const o = { p: 0 };
  tween = gsap.to(o, { p: 1, duration: W.duration, ease: 'none', onUpdate: () => draw(o.p), onComplete: () => { el.classList.remove('on'); tween = null; } });
  return W.revealAt;
}
