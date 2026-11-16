// Yalla Quest · effets — encre liquide, limitée à des « zones ».
// Un seul canvas Three.js fixe (pointer-events: none), un seul renderer. Chaque zone a sa petite
// simulation ping-pong ; l'affichage est découpé au rectangle de la zone (viewport + scissor).
// Le même canvas sert à FloatingEmblem via addPass().
import { quadVert, simFrag, solidFrag, revealFrag, glassFrag } from './shaders.js';

let stage = null;          // { THREE, renderer, canvas, scene, camera, mesh, sim, solid, reveal, ... }
let ctx = null;
const zones = new Map();   // clé -> zone
const passes = new Set();  // passes supplémentaires (emblème)
const texCache = new Map();// src d'image -> THREE.Texture
const pointer = { x: -1, y: -1, inside: false, fine: true };
let dirty = true;          // quelque chose a bougé : il faut re-mesurer
let cleared = true;
let paused = false;
let lastTime = 0;
let bound = false;
let lastScrollY = null;     // pour que l'encre plein écran suive le contenu au défilement

/* ---------- couleurs : variables CSS -> uniforms ---------- */
let probe = null;
export function cssColor(name) {
  const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  if (!probe) probe = document.createElement('canvas').getContext('2d');
  let out = [1, 1, 1];
  if (probe && raw) {
    probe.fillStyle = '#000'; probe.fillStyle = raw;
    const v = probe.fillStyle; // normalisé : #rrggbb ou rgba(r, g, b, a)
    if (v[0] === '#') out = [1, 3, 5].map((i) => parseInt(v.slice(i, i + 2), 16) / 255);
    else { const m = v.match(/[\d.]+/g); if (m) out = m.slice(0, 3).map((n) => +n / 255); }
  }
  return out;
}

/* ---------- scène partagée ---------- */
function createStage(THREE) {
  const canvas = document.createElement('canvas');
  canvas.className = 'fx-gl';
  canvas.setAttribute('aria-hidden', 'true');
  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, premultipliedAlpha: true, powerPreference: 'high-performance' });
  } catch (e) { return null; }
  if (!renderer.getContext()) return null;
  renderer.setClearColor(0x000000, 0);
  renderer.autoClear = false;
  document.body.appendChild(canvas);

  const premult = { transparent: true, depthTest: false, depthWrite: false, blending: THREE.CustomBlending, blendSrc: THREE.OneFactor, blendDst: THREE.OneMinusSrcAlphaFactor, blendSrcAlpha: THREE.OneFactor, blendDstAlpha: THREE.OneMinusSrcAlphaFactor };
  const sim = new THREE.ShaderMaterial({
    vertexShader: quadVert, fragmentShader: simFrag, depthTest: false, depthWrite: false, blending: THREE.NoBlending,
    uniforms: {
      uPrev: { value: null }, uSize: { value: new THREE.Vector2(1, 1) }, uTexel: { value: new THREE.Vector2(1, 1) },
      uSeg: { value: [0, 1, 2, 3].map(() => new THREE.Vector4()) }, uSplat: { value: [0, 1, 2, 3].map(() => new THREE.Vector2()) },
      uFade: { value: 0.96 }, uSub: { value: 0.003 }, uNoise: { value: 1 }, uScale: { value: 0.01 }, uDrip: { value: 0 },
      uDiffuse: { value: 0.2 }, uEdge: { value: 0 }, uTime: { value: 0 }, uShift: { value: new THREE.Vector2() },
    },
  });
  const solid = new THREE.ShaderMaterial({
    vertexShader: quadVert, fragmentShader: solidFrag, ...premult,
    uniforms: {
      uInk: { value: null }, uSize: { value: new THREE.Vector2(1, 1) }, uColor: { value: new THREE.Vector3(1, 1, 1) },
      uOpacity: { value: 0.3 }, uRim: { value: 0.8 }, uThreshold: { value: 0.3 }, uSoft: { value: 0.05 }, uTime: { value: 0 },
    },
  });
  const reveal = new THREE.ShaderMaterial({
    vertexShader: quadVert, fragmentShader: revealFrag, ...premult,
    uniforms: {
      uInk: { value: null }, uImage: { value: null }, uSize: { value: new THREE.Vector2(1, 1) }, uCover: { value: new THREE.Vector2(1, 1) },
      uDark: { value: new THREE.Vector3() }, uLight: { value: new THREE.Vector3(1, 1, 1) },
      uThreshold: { value: 0.3 }, uSoft: { value: 0.05 }, uTime: { value: 0 },
    },
  });
  const scene = new THREE.Scene();
  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
  const mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), sim);
  mesh.frustumCulled = false;
  scene.add(mesh);

  const glass = new THREE.ShaderMaterial({
    vertexShader: quadVert, fragmentShader: glassFrag, ...premult,
    uniforms: {
      uInk: { value: null }, uTexel: { value: new THREE.Vector2(1, 1) }, uTint: { value: new THREE.Vector3(1, 1, 1) },
      uThreshold: { value: 0.3 }, uSoft: { value: 0.05 }, uTintAmt: { value: 0.08 }, uRimAmt: { value: 0.6 },
      uSpec: { value: 0.9 }, uShade: { value: 0.3 }, uNormal: { value: 3 },
      uSize: { value: new THREE.Vector2(1, 1) }, uTime: { value: 0 }, // posés par la boucle commune (inutilisés ici)
    },
  });
  const s = { THREE, renderer, canvas, scene, camera, mesh, sim, solid, reveal, glass, premult, width: 0, height: 0, lost: false };
  canvas.addEventListener('webglcontextlost', (e) => { e.preventDefault(); s.lost = true; canvas.style.display = 'none'; document.dispatchEvent(new CustomEvent('yallaquest:gl-lost')); });
  return s;
}

