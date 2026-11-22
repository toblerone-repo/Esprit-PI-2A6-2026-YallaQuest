// Emblème flottant (page d'une guilde) : l'<img class="emblem"> reste dans le DOM (accessibilité, repli, place
// réservée : aucun décalage) mais devient invisible ; à sa place, dans le canvas d'encre partagé, une tuile 3D
// arrondie texturée avec la même image. Elle s'incline vers le curseur (15° max, amorti 0,08), flotte doucement
// et porte un reflet irisé discret. Dessinée après l'encre du titre : l'encre semble couler de derrière elle.
// Sans WebGL (ou si le contexte est perdu), l'image d'origine reste affichée.

import { EMBLEM_VERT, EMBLEM_FRAG } from './shaders.js';

let cfg = null, T3 = null, stage = null;
let scene = null, camera = null, mesh = null, mat = null;
let img = null, liveImg = null, introTween = null;
const st = { rx: 0, ry: 0, intro: 1 };
const texCache = new Map();
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

export function setup(THREE, s, c) {
  T3 = THREE; stage = s; cfg = c;
  const E = cfg.TUNE.emblem;
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(30, 1, 0.1, 20);
  // la tuile (1 unité) occupe exactement la taille de l'<img> ; la marge laisse la place à l'inclinaison
  camera.position.set(0, 0, (1 + 2 * E.margin) / 2 / Math.tan(THREE.MathUtils.degToRad(15)));

  const b = E.bevel, side = 1 - 2 * b, r = Math.max(0.01, E.radius - b), h = side / 2;
  const shape = new THREE.Shape();
  shape.moveTo(-h + r, -h);
  shape.lineTo(h - r, -h); shape.absarc(h - r, -h + r, r, -Math.PI / 2, 0, false);
  shape.lineTo(h, h - r); shape.absarc(h - r, h - r, r, 0, Math.PI / 2, false);
  shape.lineTo(-h + r, h); shape.absarc(-h + r, h - r, r, Math.PI / 2, Math.PI, false);
  shape.lineTo(-h, -h + r); shape.absarc(-h + r, -h + r, r, Math.PI, Math.PI * 1.5, false);
  const geo = new THREE.ExtrudeGeometry(shape, { depth: E.depth, bevelEnabled: true, bevelThickness: b, bevelSize: b, bevelSegments: 4, curveSegments: 10 });
  geo.center();
  const pos = geo.attributes.position, uv = geo.attributes.uv;
  for (let i = 0; i < pos.count; i++) uv.setXY(i, pos.getX(i) + 0.5, pos.getY(i) + 0.5);
  uv.needsUpdate = true;

  mat = new THREE.ShaderMaterial({
    vertexShader: EMBLEM_VERT, fragmentShader: EMBLEM_FRAG,
    uniforms: {
      uMap: { value: null }, uUvScale: { value: new THREE.Vector2(1, 1) }, uUvOffset: { value: new THREE.Vector2() },
      uTilt: { value: new THREE.Vector2() }, uTime: { value: 0 }, uAlpha: { value: 1 }, uSheen: { value: E.sheen }, uChroma: { value: E.chroma },
      uAccent: { value: new THREE.Vector3() }, uNight: { value: new THREE.Vector3() },
    },
    transparent: true, premultipliedAlpha: true, depthTest: true, depthWrite: true,
  });
  mesh = new THREE.Mesh(geo, mat);
  scene.add(mesh);
  stage.addLayer({ active, draw, lost });
}

export function init(root, ctx) {
  if (!stage || stage.dead || !root) return;
  img = root.querySelector(cfg.SEL.emblem);
  if (!img) return;
  readColors();
  if (ctx && ctx.mode === 'enter') {
    if (introTween) introTween.kill();
    st.intro = 0;
    introTween = cfg.gsap.to(st, { intro: 1, duration: cfg.TUNE.emblem.introDuration, delay: ctx.delay || 0, ease: 'power3.out' });
  }
}

export function destroy() {
  // l'ancienne image disparaît avec le rendu ; l'intro en cours continue (pas de rejeu sur la même route)
  if (img) img.classList.remove('fx-emblem-live');
  img = null; liveImg = null;
}

