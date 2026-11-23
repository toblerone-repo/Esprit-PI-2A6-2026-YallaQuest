// GLSL des effets (chaînes exportées). Three.js (WebGL 2) ajoute lui-même #version, texture2D -> texture
// et gl_FragColor -> sortie. Toutes les couleurs arrivent en uniforms, lues dans les variables CSS.

// Bruit simplex 3D — Ian McEwan, Ashima Arts / Stefan Gustavson (licence MIT).
const NOISE = /* glsl */ `
vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec4 mod289(vec4 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec4 permute(vec4 x) { return mod289(((x * 34.0) + 1.0) * x); }
vec4 taylorInvSqrt(vec4 r) { return 1.79284291400159 - 0.85373472095314 * r; }
float snoise(vec3 v) {
  const vec2 C = vec2(1.0 / 6.0, 1.0 / 3.0);
  const vec4 D = vec4(0.0, 0.5, 1.0, 2.0);
  vec3 i = floor(v + dot(v, C.yyy));
  vec3 x0 = v - i + dot(i, C.xxx);
  vec3 g = step(x0.yzx, x0.xyz);
  vec3 l = 1.0 - g;
  vec3 i1 = min(g.xyz, l.zxy);
  vec3 i2 = max(g.xyz, l.zxy);
  vec3 x1 = x0 - i1 + C.xxx;
  vec3 x2 = x0 - i2 + C.yyy;
  vec3 x3 = x0 - D.yyy;
  i = mod289(i);
  vec4 p = permute(permute(permute(i.z + vec4(0.0, i1.z, i2.z, 1.0)) + i.y + vec4(0.0, i1.y, i2.y, 1.0)) + i.x + vec4(0.0, i1.x, i2.x, 1.0));
  float n_ = 0.142857142857;
  vec3 ns = n_ * D.wyz - D.xzx;
  vec4 j = p - 49.0 * floor(p * ns.z * ns.z);
  vec4 x_ = floor(j * ns.z);
  vec4 y_ = floor(j - 7.0 * x_);
  vec4 x = x_ * ns.x + ns.yyyy;
  vec4 y = y_ * ns.x + ns.yyyy;
  vec4 h = 1.0 - abs(x) - abs(y);
  vec4 b0 = vec4(x.xy, y.xy);
  vec4 b1 = vec4(x.zw, y.zw);
  vec4 s0 = floor(b0) * 2.0 + 1.0;
  vec4 s1 = floor(b1) * 2.0 + 1.0;
  vec4 sh = -step(h, vec4(0.0));
  vec4 a0 = b0.xzyw + s0.xzyw * sh.xxyy;
  vec4 a1 = b1.xzyw + s1.xzyw * sh.zzww;
  vec3 p0 = vec3(a0.xy, h.x);
  vec3 p1 = vec3(a0.zw, h.y);
  vec3 p2 = vec3(a1.xy, h.z);
  vec3 p3 = vec3(a1.zw, h.w);
  vec4 norm = taylorInvSqrt(vec4(dot(p0, p0), dot(p1, p1), dot(p2, p2), dot(p3, p3)));
  p0 *= norm.x; p1 *= norm.y; p2 *= norm.z; p3 *= norm.w;
  vec4 m = max(0.6 - vec4(dot(x0, x0), dot(x1, x1), dot(x2, x2), dot(x3, x3)), 0.0);
  m = m * m;
  return 42.0 * dot(m * m, vec4(dot(p0, x0), dot(p1, x1), dot(p2, x2), dot(p3, x3)));
}`;

// plein cadre : la position du quad est déjà en coordonnées de découpe
export const QUAD_VERT = /* glsl */ `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = vec4(position.xy, 0.0, 1.0);
}`;