function resize() {
  if (!stage) return;
  const w = document.documentElement.clientWidth, h = window.innerHeight;
  if (w === stage.width && h === stage.height) return;
  stage.width = w; stage.height = h;
  stage.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, ctx.TUNE.ink.maxDpr));
  stage.renderer.setSize(w, h, false);
  dirty = true; cleared = false;
}

/** La scène partagée (ou null si WebGL est indisponible). */
export function getStage(c) {
  if (c) ctx = c;
  if (stage) return stage.lost ? null : stage;
  if (!ctx || !ctx.THREE || !ctx.env.webgl) return null;
  stage = createStage(ctx.THREE);
  if (!stage) { ctx.env.webgl = false; return null; }
  resize();
  bind();
  return stage;
}

/** Une passe = { active(now): bool, render(stage, now, dt) } dessinée après l'encre. */
export function addPass(p) { passes.add(p); dirty = true; return () => passes.delete(p); }
export function wake() { dirty = true; }
export function getPointer() { return pointer; }
export function setPaused(v) { paused = v; dirty = true; }

/* ---------- pointeur ---------- */
function onMove(e) {
  if (e.pointerType === 'touch') return;
  pointer.x = e.clientX; pointer.y = e.clientY; pointer.inside = true; dirty = true;
}
function onLeave() { pointer.inside = false; }
function onDown(e) {
  // Écran tactile / étroit : pas de suivi du curseur, juste une courte giclée au toucher dans une zone « solid ».
  if (!ctx || !ctx.env.coarse || paused) return;
  for (const z of zones.values()) {
    if (z.mode !== 'solid') continue;
    const r = zoneRect(z);
    if (!r || e.clientX < r.x || e.clientX > r.x + r.w || e.clientY < r.y || e.clientY > r.y + r.h) continue;
    for (let i = 0; i < 5; i++) {
      const a = Math.random() * 6.283, d = 6 + Math.random() * 26;
      splatAt(z, e.clientX + Math.cos(a) * d, e.clientY + Math.sin(a) * d, 14 + Math.random() * 18, 0.9);
    }
  }
}
function onScroll() { dirty = true; }
function onVisibility() { lastTime = 0; dirty = true; }

function bind() {
  if (bound) return; bound = true;
  window.addEventListener('pointermove', onMove, { passive: true });
  window.addEventListener('pointerdown', onDown, { passive: true });
  document.documentElement.addEventListener('pointerleave', onLeave);
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', resize);
  document.addEventListener('visibilitychange', onVisibility);
  ctx.gsap.ticker.add(tick);
}
function unbind() {
  if (!bound) return; bound = false;
  window.removeEventListener('pointermove', onMove);
  window.removeEventListener('pointerdown', onDown);
  document.documentElement.removeEventListener('pointerleave', onLeave);
  window.removeEventListener('scroll', onScroll);
  window.removeEventListener('resize', resize);
  document.removeEventListener('visibilitychange', onVisibility);
  ctx.gsap.ticker.remove(tick);
}

