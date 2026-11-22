// Encre liquide, limitée à des « zones » (jamais en plein écran par-dessus les commandes).
// Un seul WebGLRenderer, un seul canvas fixe (.fx-ink, pointer-events: none) placé DERRIÈRE le contenu :
// on ne dessine que dans le rectangle des zones actives (scissor), ailleurs il reste transparent.
//   .title-row  -> « solid » : encre var(--accent) derrière le titre (plafonnée sous le texte, absente sous .actions)
//   .proposal .art -> « reveal » : l'encre découvre une bichromie var(--night)/var(--accent) de la même image.
//     L'image est sous le fond ivoire de la carte : ce rendu est fait hors écran dans le même canvas, puis copié
//     dans un petit canvas 2D posé sur l'image (.fx-art-ink), qui suit donc l'inclinaison de la carte.
// Simulation par zone : deux render targets (ping-pong) à 0,35 de la résolution, taches au passage de la souris
// (taille selon la vitesse), atténuation ~0,96 par image, advection par curl noise, gravité (gouttes).
// Souris immobile : l'encre fond en ~1,5 s. Écran tactile / étroit : pas de suivi, une giclée au toucher.

import { QUAD_VERT, SIM_FRAG, SOLID_FRAG, REVEAL_FRAG } from './shaders.js';

const MAX_SPLATS = 8, MAX_RECTS = 16;
let cfg = null, T3 = null, stage = null;
let quad = null, scene = null, camera = null, simMat = null, solidMat = null, revealMat = null;
let rtType = null, rtScale = 1;
let zones = [];
const pool = new Map();          // simulations gardées entre deux rendus de la même route (clé : zone)
const texCache = new Map();
const colors = {};
let last = null, dripClock = 0;

/* ---------- couleurs : lues dans les variables CSS du template ---------- */
const probe = document.createElement('canvas').getContext('2d');
function cssColor(name) {
  const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  probe.fillStyle = '#000';
  if (v) probe.fillStyle = v;
  const s = probe.fillStyle;
  if (s[0] === '#') return [1, 3, 5].map((i) => parseInt(s.slice(i, i + 2), 16) / 255);
  const m = s.match(/[\d.]+/g) || [0, 0, 0];
  return [m[0] / 255, m[1] / 255, m[2] / 255];
}
function readColors() {
  ['--accent', '--night', '--ivory', '--ground'].forEach((n) => { colors[n] = new T3.Vector3(...cssColor(n)); });
}

/* ---------- scène partagée ---------- */
export function createStage(THREE, c) {
  cfg = c; T3 = THREE;
  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: false, premultipliedAlpha: true, powerPreference: 'high-performance' });
  } catch (err) { console.warn('[Yalla Quest fx] WebGL indisponible :', err); return null; }
  const canvas = renderer.domElement;
  canvas.className = 'fx-ink';
  canvas.setAttribute('aria-hidden', 'true');
  document.body.prepend(canvas);
  renderer.autoClear = false;
  renderer.setClearColor(0x000000, 0);
  const ext = renderer.extensions;
  const floatOk = ext.has('EXT_color_buffer_float') || ext.has('EXT_color_buffer_half_float');
  rtType = floatOk ? THREE.HalfFloatType : THREE.UnsignedByteType;
  rtScale = floatOk ? 1 : 3;     // en 8 bits on stocke densité / 3
  readColors();

  camera = new THREE.Camera();
  scene = new THREE.Scene();
  const vec4s = (n) => Array.from({ length: n }, () => new THREE.Vector4());
  simMat = new THREE.ShaderMaterial({
    vertexShader: QUAD_VERT, fragmentShader: SIM_FRAG, defines: { MAX_SPLATS },
    uniforms: {
      uPrev: { value: null }, uAspect: { value: 1 }, uTime: { value: 0 }, uDt: { value: 0.016 }, uFade: { value: 0.96 },
      uNoise: { value: 0.1 }, uNoiseScale: { value: 2 }, uDrip: { value: 0.2 }, uDrag: { value: 0 }, uScale: { value: rtScale },
      uSplats: { value: vec4s(MAX_SPLATS) }, uSplatCount: { value: 0 },
    },
    depthTest: false, depthWrite: false, blending: THREE.NoBlending,
  });
  solidMat = new THREE.ShaderMaterial({
    vertexShader: QUAD_VERT, fragmentShader: SOLID_FRAG, defines: { MAX_RECTS },
    uniforms: {
      uInk: { value: null }, uTexel: { value: new THREE.Vector2() }, uSize: { value: new THREE.Vector2() },
      uColor: { value: colors['--accent'] }, uHighlight: { value: colors['--ivory'] },
      uThreshold: { value: 0.3 }, uSoft: { value: 0.07 }, uOpacity: { value: 1 }, uFeather: { value: 10 }, uEdge: { value: 14 }, uScale: { value: rtScale },
      uRects: { value: vec4s(MAX_RECTS) }, uCaps: { value: new Array(MAX_RECTS).fill(1) }, uRectCount: { value: 0 },
    },
    depthTest: false, depthWrite: false, transparent: true, premultipliedAlpha: true,
  });
  revealMat = new THREE.ShaderMaterial({
    vertexShader: QUAD_VERT, fragmentShader: REVEAL_FRAG,
    uniforms: {
      uInk: { value: null }, uMap: { value: null }, uUvScale: { value: new THREE.Vector2(1, 1) }, uUvOffset: { value: new THREE.Vector2() },
      uDark: { value: colors['--night'] }, uLight: { value: colors['--accent'] }, uThreshold: { value: 0.3 }, uSoft: { value: 0.07 }, uScale: { value: rtScale },
    },
    depthTest: false, depthWrite: false, blending: THREE.NoBlending,
  });
  quad = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), simMat);
  quad.frustumCulled = false;
  scene.add(quad);

  stage = {
    THREE, renderer, canvas, dead: false, vw: 0, vh: 0, pr: 1, layers: [], dirty: false,
    addLayer(layer) { this.layers.push(layer); },
    frame, kill,
  };
  canvas.addEventListener('webglcontextlost', (e) => { e.preventDefault(); kill(); }, false);
  resize();
  return stage;
}

