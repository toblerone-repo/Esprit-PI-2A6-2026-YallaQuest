// Yalla Quest · effets — GLSL en chaînes exportées.
// Les couleurs arrivent en uniforms depuis les variables CSS (--accent, --night) : aucun hex ici.

// Simplex noise 2D — Ian McEwan / Ashima Arts (MIT).
const SNOISE = /* glsl */ `
vec3 mod289(vec3 x){ return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec2 mod289(vec2 x){ return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec3 permute(vec3 x){ return mod289(((x * 34.0) + 1.0) * x); }
float snoise(vec2 v){
  const vec4 C = vec4(0.211324865405187, 0.366025403784439, -0.577350269189626, 0.024390243902439);
  vec2 i = floor(v + dot(v, C.yy));
  vec2 x0 = v - i + dot(i, C.xx);
  vec2 i1 = (x0.x > x0.y) ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
  vec4 x12 = x0.xyxy + C.xxzz;
  x12.xy -= i1;
  i = mod289(i);
  vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0)) + i.x + vec3(0.0, i1.x, 1.0));
  vec3 m = max(0.5 - vec3(dot(x0, x0), dot(x12.xy, x12.xy), dot(x12.zw, x12.zw)), 0.0);
  m = m * m; m = m * m;
  vec3 x = 2.0 * fract(p * C.www) - 1.0;
  vec3 h = abs(x) - 0.5;
  vec3 ox = floor(x + 0.5);
  vec3 a0 = x - ox;
  m *= 1.79284291400159 - 0.85373472095314 * (a0 * a0 + h * h);
  vec3 g;
  g.x = a0.x * x0.x + h.x * x0.y;
  g.yz = a0.yz * x12.xz + h.yz * x12.yw;
  return 130.0 * dot(m, g);
}
`;

// Plein écran (le viewport/scissor découpe la zone) : pas de matrices.
export const quadVert = /* glsl */ `
varying vec2 vUv;
void main(){
  vUv = uv;
  gl_Position = vec4(position.xy, 0.0, 1.0);
}
`;

// Simulation d'encre (ping-pong). Canal R = densité 0..1.
// Unités en pixels CSS de la zone, origine en bas à gauche.
export const simFrag = /* glsl */ `
precision highp float;
varying vec2 vUv;
uniform sampler2D uPrev;
uniform vec2 uSize;        // taille de la zone (px)
uniform vec2 uTexel;       // 1 / taille de la cible de rendu
uniform vec4 uSeg[4];      // jusqu'à 4 éclaboussures : segment a.xy -> b.xy (px)
uniform vec2 uSplat[4];    // rayon (px), quantité (0 = inactif)
uniform float uFade;       // fondu multiplicatif par image
uniform float uSub;        // fondu soustractif : garantit le retour à zéro
uniform float uNoise;      // advection par le bruit (px / image)
uniform float uScale;      // fréquence du bruit (1 / px)
uniform float uDrip;       // dérive vers le bas (px / image)
uniform vec2 uShift;       // déplacement du contenu sous la zone (défilement), px
uniform float uDiffuse;    // 0..1 : mélange avec les voisins (blobs qui fusionnent)
uniform float uEdge;       // px d'amortissement aux bords (0 = aucun)
uniform float uTime;
${SNOISE}
vec2 curl(vec2 p){
  float e = 0.35;
  float a = snoise(p + vec2(0.0, e)), b = snoise(p - vec2(0.0, e));
  float c = snoise(p + vec2(e, 0.0)), d = snoise(p - vec2(e, 0.0));
  return vec2(a - b, -(c - d)) / (2.0 * e);
}
float sdSeg(vec2 p, vec2 a, vec2 b){
  vec2 pa = p - a, ba = b - a;
  float h = clamp(dot(pa, ba) / max(dot(ba, ba), 0.0001), 0.0, 1.0);
  return length(pa - ba * h);
}
void main(){
  vec2 px = vUv * uSize;
  vec2 flow = curl(px * uScale + vec2(0.0, uTime * 0.12)) * uNoise;
  flow.y -= uDrip;
  flow += uShift;
  vec2 src = (px - flow) / uSize;
  float d = texture2D(uPrev, src).r;
  float n = texture2D(uPrev, src + vec2(uTexel.x, 0.0)).r + texture2D(uPrev, src - vec2(uTexel.x, 0.0)).r
          + texture2D(uPrev, src + vec2(0.0, uTexel.y)).r + texture2D(uPrev, src - vec2(0.0, uTexel.y)).r;
  d = mix(d, n * 0.25, uDiffuse);
  d = max(d * uFade - uSub, 0.0);
  for (int i = 0; i < 4; i++) {
    if (uSplat[i].y > 0.0) {
      float s = sdSeg(px, uSeg[i].xy, uSeg[i].zw);
      d += uSplat[i].y * (1.0 - smoothstep(uSplat[i].x * 0.35, uSplat[i].x, s));
    }
  }
  if (uEdge > 0.0) {
    vec2 m = min(px, uSize - px);
    d *= smoothstep(0.0, uEdge, min(m.x, m.y));
  }
  gl_FragColor = vec4(min(d, 1.0), 0.0, 0.0, 1.0);
}
`;