// simulation : advection par un champ de rotationnel (curl noise) + gravité sur l'encre épaisse + traînée
// du défilement, atténuation, puis taches (splats) au passage de la souris
export const SIM_FRAG = /* glsl */ `
precision highp float;
uniform sampler2D uPrev;
uniform float uAspect;
uniform float uTime;
uniform float uDt;
uniform float uFade;
uniform float uNoise;
uniform float uNoiseScale;
uniform float uDrip;
uniform float uDrag;
uniform float uScale;
uniform vec4 uSplats[MAX_SPLATS];   // xy = position (uv), z = rayon (en hauteurs de zone), w = quantité
uniform int uSplatCount;
varying vec2 vUv;
${NOISE}
vec2 curl(vec2 p, float t) {
  const float e = 0.08;
  float a = snoise(vec3(p.x, p.y + e, t)) - snoise(vec3(p.x, p.y - e, t));
  float b = snoise(vec3(p.x + e, p.y, t)) - snoise(vec3(p.x - e, p.y, t));
  return vec2(a, -b) / (2.0 * e);
}
void main() {
  float here = texture2D(uPrev, vUv).r * uScale;
  vec2 p = vec2(vUv.x * uAspect, vUv.y) * uNoiseScale;
  vec2 vel = curl(p, uTime * 0.12) * uNoise;
  vel.y -= uDrip * smoothstep(0.35, 1.6, here);
  vel.y += uDrag;
  vec2 back = vUv - vec2(vel.x / uAspect, vel.y) * uDt;
  float d = texture2D(uPrev, back).r * uScale * uFade;
  float border = min(min(vUv.x, 1.0 - vUv.x), min(vUv.y, 1.0 - vUv.y));
  d *= mix(0.9, 1.0, smoothstep(0.0, 0.02, border));
  for (int i = 0; i < MAX_SPLATS; i++) {
    if (i >= uSplatCount) break;
    vec4 s = uSplats[i];
    vec2 q = vec2((vUv.x - s.x) * uAspect, vUv.y - s.y);
    d += s.w * exp(-dot(q, q) / (s.z * s.z));
  }
  gl_FragColor = vec4(min(d, 3.0) / uScale, 0.0, 0.0, 1.0);
}`;

const BOX = /* glsl */ `
float boxMask(vec2 p, vec4 r, float f) {
  vec2 q = max(r.xy - p, p - r.zw);
  return 1.0 - smoothstep(-f, f, max(q.x, q.y));
}`;

// zone « solid » : encre lilas derrière le texte. Seuil smoothstep = bord net et gluant ; léger relief
// (reflet ivoire) ; sous chaque ligne de texte l'opacité est plafonnée pour garder le contraste ;
// plafond 0 sous les boutons (.actions)
export const SOLID_FRAG = /* glsl */ `
precision highp float;
uniform sampler2D uInk;
uniform vec2 uTexel;
uniform vec2 uSize;
uniform vec3 uColor;
uniform vec3 uHighlight;
uniform float uThreshold;
uniform float uSoft;
uniform float uOpacity;
uniform float uFeather;
uniform float uEdge;
uniform float uScale;
uniform vec4 uRects[MAX_RECTS];
uniform float uCaps[MAX_RECTS];
uniform int uRectCount;
varying vec2 vUv;
${BOX}
void main() {
  float d = texture2D(uInk, vUv).r * uScale;
  float a = smoothstep(uThreshold - uSoft, uThreshold + uSoft, d);
  if (a <= 0.002) discard;
  float dx = texture2D(uInk, vUv + vec2(uTexel.x, 0.0)).r - texture2D(uInk, vUv - vec2(uTexel.x, 0.0)).r;
  float dy = texture2D(uInk, vUv + vec2(0.0, uTexel.y)).r - texture2D(uInk, vUv - vec2(0.0, uTexel.y)).r;
  vec3 n = normalize(vec3(-dx * uScale, -dy * uScale, 0.6));
  float spec = pow(max(dot(n, normalize(vec3(-0.35, 0.55, 0.75))), 0.0), 12.0);
  vec3 col = mix(uColor, uHighlight, spec * 0.55);
  vec2 p = vec2(vUv.x, 1.0 - vUv.y) * uSize;
  float cap = 1.0;
  for (int i = 0; i < MAX_RECTS; i++) {
    if (i >= uRectCount) break;
    cap = min(cap, mix(1.0, uCaps[i], boxMask(p, uRects[i], uFeather)));
  }
  float e = min(min(p.x, uSize.x - p.x), min(p.y, uSize.y - p.y));
  a *= cap * uOpacity * smoothstep(0.0, uEdge, e);
  gl_FragColor = vec4(col * a, a);
}`;