function kill() {
  if (!stage || stage.dead) return;
  stage.dead = true;
  zones.forEach((z) => { if (z.overlay) z.overlay.remove(); });
  zones = [];
  stage.layers.forEach((l) => l.lost && l.lost());
  pool.forEach((s) => { s.a.dispose(); s.b.dispose(); });
  pool.clear();
  stage.canvas.remove();
  try { stage.renderer.dispose(); } catch (e) { /* rien */ }
}

function resize() {
  const vw = document.documentElement.clientWidth, vh = innerHeight;
  const pr = Math.min(devicePixelRatio || 1, 2);
  if (vw === stage.vw && vh === stage.vh && pr === stage.pr) return;
  stage.vw = vw; stage.vh = vh; stage.pr = pr;
  stage.renderer.setPixelRatio(pr);
  stage.renderer.setSize(vw, vh, false);
}

/* ---------- zones ---------- */
export function init(root, ctx) {
  if (!stage || stage.dead || cfg.env.reduced || !root) return;
  readColors();
  if (ctx && ctx.mode === 'enter') { pool.forEach((s) => { s.a.dispose(); s.b.dispose(); }); pool.clear(); }   // nouvelle route : encre neuve
  const S = cfg.SEL, I = cfg.TUNE.ink;
  zones = [];
  const title = root.querySelector(S.titleRow);
  if (title) {
    const mb = parseFloat(getComputedStyle(title).marginBottom) || 0;
    zones.push(makeZone('title', title, {
      reach: Math.max(0, Math.min(I.titleReach, mb - 4)),
      emblem: title.querySelector(S.emblem),
      texts: [['h1', I.capTitle], ['.sub', I.capSub], ['.kicker', I.capMuted], ['.about', I.capMuted]]
        .map(([s, cap]) => ({ el: title.querySelector(s), cap })).filter((t) => t.el),
      exclude: [...title.querySelectorAll(S.actions)],
      opacity: 1,
    }));
  }
  root.querySelectorAll(S.proposal).forEach((card) => {
    const art = card.querySelector(S.art);
    if (!art) return;
    const overlay = document.createElement('canvas');
    overlay.className = 'fx-art-ink';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.width = overlay.height = 1;
    card.appendChild(overlay);
    zones.push(makeZone('art:' + (card.getAttribute('aria-labelledby') || zones.length), art, { mode: 'reveal', overlay, g: overlay.getContext('2d'), card }));
  });
}

export function destroy() {
  zones.forEach((z) => { if (z.overlay) z.overlay.remove(); });
  zones = [];
}

