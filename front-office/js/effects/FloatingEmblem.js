// Yalla Quest · effets — l'emblème de guilde en tuile 3D flottante.
// L'<img class="emblem"> reste dans le DOM (accessibilité, repli, place réservée : aucun décalage de mise en page) ;
// elle devient transparente une fois la tuile dessinée, dans le canvas partagé d'InkField.
import * as Ink from './InkField.js';
import { emblemVert, emblemFrag } from './shaders.js';

let ctx = null;
let img = null;
let rig = null;            // { scene, camera, group, front, back }
let removePass = null;
let state = { rx: 0, ry: 0, intro: 1, src: '', lastDrip: 0, live: false };
let introTween = null;

function build(stage) {
  const { THREE } = stage;
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(1, 1, 0.1, 20);
  const geo = new THREE.PlaneGeometry(1, 1);
  const mk = (back) => new THREE.ShaderMaterial({
    vertexShader: emblemVert, fragmentShader: emblemFrag, ...stage.premult, side: THREE.DoubleSide,
    uniforms: {
      uMap: { value: null }, uCover: { value: new THREE.Vector2(1, 1) }, uRadius: { value: 0.17 }, uPx: { value: 0.01 },
      uTilt: { value: new THREE.Vector2() }, uTime: { value: 0 }, uSheen: { value: 0 }, uAlpha: { value: 1 }, uBack: { value: back },
      uAccent: { value: new THREE.Vector3(1, 1, 1) }, uNight: { value: new THREE.Vector3() },
    },
  });
  const back = new THREE.Mesh(geo, mk(1)); back.position.z = -0.07; back.renderOrder = 0;
  const front = new THREE.Mesh(geo, mk(0)); front.renderOrder = 1;
  const group = new THREE.Group(); group.add(back, front); scene.add(group);
  return { scene, camera, group, front, back };
}

const pass = {
  active() {
    if (!img || !img.isConnected || !rig) return false;
    const r = img.getBoundingClientRect();
    pass.rect = r;
    return r.width > 0 && r.bottom > -40 && r.top < window.innerHeight + 40;
  },
  render(stage, now, dt) {
    const tex = Ink.textureFor(img);
    if (!tex) return false; // image pas encore chargée : l'<img> reste visible
    const T = ctx.TUNE.emblem, r = pass.rect, f = dt * 60, t = now / 1000;
    const p = Ink.getPointer();
    const cx = r.left + r.width / 2, cy = r.top + r.height / 2;

    // inclinaison vers le curseur (amortie), plus un rien de vitesse de défilement
    let tx = 0, ty = 0;
    if (p.inside && p.fine) {
      ty = Math.max(-1, Math.min(1, (p.x - cx) / T.reach));
      tx = Math.max(-1, Math.min(1, (p.y - cy) / T.reach));
    }
    tx += Math.max(-0.5, Math.min(0.5, (ctx.velocity ? ctx.velocity() : 0) * 0.02));
    const k = 1 - Math.pow(1 - T.lerp, f);
    const prx = state.rx, pry = state.ry;
    state.rx += (tx - state.rx) * k;
    state.ry += (ty - state.ry) * k;
    const max = (T.tilt * Math.PI) / 180;
    const { group, front, back, camera, scene } = rig;
    group.rotation.x = state.rx * max + Math.sin(t * 0.7) * 0.03;
    group.rotation.y = state.ry * max + Math.cos(t * 0.5) * 0.04;
    group.rotation.z = Math.sin(t * 0.6) * 0.015;
    group.position.y = Math.sin(t * T.bobSpeed) * T.bob;
    const s = 0.55 + 0.45 * state.intro;
    group.scale.setScalar(s);
    group.rotation.y += (1 - state.intro) * -1.1;

    // viewport carré, plus grand que l'image pour laisser la rotation déborder
    const pad = T.overscan, side = r.width * (1 + pad * 2);
    const dist = 3;
    camera.fov = (2 * Math.atan(((1 + pad * 2) / 2) / dist) * 180) / Math.PI;
    camera.aspect = 1; camera.position.set(0, 0, dist); camera.updateProjectionMatrix();

    const radius = parseFloat(getComputedStyle(img).borderTopLeftRadius) || 18;
    for (const m of [front, back]) {
      const u = m.material.uniforms;
      u.uMap.value = tex;
      const ai = tex.userData.aspect, ae = r.width / r.height;
      u.uCover.value.set(ai > ae ? ae / ai : 1, ai > ae ? 1 : ai / ae);
      u.uRadius.value = radius / r.width;
      u.uPx.value = 1 / (r.width * Math.min(window.devicePixelRatio || 1, 2));
      u.uTilt.value.set(state.ry, -state.rx);
      u.uTime.value = t;
      u.uSheen.value = T.sheen;
      u.uAlpha.value = Math.min(1, state.intro * 1.6);
      u.uAccent.value.fromArray(pass.colors.accent);
      u.uNight.value.fromArray(pass.colors.night);
    }
    const x = cx - side / 2, y = stage.height - (cy + side / 2);
    stage.renderer.setViewport(x, y, side, side);
    stage.renderer.setScissor(Math.max(0, x), Math.max(0, y), Math.max(1, Math.min(stage.width, x + side) - Math.max(0, x)), Math.max(1, Math.min(stage.height, y + side) - Math.max(0, y)));
    stage.renderer.setScissorTest(true);
    stage.renderer.render(scene, camera);

    if (!state.live) { state.live = true; img.classList.add('fx-emblem-live'); }

    // l'encre lilas suinte de derrière la tuile quand elle bouge
    const speed = Math.hypot(state.rx - prx, state.ry - pry);
    if (speed > T.dripSpeed && now - state.lastDrip > T.dripEvery) {
      state.lastDrip = now;
      const a = Math.random() * 6.283, rr = r.width * 0.5;
      Ink.splat('title', cx + Math.cos(a) * rr, cy + Math.sin(a) * rr * 0.9 + r.height * 0.1, 10 + Math.random() * 10, 0.75);
    }
    return true;
  },
  rect: null,
  colors: { accent: [1, 1, 1], night: [0, 0, 0] },
};