// Affichage « solid » : encre lilas, bord net et gluant (seuil smoothstep), corps translucide + liseré.
// Sortie en alpha prémultiplié.
export const solidFrag = /* glsl */ `
precision highp float;
varying vec2 vUv;
uniform sampler2D uInk;
uniform vec2 uSize;
uniform vec3 uColor;
uniform float uOpacity;    // opacité du corps de l'encre
uniform float uRim;        // 0..1 : renfort du liseré
uniform float uThreshold;
uniform float uSoft;
uniform float uTime;
${SNOISE}
void main(){
  float d = texture2D(uInk, vUv).r;
  d += snoise(vUv * uSize * 0.014 + vec2(0.0, uTime * 0.25)) * 0.06 * smoothstep(0.04, 0.4, d);
  float a = smoothstep(uThreshold, uThreshold + uSoft, d);
  float rim = a * (1.0 - smoothstep(uThreshold + uSoft, uThreshold + uSoft + 0.16, d));
  float alpha = a * mix(uOpacity, 1.0, rim * uRim);
  gl_FragColor = vec4(uColor * alpha, alpha);
}
`;

// Affichage « reveal » : l'encre découvre la même image en bichromie --night / --accent.
export const revealFrag = /* glsl */ `
precision highp float;
varying vec2 vUv;
uniform sampler2D uInk;
uniform sampler2D uImage;
uniform vec2 uSize;
uniform vec2 uCover;       // échelle UV pour reproduire object-fit: cover
uniform vec3 uDark;
uniform vec3 uLight;
uniform float uThreshold;
uniform float uSoft;
uniform float uTime;
${SNOISE}
void main(){
  float d = texture2D(uInk, vUv).r;
  d += snoise(vUv * uSize * 0.02 + vec2(0.0, uTime * 0.25)) * 0.06 * smoothstep(0.04, 0.4, d);
  float a = smoothstep(uThreshold, uThreshold + uSoft, d);
  vec3 img = texture2D(uImage, (vUv - 0.5) * uCover + 0.5).rgb;
  float l = dot(img, vec3(0.299, 0.587, 0.114));
  vec3 col = mix(uDark, uLight, smoothstep(0.06, 0.82, l));
  float rim = a * (1.0 - smoothstep(uThreshold + uSoft, uThreshold + uSoft + 0.12, d));
  col = mix(col, uLight, rim * 0.6);
  gl_FragColor = vec4(col * a, a);
}
`;