function makeZone(key, el, o) {
  return Object.assign({ key, el, mode: 'solid', reach: 0, top: 0, texts: [], exclude: [], opacity: 1, rects: null, rectsAge: 1e9 }, o);
}
function zoneRect(z) {
  const r = (z.overlay || z.el).getBoundingClientRect();
  return { left: r.left, top: r.top - z.top, right: r.right, bottom: r.bottom + z.reach, width: r.width, height: r.height + z.top + z.reach };
}

/* ---------- simulations (ping-pong) ---------- */
function getSim(z, w, h) {
  const I = cfg.TUNE.ink;
  const scale = z.mode === 'reveal' ? Math.min(0.5, I.simScale * 1.4) : I.simScale;
  const sw = Math.max(8, Math.min(I.maxSim, Math.round(w * scale)));
  const sh = Math.max(8, Math.min(I.maxSim, Math.round(h * scale)));
  let s = pool.get(z.key);
  if (s && (Math.abs(s.w - sw) > sw * 0.1 || Math.abs(s.h - sh) > sh * 0.1)) { s.a.dispose(); s.b.dispose(); pool.delete(z.key); s = null; }
  if (!s) {
    const opt = { type: rtType, format: T3.RGBAFormat, minFilter: T3.LinearFilter, magFilter: T3.LinearFilter, depthBuffer: false, stencilBuffer: false, wrapS: T3.ClampToEdgeWrapping, wrapT: T3.ClampToEdgeWrapping };
    s = { a: new T3.WebGLRenderTarget(sw, sh, opt), b: new T3.WebGLRenderTarget(sw, sh, opt), w: sw, h: sh, last: -1e9, live: false, splats: [] };
    pool.set(z.key, s);
    clearSim(s);
  }
  return s;
}
function clearSim(s) {
  const r = stage.renderer;
  [s.a, s.b].forEach((rt) => { r.setRenderTarget(rt); r.clear(true, false, false); });
  r.setRenderTarget(null);
  s.live = false; s.splats = []; s.last = -1e9;
}
function step(s, w, h, dt, time, drag) {
  const I = cfg.TUNE.ink, u = simMat.uniforms;
  u.uPrev.value = s.a.texture;
  u.uAspect.value = w / Math.max(1, h);
  u.uTime.value = time; u.uDt.value = dt;
  u.uFade.value = Math.pow(I.fade, dt * 60);
  u.uNoise.value = I.noise; u.uNoiseScale.value = I.noiseScale; u.uDrip.value = I.drip; u.uDrag.value = drag;
  const n = Math.min(MAX_SPLATS, s.splats.length);
  for (let i = 0; i < n; i++) u.uSplats.value[i].copy(s.splats[i]);
  u.uSplatCount.value = n;
  s.splats = s.splats.slice(n);
  quad.material = simMat;
  stage.renderer.setRenderTarget(s.b);
  stage.renderer.render(scene, camera);
  const t = s.a; s.a = s.b; s.b = t;
}
function addSplat(s, z, r, x, y, radiusPx, amount, time) {
  if (s.splats.length >= MAX_SPLATS * 2) return;
  s.splats.push(new T3.Vector4((x - r.left) / r.width, 1 - (y - r.top) / r.height, radiusPx / Math.max(1, r.height), amount));
  s.last = time; s.live = true;
}
const inside = (r, x, y) => x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;

/* ---------- rectangles de plafond (texte, boutons) en coordonnées de zone ---------- */
function lineRects(el) {
  if (el.querySelector('.char, .word')) return [el.getBoundingClientRect()];   // titre découpé : la boîte suffit
  const range = document.createRange();
  range.selectNodeContents(el);
  const list = [...range.getClientRects()].filter((q) => q.width > 0 && q.height > 0);
  return list.length ? list : [el.getBoundingClientRect()];
}
function updateRects(z, r) {
  const out = [];
  z.texts.forEach((t) => lineRects(t.el).forEach((q) => out.push({ q, cap: t.cap, pad: 4 })));
  z.exclude.forEach((el) => out.push({ q: el.getBoundingClientRect(), cap: 0, pad: 10 }));
  let list = out;
  if (list.length > MAX_RECTS) list = z.texts.map((t) => ({ q: t.el.getBoundingClientRect(), cap: t.cap, pad: 4 })).concat(z.exclude.map((el) => ({ q: el.getBoundingClientRect(), cap: 0, pad: 10 }))).slice(0, MAX_RECTS);
  z.rects = list.map(({ q, cap, pad }) => ({ x0: q.left - r.left - pad, y0: q.top - r.top - pad, x1: q.right - r.left + pad, y1: q.bottom - r.top + pad, cap }));
  z.rectsAge = 0;
}