function burst() {
  // bouffée d'encre à l'arrivée sur la page
  if (!img) return;
  const r = img.getBoundingClientRect(), cx = r.left + r.width / 2, cy = r.top + r.height / 2;
  for (let i = 0; i < 4; i++) {
    const a = (i / 4) * 6.283 + Math.random(), rr = r.width * 0.48;
    Ink.splat('title', cx + Math.cos(a) * rr, cy + Math.sin(a) * rr, 16 + Math.random() * 12, 0.95);
  }
}

export function init(root, c) {
  ctx = c;
  const el = root.querySelector(c.SEL.emblem);
  const stage = c.env.webgl && !c.env.reduced && !c.env.coarse ? Ink.getStage(c) : null;
  if (!el || !stage) { destroy(); return; }
  if (!rig) rig = build(stage);
  const src = el.currentSrc || el.src;
  const same = c.sameRoute && state.src === src && state.live;
  img = el;
  pass.colors = { accent: Ink.cssColor('--accent'), night: Ink.cssColor('--night') };
  if (same) {
    img.classList.add('fx-emblem-live'); // même image : la tuile continue, sans rejouer l'entrée
  } else {
    if (introTween) introTween.kill();
    state = { rx: 0, ry: 0, intro: 0, src, lastDrip: 0, live: false };
    introTween = c.gsap.to(state, { intro: 1, duration: c.TUNE.emblem.intro, delay: c.delay || 0, ease: 'power3.out', onStart: burst });
  }
  if (!removePass) removePass = Ink.addPass(pass);
  Ink.wake();
}

export function destroy() {
  if (introTween) { introTween.kill(); introTween = null; }
  if (removePass) { removePass(); removePass = null; }
  if (img) img.classList.remove('fx-emblem-live');
  img = null;
  state.live = false;
  Ink.wake();
}

export function dispose() {
  destroy();
  if (rig) { rig.front.geometry.dispose(); rig.front.material.dispose(); rig.back.material.dispose(); rig = null; }
}