function readColors() {
  const probe = document.createElement('canvas').getContext('2d');
  const read = (name, target) => {
    probe.fillStyle = '#000';
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    if (v) probe.fillStyle = v;
    const s = probe.fillStyle;
    if (s[0] === '#') target.set(parseInt(s.slice(1, 3), 16) / 255, parseInt(s.slice(3, 5), 16) / 255, parseInt(s.slice(5, 7), 16) / 255);
  };
  read('--accent', mat.uniforms.uAccent.value);
  read('--night', mat.uniforms.uNight.value);
}

function textureFor(el) {
  const src = el.currentSrc || el.src;
  if (!src) return null;
  let t = texCache.get(src);
  if (!t) {
    const image = new Image();
    image.src = src;
    t = new T3.Texture(image);
    t.anisotropy = Math.min(4, stage.renderer.capabilities.getMaxAnisotropy());
    const ready = () => { t.needsUpdate = true; };
    if (image.complete && image.naturalWidth) ready(); else image.addEventListener('load', ready, { once: true });
    texCache.set(src, t);
  }
  return t.image.complete && t.image.naturalWidth ? t : null;
}

function active() {
  if (!img || !img.isConnected || stage.dead || !textureFor(img)) return false;
  const r = img.getBoundingClientRect();
  return r.width > 0 && r.bottom > -r.height && r.top < stage.vh + r.height;
}

function draw(dt, time) {
  const E = cfg.TUNE.emblem, P = cfg.pointer, renderer = stage.renderer;
  const tex = textureFor(img);
  const r = img.getBoundingClientRect();
  const size = r.width, m = size * E.margin, S = size + 2 * m;
  const x = r.left - m, y = stage.vh - (r.bottom + m);

  // inclinaison vers le curseur (souris + écran large seulement)
  let tx = 0, ty = 0;
  if (cfg.env.fine && P.inside && P.type === 'mouse') {
    const reach = Math.max(stage.vw, stage.vh) * E.reach;
    ty = clamp((P.x - (r.left + size / 2)) / reach, -1, 1) * E.tilt;
    tx = clamp((P.y - (r.top + size / 2)) / reach, -1, 1) * E.tilt;
  }
  const k = 1 - Math.pow(1 - E.lerp, dt * 60);
  st.rx += (tx - st.rx) * k;
  st.ry += (ty - st.ry) * k;
  const it = st.intro, rad = T3.MathUtils.degToRad;
  mesh.rotation.set(rad(st.rx) + Math.sin(time * 0.8) * 0.03 + (1 - it) * 0.6, rad(st.ry) + Math.cos(time * 0.6) * 0.03, Math.sin(time * 0.5) * 0.015);
  mesh.position.y = Math.sin(time * E.bobSpeed) * E.bob;
  mesh.scale.setScalar(0.7 + 0.3 * it);

  const u = mat.uniforms;
  u.uMap.value = tex;
  const iA = tex.image.naturalWidth / Math.max(1, tex.image.naturalHeight);
  if (iA > 1) { u.uUvScale.value.set(1 / iA, 1); u.uUvOffset.value.set((1 - 1 / iA) / 2, 0); }
  else { u.uUvScale.value.set(1, iA); u.uUvOffset.value.set(0, (1 - iA) / 2); }
  u.uTilt.value.set(st.ry / E.tilt, -st.rx / E.tilt);
  u.uTime.value = time;
  u.uAlpha.value = it;

  const sx = Math.max(0, x), sy = Math.max(0, y);
  const sw = Math.min(stage.vw, x + S) - sx, sh = Math.min(stage.vh, y + S) - sy;
  if (sw <= 0 || sh <= 0) return;
  renderer.setRenderTarget(null);
  renderer.setViewport(x, y, S, S);
  renderer.setScissor(sx, sy, sw, sh);
  renderer.setScissorTest(true);
  renderer.render(scene, camera);
  renderer.setScissorTest(false);

  // premier rendu réussi : on masque l'image d'origine (elle garde sa place)
  if (liveImg !== img) {
    liveImg = img;
    cfg.gsap.killTweensOf(img);
    cfg.gsap.set(img, { clearProps: 'transform,opacity' });
    img.classList.add('fx-emblem-live');
  }
}

function lost() {
  if (img) img.classList.remove('fx-emblem-live');
  liveImg = null;
}