/* ---------- textures des images de carte ---------- */
function textureFor(img) {
  const src = img.currentSrc || img.src;
  let t = texCache.get(src);
  if (t) return t.image.complete && t.image.naturalWidth ? t : null;
  const image = new Image();
  image.decoding = 'async';
  image.src = src;
  t = new T3.Texture(image);
  t.minFilter = T3.LinearFilter; t.generateMipmaps = false;
  const ready = () => { t.needsUpdate = true; };
  if (image.complete && image.naturalWidth) ready(); else image.addEventListener('load', ready, { once: true });
  texCache.set(src, t);
  return image.complete && image.naturalWidth ? t : null;
}
function cover(uScale, uOffset, boxW, boxH, iw, ih) {
  const bA = boxW / Math.max(1, boxH), iA = iw / Math.max(1, ih);
  if (bA > iA) { uScale.set(1, iA / bA); uOffset.set(0, (1 - iA / bA) / 2); }
  else { uScale.set(bA / iA, 1); uOffset.set((1 - bA / iA) / 2, 0); }
}

/* ---------- une image ---------- */
function frame(dt, time) {
  if (stage.dead || document.hidden) return;
  resize();
  const I = cfg.TUNE.ink, P = cfg.pointer, renderer = stage.renderer;
  const vw = stage.vw, vh = stage.vh;

  // geste de la souris depuis l'image précédente -> taches dans les zones traversées
  const follow = cfg.env.fine && P.inside && P.type === 'mouse';
  const moves = [];
  if (follow && last) {
    const dx = P.x - last.x, dy = P.y - last.y, dist = Math.hypot(dx, dy);
    if (dist > 0.5) {
      const speed = dist / Math.max(dt, 1 / 240);
      const rad = Math.min(I.radiusMax, Math.max(I.radiusMin, I.radius + speed * I.radiusSpeed));
      const amt = Math.min(I.amountMax, I.amount + speed * I.amountSpeed);
      const n = Math.max(1, Math.min(6, Math.ceil(dist / (rad * 0.5))));
      for (let i = 1; i <= n; i++) moves.push({ x: last.x + (dx * i) / n, y: last.y + (dy * i) / n, rad, amt: amt / Math.sqrt(n) });
    }
  }
  last = follow ? { x: P.x, y: P.y } : null;
  const taps = P.taps.splice(0);
  dripClock += dt;

  const visible = [];
  for (const z of zones) {
    if (!z.el.isConnected) continue;
    const r = zoneRect(z);
    if (r.width < 4 || r.height < 4) continue;
    const onScreen = r.bottom > 0 && r.top < vh && r.right > 0 && r.left < vw;
    const myMoves = onScreen ? moves.filter((m) => inside(r, m.x, m.y)) : [];
    const myTaps = onScreen ? taps.filter((t) => inside(r, t.x, t.y)) : [];
    let s = pool.get(z.key);
    if (!s && !myMoves.length && !myTaps.length) continue;      // rien à dessiner : pas de simulation allouée
    s = getSim(z, r.width, r.height);
    for (const m of myMoves) addSplat(s, z, r, m.x, m.y, m.rad, m.amt, time);
    for (const t of myTaps) {
      for (let k = 0; k < I.tapBurst; k++) {
        const a = Math.random() * 6.283, d = Math.random() * 26;
        addSplat(s, z, r, t.x + Math.cos(a) * d, t.y + Math.sin(a) * d, 22 + Math.random() * 26, 0.9, time);
      }
    }
    // l'encre du titre coule aussi de derrière l'emblème
    if (myMoves.length && z.emblem && dripClock > I.emblemDripEvery) {
      const e = z.emblem.getBoundingClientRect();
      if (e.width > 0) addSplat(s, z, r, e.left + e.width * (0.25 + Math.random() * 0.5), e.bottom - e.height * 0.12, e.width * 0.16, 0.9, time);
    }
    if (s.live && time - s.last > I.life) clearSim(s);      // fondue : on arrête de la calculer
    if (s.live && onScreen) visible.push({ z, r, s });
  }
  if (dripClock > I.emblemDripEvery) dripClock = 0;

  const layers = stage.layers.filter((l) => l.active());
  if (!visible.length && !layers.length) {
    if (stage.dirty) { renderer.setRenderTarget(null); renderer.setScissorTest(false); renderer.clear(); stage.dirty = false; }
    zones.forEach((z) => { if (z.overlay && z.painted) { z.g.clearRect(0, 0, z.overlay.width, z.overlay.height); z.painted = false; } });
    return;
  }

  // traînée du défilement : l'encre « retarde » un peu sur le contenu (px/s -> hauteurs de zone par seconde)
  const dragPx = -cfg.getVelocity() * I.drag * 60;
  visible.forEach(({ z, r, s }) => step(s, r.width, r.height, dt, time, z.mode === 'reveal' ? 0 : Math.max(-1.5, Math.min(1.5, dragPx / Math.max(1, r.height)))));

  renderer.setRenderTarget(null);
  renderer.setScissorTest(false);
  renderer.clear();
  stage.dirty = true;

  // 1) « reveal » : rendu hors écran dans le coin haut gauche du canvas, copié dans le canvas 2D de la carte
  for (const { z, s } of visible) {
    if (z.mode !== 'reveal') continue;
    const tex = textureFor(z.el);
    const w = Math.min(z.overlay.offsetWidth, vw), h = Math.min(z.overlay.offsetHeight, vh);
    if (!tex || w < 2 || h < 2) continue;
    const pw = Math.round(w * stage.pr), ph = Math.round(h * stage.pr);
    if (z.overlay.width !== pw || z.overlay.height !== ph) { z.overlay.width = pw; z.overlay.height = ph; }
    const u = revealMat.uniforms;
    u.uInk.value = s.a.texture; u.uMap.value = tex;
    u.uThreshold.value = I.threshold; u.uSoft.value = I.soft;
    cover(u.uUvScale.value, u.uUvOffset.value, w, h, tex.image.naturalWidth, tex.image.naturalHeight);
    renderer.setViewport(0, vh - h, w, h);
    renderer.setScissor(0, vh - h, w, h);
    renderer.setScissorTest(true);
    renderer.clear(true, false, false);
    quad.material = revealMat;
    renderer.render(scene, camera);
    z.g.clearRect(0, 0, pw, ph);
    z.g.drawImage(stage.canvas, 0, 0, pw, ph, 0, 0, pw, ph);
    z.painted = true;
    renderer.clear(true, false, false);
  }
  zones.forEach((z) => { if (z.overlay && z.painted && !visible.some((v) => v.z === z)) { z.g.clearRect(0, 0, z.overlay.width, z.overlay.height); z.painted = false; } });

  // 2) « solid » : directement dans le canvas fixe, au rectangle de la zone
  for (const { z, r, s } of visible) {
    if (z.mode !== 'solid') continue;
    if (!z.rects || ++z.rectsAge > 12) updateRects(z, r);
    const sx = Math.max(0, r.left), sy = Math.max(0, vh - r.bottom);
    const sw = Math.min(vw, r.right) - sx, sh = Math.min(vh, vh - r.top) - sy;
    if (sw <= 0 || sh <= 0) continue;
    const u = solidMat.uniforms;
    u.uInk.value = s.a.texture;
    u.uTexel.value.set(1 / s.w, 1 / s.h);
    u.uSize.value.set(r.width, r.height);
    u.uColor.value = colors['--accent']; u.uHighlight.value = colors['--ivory'];
    u.uThreshold.value = I.threshold; u.uSoft.value = I.soft; u.uOpacity.value = z.opacity;
    u.uFeather.value = I.feather; u.uEdge.value = I.edge;
    const n = Math.min(MAX_RECTS, z.rects.length);
    for (let i = 0; i < n; i++) { const q = z.rects[i]; u.uRects.value[i].set(q.x0, q.y0, q.x1, q.y1); u.uCaps.value[i] = q.cap; }
    u.uRectCount.value = n;
    renderer.setViewport(r.left, vh - r.bottom, r.width, r.height);
    renderer.setScissor(sx, sy, sw, sh);
    renderer.setScissorTest(true);
    quad.material = solidMat;
    renderer.render(scene, camera);
  }

  // 3) calques (emblème 3D), dessinés par-dessus l'encre
  renderer.setScissorTest(false);
  layers.forEach((l) => l.draw(dt, time));
}