/* ---------- zones ---------- */
function zoneRect(z) {
  if (z.fixed) return stage ? { x: 0, y: 0, w: stage.width, h: stage.height } : null; // zone « curseur » : tout l'écran
  if (!z.el.isConnected) return null;
  const r = z.el.getBoundingClientRect();
  if (!r.width || !r.height) return null;
  let left = r.left - z.bleed[3], top = r.top - z.bleed[0], right = r.right + z.bleed[1], bottom = r.bottom + z.bleed[2];
  if (z.cut && z.cut.isConnected) {            // ne jamais passer sous les boutons d'action
    const c = z.cut.getBoundingClientRect();
    if (c.width && c.height) {
      if (c.left > r.left + r.width * 0.5) right = Math.min(right, c.left - 12);
      else if (c.top > r.top + 8) bottom = Math.min(bottom, c.top - 8);
    }
  }
  const w = right - left, h = bottom - top;
  if (w < 8 || h < 8) return null;
  return { x: left, y: top, w, h };
}

function makeTargets(z, w, h) {
  const { THREE } = stage, k = ctx.TUNE.ink.simScale;
  const tw = Math.max(16, Math.min(512, Math.round(w * k))), th = Math.max(16, Math.min(512, Math.round(h * k)));
  disposeTargets(z);
  const opt = { minFilter: THREE.LinearFilter, magFilter: THREE.LinearFilter, wrapS: THREE.ClampToEdgeWrapping, wrapT: THREE.ClampToEdgeWrapping, depthBuffer: false, stencilBuffer: false, generateMipmaps: false };
  z.a = new THREE.WebGLRenderTarget(tw, th, opt);
  z.b = new THREE.WebGLRenderTarget(tw, th, opt);
  z.tw = tw; z.th = th; z.w0 = w; z.h0 = h;
  for (const t of [z.a, z.b]) { stage.renderer.setRenderTarget(t); stage.renderer.setClearColor(0x000000, 1); stage.renderer.clear(); }
  stage.renderer.setClearColor(0x000000, 0);
  stage.renderer.setRenderTarget(null);
}
function disposeTargets(z) { if (z.a) z.a.dispose(); if (z.b) z.b.dispose(); z.a = z.b = null; }

function imageTexture(img) {
  if (!img || !img.complete || !img.naturalWidth) return null;
  const key = img.currentSrc || img.src;
  let t = texCache.get(key);
  if (!t) {
    // Une copie hors DOM : Three lit image.width/height, qui valent la taille AFFICHÉE pour une <img> de la page.
    const { THREE } = stage;
    t = new THREE.Texture();
    t.minFilter = THREE.LinearFilter; t.magFilter = THREE.LinearFilter; t.generateMipmaps = false;
    t.userData = { aspect: 1, ready: false };
    const copy = new Image();
    copy.onload = () => { t.image = copy; t.userData.aspect = copy.naturalWidth / copy.naturalHeight; t.userData.ready = true; t.needsUpdate = true; dirty = true; };
    copy.src = key;
    texCache.set(key, t);
  }
  return t.userData.ready ? t : null;
}
/** Texture partagée pour une <img> déjà chargée (utilisée aussi par l'emblème). */
export function textureFor(img) { return stage ? imageTexture(img) : null; }

function queue(z, x0, y0, x1, y1, radius, amount) {
  if (z.queue.length >= 4) z.queue.shift();
  z.queue.push([x0, y0, x1, y1, radius, amount]);
  z.hotUntil = performance.now() + ctx.TUNE.ink.hold;
  dirty = true;
}
function splatAt(z, clientX, clientY, radius, amount) {
  const r = zoneRect(z); if (!r) return;
  const x = clientX - r.x, y = r.y + r.h - clientY;
  queue(z, x, y, x, y, radius, amount);
}
/** Dépose de l'encre dans une zone depuis un autre module (coordonnées écran). */
export function splat(key, clientX, clientY, radius = 18, amount = 0.8) {
  const z = zones.get(key) || zones.get('cursor');
  if (z && stage && !stage.lost) splatAt(z, clientX, clientY, radius, amount);
}

function zoneBusy(z) {
  // Une carte en cours d'entrée ou inclinée n'est pas alignée sur son rectangle : pas de « reveal » dessus.
  return z.card && (z.card.dataset.fxEntering === '1' || z.card.dataset.fxTilt === '1');
}

