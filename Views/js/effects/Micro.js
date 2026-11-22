// Micro-interactions : inclinaison 3D des cartes + parallaxe de l'image, attraction magnétique (.btn.primary,
// .chip), pastille d'encre qui coule entre les onglets, tache d'encre dans le bouton de vote pressé, toast
// élastique. Inclinaison et aimant : souris + écran large seulement, jamais en mouvement réduit.

let cfg = null, gsap = null;
let offs = [];             // écouteurs à retirer
let cards = [];            // états d'inclinaison
let observers = [];
let timelines = [];
let ticking = false;

export function setup(c) { cfg = c; gsap = c.gsap; }

const on = (el, type, fn, opts) => { el.addEventListener(type, fn, opts); offs.push(() => el.removeEventListener(type, fn, opts)); };
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const rectOf = (a) => ({ left: a.offsetLeft, top: a.offsetTop, width: a.offsetWidth, height: a.offsetHeight });

/* ---------- mesure des onglets sélectionnés, AVANT le re-rendu (appelée au clic, en capture) ---------- */
export function measureTabs(root) {
  if (!cfg || !root) return [];
  const S = cfg.SEL;
  return [...root.querySelectorAll(S.tabs)].map((tabs) => {
    const all = [...tabs.querySelectorAll(S.tab)];
    const sel = all.find((a) => a.getAttribute('aria-selected') === 'true');
    return sel && tabs.classList.contains('fx-tabs') ? Object.assign(rectOf(sel), { index: all.indexOf(sel) }) : null;
  });
}

export function init(root, ctx) {
  if (!cfg || !root) return;
  if (!ticking) { gsap.ticker.add(tick); ticking = true; }
  if (cfg.env.reduced) return;
  initTilt(root);
  initMagnetic(root);
  initTabs(root, ctx);
  if (ctx.mode === 'update' && ctx.action && ctx.action.kind === 'vote') splash(root, ctx.action);
}

export function destroy() {
  offs.forEach((f) => f()); offs = [];
  observers.forEach((o) => o.disconnect()); observers = [];
  timelines.forEach((t) => t.kill()); timelines = [];
  cards.forEach((st) => reset(st)); cards = [];
}

/* ---------- inclinaison des cartes ---------- */
function initTilt(root) {
  const S = cfg.SEL, T = cfg.TUNE.tilt;
  root.querySelectorAll(S.proposal).forEach((card) => {
    const st = { card, rx: 0, ry: 0, tx: 0, ty: 0, px: 0, py: 0, tpx: 0, tpy: 0, s: 0, ts: 0, active: false };
    on(card, 'pointermove', (e) => {
      if (e.pointerType !== 'mouse' || !cfg.env.fine) return;
      const r = card.getBoundingClientRect();
      const nx = clamp(((e.clientX - r.left) / r.width) * 2 - 1, -1, 1);
      const ny = clamp(((e.clientY - r.top) / r.height) * 2 - 1, -1, 1);
      const blocked = !!(e.target.closest && e.target.closest(S.noTilt));   // pas d'inclinaison sur les boutons de vote ni sur la liste des votants
      st.tx = blocked ? 0 : -ny * T.max;
      st.ty = blocked ? 0 : nx * T.max;
      st.tpx = -nx * T.parallax; st.tpy = -ny * T.parallax; st.ts = 1;
      st.active = true;
    }, { passive: true });
    on(card, 'pointerleave', () => { st.tx = st.ty = st.tpx = st.tpy = 0; st.ts = 0; }, { passive: true });
    cards.push(st);
  });
}
function reset(st) {
  st.card.style.transform = '';
  ['--fx-px', '--fx-py', '--fx-ps'].forEach((p) => st.card.style.removeProperty(p));
  st.active = false;
}
function tick(time, deltaMs) {
  if (!cards.length) return;
  const T = cfg.TUNE.tilt;
  const k = 1 - Math.pow(1 - T.lerp, Math.min(deltaMs, 66) / 16.667);
  cards.forEach((st) => {
    if (!st.active) return;
    st.rx += (st.tx - st.rx) * k; st.ry += (st.ty - st.ry) * k;
    st.px += (st.tpx - st.px) * k; st.py += (st.tpy - st.py) * k; st.s += (st.ts - st.s) * k;
    const still = Math.abs(st.rx) + Math.abs(st.ry) + Math.abs(st.px) + Math.abs(st.py) + st.s < 0.01;
    if (still && st.ts === 0 && st.tx === 0 && st.ty === 0) { st.rx = st.ry = st.px = st.py = st.s = 0; reset(st); return; }
    st.card.style.transform = `perspective(${T.perspective}px) rotateX(${st.rx.toFixed(3)}deg) rotateY(${st.ry.toFixed(3)}deg)`;
    st.card.style.setProperty('--fx-px', st.px.toFixed(2) + 'px');
    st.card.style.setProperty('--fx-py', st.py.toFixed(2) + 'px');
    st.card.style.setProperty('--fx-ps', (1 + (T.artScale - 1) * st.s).toFixed(4));
  });
}