// Affichage « glass » : une goutte de verre presque invisible. Le relief vient du gradient de densité :
// léger voile lilas, liseré lumineux (Fresnel), reflet spéculaire (lumière en haut à gauche), ombre sur le bord opposé.
export const glassFrag = /* glsl */ `
precision highp float;
varying vec2 vUv;
uniform sampler2D uInk;
uniform vec2 uTexel;
uniform vec3 uTint;
uniform float uThreshold;
uniform float uSoft;
uniform float uTintAmt;    // opacité du voile coloré
uniform float uRimAmt;     // liseré lumineux
uniform float uSpec;       // reflet
uniform float uShade;      // ombre côté opposé à la lumière
uniform float uNormal;     // relief (force des normales)
float hgt(vec2 uv){ return smoothstep(uThreshold, 1.0, texture2D(uInk, uv).r); }
void main(){
  float d = texture2D(uInk, vUv).r;
  float a = smoothstep(uThreshold, uThreshold + uSoft, d);
  if (a < 0.002) { gl_FragColor = vec4(0.0); return; }
  vec2 e = uTexel * 1.5;
  float hx = hgt(vUv + vec2(e.x, 0.0)) - hgt(vUv - vec2(e.x, 0.0));
  float hy = hgt(vUv + vec2(0.0, e.y)) - hgt(vUv - vec2(0.0, e.y));
  vec3 n = normalize(vec3(-hx * uNormal, -hy * uNormal, 1.0));
  vec3 L = normalize(vec3(-0.45, 0.55, 0.7));
  float spec = pow(max(dot(reflect(-L, n), vec3(0.0, 0.0, 1.0)), 0.0), 36.0) * uSpec;
  float fres = pow(1.0 - n.z, 1.6) * uRimAmt;
  float edge = 1.0 - smoothstep(uThreshold + uSoft, uThreshold + uSoft + 0.18, d);  // fin liseré au contour
  float light = clamp(spec + fres * max(dot(n.xy, L.xy) * 0.5 + 0.6, 0.0) + edge * 0.35, 0.0, 1.0);
  float shade = clamp(max(dot(n.xy, -L.xy), 0.0) * uShade * 1.6, 0.0, 0.6);
  float alpha = clamp(uTintAmt + light + shade, 0.0, 1.0) * a;
  vec3 col = (uTint * uTintAmt + vec3(light)) * a;                  // l'ombre n'ajoute que de l'alpha (noir)
  gl_FragColor = vec4(min(col, vec3(alpha)), alpha);
}
`;

// Tuile de l'emblème (plan arrondi en perspective).
export const emblemVert = /* glsl */ `
varying vec2 vUv;
void main(){
  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
}
`;

export const emblemFrag = /* glsl */ `
precision highp float;
varying vec2 vUv;
uniform sampler2D uMap;
uniform vec2 uCover;       // object-fit: cover
uniform float uRadius;     // rayon des coins, en fraction du côté
uniform float uPx;         // un pixel écran, en fraction du côté (anti-crénelage)
uniform vec2 uTilt;        // inclinaison normalisée -1..1
uniform float uTime;
uniform float uSheen;
uniform float uAlpha;
uniform float uBack;       // 1 = plaque arrière (épaisseur simulée)
uniform vec3 uAccent;
uniform vec3 uNight;
float sdRound(vec2 p, vec2 b, float r){
  vec2 q = abs(p) - b + r;
  return length(max(q, 0.0)) + min(max(q.x, q.y), 0.0) - r;
}
void main(){
  vec2 p = vUv - 0.5;
  float sd = sdRound(p, vec2(0.5), uRadius);
  float mask = 1.0 - smoothstep(-uPx * 1.5, 0.0, sd);
  vec2 uvc = p * uCover + 0.5;
  vec2 ca = uTilt * 0.014;                       // léger décalage chromatique, dans le sens de l'inclinaison
  vec3 col = vec3(texture2D(uMap, uvc + ca).r, texture2D(uMap, uvc).g, texture2D(uMap, uvc - ca).b);
  float band = dot(p, vec2(0.7071)) + (uTilt.x - uTilt.y) * 0.55 + sin(uTime * 0.45) * 0.18;
  float s = exp(-band * band * 16.0);
  vec3 irid = 0.5 + 0.5 * cos(6.2831 * (band * 1.4 + vec3(0.0, 0.33, 0.67)) + uTime * 0.3);
  col += mix(irid, uAccent, 0.55) * s * uSheen;
  col += uAccent * smoothstep(-0.035, 0.0, sd) * 0.22;   // arête éclairée
  col = mix(col, mix(uNight, uAccent, 0.3), uBack);
  float a = mask * uAlpha;
  gl_FragColor = vec4(col * a, a);
}
`;