// zone « reveal » : l'encre découvre une version bichromie (night -> accent) de la même image, avec un liseré night
export const REVEAL_FRAG = /* glsl */ `
precision highp float;
uniform sampler2D uInk;
uniform sampler2D uMap;
uniform vec2 uUvScale;
uniform vec2 uUvOffset;
uniform vec3 uDark;
uniform vec3 uLight;
uniform float uThreshold;
uniform float uSoft;
uniform float uScale;
varying vec2 vUv;
void main() {
  float d = texture2D(uInk, vUv).r * uScale;
  float a = smoothstep(uThreshold - uSoft, uThreshold + uSoft, d);
  if (a <= 0.002) discard;
  vec3 c = texture2D(uMap, vUv * uUvScale + uUvOffset).rgb;
  float l = dot(c, vec3(0.299, 0.587, 0.114));
  vec3 duo = mix(uDark, uLight, smoothstep(0.08, 0.85, l));
  float rim = 1.0 - smoothstep(uThreshold + uSoft, uThreshold + uSoft * 5.0, d);
  duo = mix(duo, uDark, rim * 0.85);
  gl_FragColor = vec4(duo * a, a);
}`;

// emblème : tuile arrondie en relief, texture = l'image de la guilde, reflet irisé discret et léger
// décalage chromatique quand elle s'incline
export const EMBLEM_VERT = /* glsl */ `
varying vec2 vUv;
varying vec3 vN;
varying vec3 vView;
void main() {
  vUv = uv;
  vec4 mv = modelViewMatrix * vec4(position, 1.0);
  vView = -mv.xyz;
  vN = normalize(normalMatrix * normal);
  gl_Position = projectionMatrix * mv;
}`;

export const EMBLEM_FRAG = /* glsl */ `
precision highp float;
uniform sampler2D uMap;
uniform vec2 uUvScale;
uniform vec2 uUvOffset;
uniform vec2 uTilt;
uniform float uTime;
uniform float uAlpha;
uniform float uSheen;
uniform float uChroma;
uniform vec3 uAccent;
uniform vec3 uNight;
varying vec2 vUv;
varying vec3 vN;
varying vec3 vView;
void main() {
  vec3 n = normalize(vN);
  vec3 v = normalize(vView);
  vec2 uv = clamp(vUv, 0.0, 1.0) * uUvScale + uUvOffset;
  vec2 ca = uTilt * uChroma;
  vec3 col = vec3(texture2D(uMap, uv + ca).r, texture2D(uMap, uv).g, texture2D(uMap, uv - ca).b);
  float fres = pow(1.0 - clamp(dot(n, v), 0.0, 1.0), 2.0);
  float ph = dot(vUv, vec2(1.2, 0.8)) + uTilt.x * 0.8 - uTilt.y * 0.6 + uTime * 0.05;
  vec3 irid = 0.5 + 0.5 * cos(6.2831 * (ph + vec3(0.0, 0.33, 0.67)));
  float band = smoothstep(0.3, 0.0, abs(fract(ph * 0.5) - 0.5));
  col += (irid * (0.05 + 0.22 * fres) + band * 0.05) * uSheen;
  float side = 1.0 - smoothstep(0.55, 0.9, abs(n.z));
  col = mix(col, mix(uNight, uAccent, 0.35 + 0.4 * fres), side);
  col *= 0.86 + 0.14 * max(dot(n, normalize(vec3(-0.4, 0.6, 1.0))), 0.0);
  gl_FragColor = vec4(col * uAlpha, uAlpha);
}`;