/* ---------- attraction magnétique ---------- */
function initMagnetic(root) {
  const M = cfg.TUNE.magnetic;
  root.querySelectorAll(cfg.SEL.magnetic).forEach((el) => {
    gsap.set(el, { '--fx-mx': '0px', '--fx-my': '0px' });
    on(el, 'pointermove', (e) => {
      if (e.pointerType !== 'mouse' || !cfg.env.fine) return;
      const r = el.getBoundingClientRect();
      const cx = r.left + r.width / 2 - (parseFloat(gsap.getProperty(el, '--fx-mx')) || 0);
      const cy = r.top + r.height / 2 - (parseFloat(gsap.getProperty(el, '--fx-my')) || 0);
      const x = clamp((e.clientX - cx) * M.strength, -M.max, M.max);
      const y = clamp((e.clientY - cy) * M.strength, -M.max, M.max);
      gsap.to(el, { '--fx-mx': x + 'px', '--fx-my': y + 'px', duration: M.follow, ease: 'power3.out', overwrite: 'auto' });
    }, { passive: true });
    on(el, 'pointerleave', () => {
      gsap.to(el, { '--fx-mx': '0px', '--fx-my': '0px', duration: M.release, ease: 'elastic.out(1, 0.35)', overwrite: 'auto' });
    }, { passive: true });
  });
}

/* ---------- pastille d'onglet ---------- */
function initTabs(root, ctx) {
  const S = cfg.SEL;
  const prev = ctx.action && ctx.action.kind === 'tab' ? ctx.action.tabs || [] : [];
  [...root.querySelectorAll(S.tabs)].forEach((tabs, i) => {
    const all = [...tabs.querySelectorAll(S.tab)];
    const sel = all.find((a) => a.getAttribute('aria-selected') === 'true');
    if (!sel) return;
    tabs.classList.add('fx-tabs');
    const blob = document.createElement('span');
    blob.className = 'fx-tab-blob';
    blob.setAttribute('aria-hidden', 'true');
    tabs.prepend(blob);
    const place = () => { const r = rectOf(sel); gsap.set(blob, { x: r.left, y: r.top, width: r.width, height: r.height, scaleY: 1 }); };
    const from = prev[i];
    const to = rectOf(sel);
    if (from && (from.left !== to.left || from.top !== to.top)) morph(blob, from, to, sel, all[from.index], place);
    else place();
    if (window.ResizeObserver) {
      const ro = new ResizeObserver(() => { if (!blob.dataset.moving) place(); });
      ro.observe(tabs);
      observers.push(ro);
    }
  });
}
function morph(blob, from, to, sel, oldSel, place) {
  const B = cfg.TUNE.tabs;
  const o = { l: from.left, r: from.left + from.width, t: from.top, h: from.height, sy: 1 };
  const right = to.left >= from.left;
  const lead = right ? 'r' : 'l', trail = right ? 'l' : 'r';
  const target = { l: to.left, r: to.left + to.width };
  sel.classList.add('fx-tab-pending');
  if (oldSel && oldSel !== sel) oldSel.classList.add('fx-tab-leaving');
  blob.dataset.moving = '1';
  const apply = () => gsap.set(blob, { x: o.l, y: o.t, width: Math.max(0, o.r - o.l), height: o.h, scaleY: o.sy });
  apply();
  const done = () => {
    sel.classList.remove('fx-tab-pending');
    if (oldSel) oldSel.classList.remove('fx-tab-leaving');
    delete blob.dataset.moving;
    place();
  };
  const tl = gsap.timeline({ onUpdate: apply, onComplete: done, onInterrupt: done });
  tl.to(o, { [lead]: target[lead], duration: B.lead, ease: 'power3.out' }, 0)
    .to(o, { [trail]: target[trail], duration: B.trail, ease: 'power3.inOut' }, B.lag)
    .to(o, { t: to.top, h: to.height, duration: B.lead, ease: 'power3.out' }, 0)
    .to(o, { sy: B.squash, duration: 0.14, ease: 'power2.out', yoyo: true, repeat: 1 }, 0)
    .call(() => { if (oldSel) oldSel.classList.remove('fx-tab-leaving'); }, null, B.lead * 0.45)
    .call(() => sel.classList.remove('fx-tab-pending'), null, B.lead * 0.55);
  timelines.push(tl);
}