/* ---------- boucle (gsap.ticker) ---------- */
function tick() {
  if (!stage || stage.lost || document.hidden) return;
  const now = performance.now();
  const dt = lastTime ? Math.min((now - lastTime) / 1000, 0.05) : 1 / 60;
  lastTime = now;
  const f = dt * 60; // normalisation à 60 i/s

  let anyHot = false, anyPass = false;
  if (!paused) {
    for (const z of zones.values()) if (z.hotUntil > now) { anyHot = true; break; }
    for (const p of passes) if (p.active(now)) { anyPass = true; break; }
  }
  if (!anyHot && !anyPass && !dirty) {
    if (!cleared) { clearScreen(); cleared = true; }
    return;
  }
  dirty = false;
  const { renderer, mesh, sim, THREE } = stage, T = ctx.TUNE.ink;
  let drew = false;
  clearScreen();
  const sy = window.scrollY, dScroll = lastScrollY === null ? 0 : sy - lastScrollY;
  lastScrollY = sy;

  if (!paused) for (const z of zones.values()) {
    const r = zoneRect(z);
    const onScreen = r && r.x < stage.width && r.x + r.w > 0 && r.y < stage.height && r.y + r.h > 0;
    if (!onScreen) { z.last = null; continue; }

    // éclaboussure du pointeur (ou du défilement sous un pointeur immobile)
    if (pointer.fine && pointer.inside && !ctx.env.coarse && !zoneBusy(z)
      && pointer.x >= r.x && pointer.x <= r.x + r.w && pointer.y >= r.y && pointer.y <= r.y + r.h) {
      const x = pointer.x - r.x, y = r.y + r.h - pointer.y;
      if (z.last) {
        const dist = Math.hypot(x - z.last[0], y - z.last[1]);
        if (dist > 0.6) {
          const m = z.mode === 'reveal' ? T.reveal : T.solid;
          const radius = Math.min(m.radiusMax, m.radiusMin + dist * m.radiusSpeed);
          queue(z, z.last[0], z.last[1], x, y, radius, Math.min(1, 0.45 + dist * 0.03));
        }
      }
      // zone « curseur » : une goutte reste toujours sous la souris, même immobile
      if (z.persist) queue(z, x, y, x, y, T.cursorRadius, 1);
      z.last = [x, y];
    } else z.last = null;

    if (z.hotUntil <= now) {
      if (z.a) { disposeTargets(z); }
      continue;
    }
    if (!z.a || Math.abs(r.w - z.w0) > z.w0 * 0.15 || Math.abs(r.h - z.h0) > z.h0 * 0.15) makeTargets(z, r.w, r.h);

    // 1. simulation : a -> b
    const u = sim.uniforms, m = z.mode === 'reveal' ? T.reveal : T.solid;
    u.uPrev.value = z.a.texture;
    u.uSize.value.set(r.w, r.h);
    u.uTexel.value.set(1 / z.tw, 1 / z.th);
    for (let i = 0; i < 4; i++) {
      const q = z.queue[i];
      if (q) { u.uSeg.value[i].set(q[0], q[1], q[2], q[3]); u.uSplat.value[i].set(q[4], q[5]); }
      else u.uSplat.value[i].set(0, 0);
    }
    z.queue.length = 0;
    u.uFade.value = Math.pow(T.fade, f);
    u.uSub.value = T.sub * f;
    u.uNoise.value = T.noise * f;
    u.uScale.value = T.noiseScale;
    u.uDrip.value = m.drip * f;
    u.uDiffuse.value = T.diffuse;
    u.uEdge.value = z.mode === 'reveal' || z.fixed ? 0 : T.edge;
    u.uShift.value.set(0, z.fixed ? dScroll : 0); // y GL vers le haut : le contenu monte quand on descend
    u.uTime.value = now / 1000 + z.seed;
    mesh.material = sim;
    renderer.setScissorTest(false);
    renderer.setRenderTarget(z.b);
    renderer.render(stage.scene, stage.camera);
    const t = z.a; z.a = z.b; z.b = t;

    // 2. affichage, découpé au rectangle de la zone
    let mat;
    if (z.mode === 'reveal') {
      const tex = imageTexture(z.el);
      if (!tex) continue;
      mat = stage.reveal;
      const ae = r.w / r.h, ai = tex.userData.aspect;
      mat.uniforms.uImage.value = tex;
      mat.uniforms.uCover.value.set(ai > ae ? ae / ai : 1, ai > ae ? 1 : ai / ae);
      mat.uniforms.uDark.value.fromArray(z.colors.night);
      mat.uniforms.uLight.value.fromArray(z.colors.accent);
    } else if (z.look === 'glass') {
      const G = T.glass;
      mat = stage.glass;
      mat.uniforms.uTint.value.fromArray(z.colors.accent);
      mat.uniforms.uTexel.value.set(1 / z.tw, 1 / z.th);
      mat.uniforms.uTintAmt.value = G.tint;
      mat.uniforms.uRimAmt.value = G.rim;
      mat.uniforms.uSpec.value = G.spec;
      mat.uniforms.uShade.value = G.shade;
      mat.uniforms.uNormal.value = G.normal;
    } else {
      mat = stage.solid;
      mat.uniforms.uColor.value.fromArray(z.colors.accent);
      mat.uniforms.uOpacity.value = z.opacity;
      mat.uniforms.uRim.value = z.rim;
    }
    mat.uniforms.uInk.value = z.a.texture;
    mat.uniforms.uSize.value.set(r.w, r.h);
    mat.uniforms.uThreshold.value = T.threshold;
    mat.uniforms.uSoft.value = T.soft;
    mat.uniforms.uTime.value = now / 1000 + z.seed;
    mesh.material = mat;
    renderer.setRenderTarget(null);
    const vy = stage.height - (r.y + r.h);
    renderer.setViewport(r.x, vy, r.w, r.h);
    renderer.setScissor(Math.max(0, r.x), Math.max(0, vy), Math.min(stage.width, r.x + r.w) - Math.max(0, r.x), Math.min(stage.height, vy + r.h) - Math.max(0, vy));
    renderer.setScissorTest(true);
    renderer.render(stage.scene, stage.camera);
    drew = true;
  }

  if (!paused) for (const p of passes) {
    if (!p.active(now)) continue;
    renderer.setRenderTarget(null);
    if (p.render(stage, now, dt) !== false) drew = true;
  }
  renderer.setScissorTest(false);
  renderer.setViewport(0, 0, stage.width, stage.height);
  cleared = !drew;
}

