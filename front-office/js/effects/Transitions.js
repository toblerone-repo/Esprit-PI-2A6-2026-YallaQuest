// Yalla Quest · effets — transition de page : un rideau d'encre qui coule sur toute la largeur de l'écran.
// L'ancienne page reste affichée (ses nœuds détachés sont re-posés dans un calque inerte), l'encre
// --accent la recouvre de haut en bas avec un bord qui goutte, suivie d'une bande --ground, puis la
// nouvelle page apparaît derrière. Le menu latéral ne bouge jamais ; rien n'est cliquable dans le calque.

let ctx = null;
let layer = null;
let tween = null;

function drips(width, count, rnd) {
  // colonnes de gouttes : position, largeur, longueur, retard
  return Array.from({ length: count }, () => ({ x: rnd() * width, w: 26 + rnd() * 54, len: 0.35 + rnd() * 0.65, lag: rnd() * 0.5 }));
}

export function isRunning() { return !!tween; }

/**
 * @param {Node[]} oldNodes  anciens enfants de #canvas (déjà détachés par render())
 * @param {number} scrollY   position de défilement de l'ancienne page
 */
export function play(root, c, oldNodes, scrollY) {
  ctx = c;
  kill();
  const { gsap, TUNE } = c;
  const r = root.getBoundingClientRect();
  // le rideau couvre toute la largeur de l'écran, du bord gauche au bord droit (sans la barre de défilement),
  // sous la barre du haut ; l'ancienne page garde sa place et sa largeur à l'intérieur
  const top = Math.max(0, r.top), W = document.documentElement.clientWidth, H = window.innerHeight - top;
  if (W < 10 || H < 10 || r.width < 10) return null;

  layer = document.createElement('div');
  layer.className = 'fx-wipe';
  layer.setAttribute('aria-hidden', 'true');
  layer.inert = true;
  layer.style.cssText = `left:0;top:${top}px;width:${W}px;height:${H}px`;

  const old = document.createElement('div');
  old.className = 'fx-wipe-old';
  const ghost = document.createElement('div');
  ghost.className = root.className + ' fx-wipe-ghost';
  ghost.style.top = `${-scrollY}px`;
  ghost.style.left = `${r.left}px`;
  ghost.style.width = `${r.width}px`;
  for (const n of oldNodes) ghost.appendChild(n);
  // l'ancienne page ne doit plus porter d'état d'effets ni d'identifiants en double
  ghost.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id'));
  old.appendChild(ghost);

  const ns = 'http://www.w3.org/2000/svg';
  const svg = document.createElementNS(ns, 'svg');
  svg.setAttribute('class', 'fx-wipe-svg');
  svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
  svg.setAttribute('preserveAspectRatio', 'none');
  const pGround = document.createElementNS(ns, 'path'); pGround.setAttribute('class', 'fx-wipe-ground');
  const pInk = document.createElementNS(ns, 'path'); pInk.setAttribute('class', 'fx-wipe-ink');
  svg.append(pGround, pInk);
  layer.append(old, svg);
  document.body.appendChild(layer);

  let seed = 7 + Math.floor(Math.random() * 1000);
  const rnd = () => { seed = (seed * 16807) % 2147483647; return seed / 2147483647; };
  const T = TUNE.wipe;
  const lead = drips(W, Math.max(5, Math.round(W / 150)), rnd);
  const trail = drips(W, Math.max(4, Math.round(W / 220)), rnd);
  const ph = [rnd() * 6.28, rnd() * 6.28, rnd() * 6.28];
  const step = Math.max(10, W / 90), n = Math.ceil(W / step);
  const ease = gsap.parseEase('power2.inOut');
  const seg = (t, a, b) => ease(Math.max(0, Math.min(1, (t - a) / (b - a))));
  const y0 = -T.dripLength - 30, y1 = H + 30;
  const state = { t: 0 };

  const draw = () => {
    const t = state.t;
    const yL = y0 + (y1 - y0) * seg(t, 0, 0.7);        // bord d'attaque (encre)
    const yM = y0 + (y1 - y0) * seg(t, 0.13, 0.85);    // encre -> fond
    const yT = y0 + (y1 - y0) * seg(t, 0.28, 1);       // fond -> nouvelle page
    const grow = Math.sin(Math.min(1, t / 0.7) * Math.PI); // les gouttes s'allongent puis se rétractent
    const L = [], M = [], Tt = [];
    for (let i = 0; i <= n; i++) {
      const x = Math.min(W, i * step);
      let l = yL + (Math.sin(x * 0.011 + ph[0] + t * 5) * 0.5 + 0.5) * T.wave;
      for (const d of lead) l += Math.exp(-(((x - d.x) / d.w) ** 2)) * d.len * T.dripLength * Math.max(0, grow - d.lag * 0.3);
      let m = yM + Math.sin(x * 0.014 + ph[1] - t * 4) * T.wave * 0.6;
      for (const d of trail) m += Math.exp(-(((x - d.x) / d.w) ** 2)) * d.len * T.dripLength * 0.5 * grow;
      let tt = yT + Math.sin(x * 0.009 + ph[2] + t * 3) * T.wave * 0.5;
      m = Math.min(m, l);
      tt = Math.min(tt, m, yL); // jamais sous la ligne de découpe de l'ancienne page
      L.push(l); M.push(m); Tt.push(tt);
    }
    const band = (A, B) => {
      let d = '';
      for (let i = 0; i <= n; i++) d += `${i ? 'L' : 'M'}${Math.min(W, i * step).toFixed(1)} ${A[i].toFixed(1)}`;
      for (let i = n; i >= 0; i--) d += `L${Math.min(W, i * step).toFixed(1)} ${B[i].toFixed(1)}`;
      return d + 'Z';
    };
    pInk.setAttribute('d', band(M, L));
    pGround.setAttribute('d', band(Tt, M));
    old.style.clipPath = `inset(${Math.max(0, yL)}px 0 0 0)`;
  };

  draw();
  tween = gsap.to(state, { t: 1, duration: T.duration, ease: 'none', onUpdate: draw, onComplete: kill });
  return tween;
}

/** Repli « mouvement réduit » : simple fondu d'opacité de 150 ms. */
export function fade(root, c) {
  ctx = c;
  if (!c.gsap) return;
  c.gsap.fromTo(root, { opacity: 0 }, { opacity: 1, duration: 0.15, ease: 'none', clearProps: 'opacity', overwrite: true });
}

export function kill() {
  if (tween) { tween.kill(); tween = null; }
  if (layer) { layer.remove(); layer = null; }
}

export function init() {}
export function destroy() { kill(); }