/* ---------- tache d'encre dans le bouton de vote ---------- */
function splash(root, act) {
  const esc = (v) => (window.CSS && CSS.escape ? CSS.escape(v) : String(v).replace(/"/g, '\\"'));
  const btn = root.querySelector(`[data-vote="${esc(act.data.vote)}"][data-answer="${esc(act.data.answer)}"]`);
  if (!btn || btn.getAttribute('aria-pressed') !== 'true') return;
  const P = cfg.TUNE.splash;
  const r = btn.getBoundingClientRect();
  const inside = !act.keyboard && act.x >= r.left && act.x <= r.right && act.y >= r.top && act.y <= r.bottom;
  const lx = inside ? act.x - r.left : r.width / 2, ly = inside ? act.y - r.top : r.height / 2;
  const reach = Math.hypot(Math.max(lx, r.width - lx), Math.max(ly, r.height - ly));
  const dot = document.createElement('span');
  dot.className = 'fx-splash';
  dot.setAttribute('aria-hidden', 'true');
  btn.appendChild(dot);
  btn.classList.add('fx-inking');
  const finish = () => { btn.classList.remove('fx-inking', 'fx-inked'); dot.remove(); };
  gsap.set(dot, { x: lx, y: ly, scale: 0, borderRadius: '50%' });
  const tl = gsap.timeline({ onComplete: finish, onInterrupt: finish });
  tl.to(dot, { scale: (reach * 2.2) / 12, duration: P.duration, ease: 'power2.out' }, 0)
    .to(dot, { borderRadius: '42% 58% 55% 45% / 48% 40% 60% 52%', duration: P.duration * 0.6, ease: 'sine.inOut', yoyo: true, repeat: 1 }, 0)
    .call(() => btn.classList.add('fx-inked'), null, P.inkAt)
    .call(() => btn.classList.remove('fx-inking', 'fx-inked'), null, P.duration)
    .to(dot, { opacity: 0, duration: 0.2, ease: 'none' }, P.duration);
  timelines.push(tl);
}

/* ---------- toast : petit écrasement liquide à chaque apparition ---------- */
export function setupToast(el) {
  if (!cfg || !el || cfg.env.reduced) return;
  const T = cfg.TUNE.toast;
  gsap.set(el, { '--fx-sx': 1, '--fx-sy': 1 });
  new MutationObserver(() => {
    if (!el.classList.contains('show')) return;
    gsap.fromTo(el, { '--fx-sx': T.sx, '--fx-sy': T.sy }, { '--fx-sx': 1, '--fx-sy': 1, duration: T.duration, ease: 'elastic.out(1, 0.45)', overwrite: true });
  }).observe(el, { attributes: true, attributeFilter: ['class'], childList: true, characterData: true, subtree: true });
}