function clearScreen() {
  const { renderer } = stage;
  renderer.setRenderTarget(null);
  renderer.setScissorTest(false);
  renderer.setViewport(0, 0, stage.width, stage.height);
  renderer.clear();
}

/* ---------- cycle de vie ---------- */
export function init(root, c) {
  ctx = c;
  const on = c.env.webgl && !c.env.reduced && !!c.THREE && !!getStage();
  document.documentElement.classList.toggle('fx-ink', !!on);
  if (!on) { destroy(); return; }
  pointer.fine = !c.env.coarse;
  const S = c.SEL, T = c.TUNE.ink;
  const colors = { accent: cssColor('--accent'), night: cssColor('--night') };
  const next = new Map();
  const add = (key, el, opts) => {
    if (!el) return;
    const old = c.sameRoute ? zones.get(key) : null; // même page : l'encre en cours survit au re-rendu
    const z = old || { key, a: null, b: null, queue: [], hotUntil: 0, last: null, seed: Math.random() * 50 };
    Object.assign(z, { el, mode: 'solid', cut: null, card: null, bleed: [0, 0, 0, 0], opacity: 1, rim: 0, colors }, opts);
    next.set(key, z);
  };
  const cursorMode = !!T.cursor;
  const look = T.look === 'solid' ? 'solid' : 'glass';
  stage.canvas.classList.toggle('fx-over', cursorMode);                       // au-dessus de la page
  stage.canvas.classList.toggle('fx-invert', cursorMode && look === 'solid'); // mix-blend-mode: difference -> le texte touché s'inverse
  if (cursorMode) {
    // une seule zone plein écran : l'encre suit la souris partout (au toucher : une giclée)
    add('cursor', document.documentElement, { fixed: true, persist: true, look, opacity: T.solid.opacity, rim: T.solid.rim });
  } else {
  const b = c.env.coarse ? 10 : 24;
  const title = root.querySelector(S.titleRow);
  add('title', title, { cut: title && title.querySelector(S.actions), bleed: [b, 0, 20, b], opacity: T.solid.opacity, rim: T.solid.rim });
  add('foot', root.querySelector(S.pagefoot), { bleed: [14, 0, 14, 0], opacity: T.solid.opacity * T.footGain, rim: T.solid.rim * T.footGain });
  if (!c.env.coarse) root.querySelectorAll(S.art).forEach((img, i) => {
    const card = img.closest(S.proposal);
    add('art:' + (card?.getAttribute('aria-labelledby') || i), img, { mode: 'reveal', card });
  });
  }
  for (const [k, z] of zones) if (next.get(k) !== z) disposeTargets(z);
  zones.clear();
  for (const [k, z] of next) zones.set(k, z);
  dirty = true;
}

export function destroy() {
  for (const z of zones.values()) disposeTargets(z);
  zones.clear();
  dirty = true;
}

/** Arrêt complet (changement de préférences, perte du contexte). */
export function dispose() {
  destroy();
  passes.clear();
  document.documentElement.classList.remove('fx-ink');
  if (!stage) return;
  unbind();
  for (const t of texCache.values()) t.dispose();
  texCache.clear();
  stage.renderer.dispose();
  stage.canvas.remove();
  stage = null;
}
