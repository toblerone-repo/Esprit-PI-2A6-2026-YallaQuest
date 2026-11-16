> **Mise à jour (branche `integration/merged-mvc`)** : tous les effets liés à la souris ont été retirés de la
> section Guildes : l'encre WebGL qui suivait le curseur (`InkField.js`), l'emblème 3D qui s'inclinait vers le
> curseur (`FloatingEmblem.js`), les shaders (`shaders.js`), l'inclinaison/parallaxe des cartes et les boutons
> magnétiques (`Micro.js`), ainsi que Three.js qui ne servait qu'à ces effets. Restent : défilement fluide (Lenis),
> entrées au défilement, rideau de transition entre pages, goutte des onglets, éclaboussure au clic sur un vote et
> toast élastique. Les chemins ci-dessous datent d'avant l'intégration : les pages s'ouvrent désormais par
> `index.php?page=guildes` et `index.php?admin=guildes`. Les sections qui décrivent l'encre, l'emblème,
> l'inclinaison et l'aimant ne s'appliquent plus.

# Yalla Quest · Guildes — scroll & mouse effects

Motion layer added on top of the Guildes template. No text, layout, colour or behaviour change apart from the
WAHSHI → Yalla Quest rebrand. No framework, no build step: runs with XAMPP/WAMP or `php -S`.

```bash
php -S 127.0.0.1:8000 -t .
```

Then open `http://127.0.0.1:8000/` (it redirects to the Front-Office). Administration:
`http://127.0.0.1:8000/Back-Office/View/guildes.php`.

## Structure (MVC, Front-Office / Back-Office)

```
index.php                                redirects to Front-Office/View/guildes.php

Front-Office/                            the member's side (Guildes)
  Model/GuildeModel.js                   data (sample shaped on the MCD) + read rules (membersOf, guildOf, outcome…)
  Controller/GuildeController.js         member actions: voter, rejoindre, quitter, changerDeGuilde, proposer
  View/guildes.php                       the page: sets $pageTitle, $currentPage, $pageScript, $enableEffects = true
  View/includes/head.php                 <head> (+ effects.css, only if $enableEffects); computes $root / $shared / $office
  View/includes/navbar.php               top bar: logo, pages, « Ma guilde », placeholder sections, account
                                         (no sidebar on the Front-Office; on phones the same links open as a sheet)
  View/includes/spinner.php              loading spinner, right after <body>
  css/front.css                          the Front-Office shell (top bar instead of sidebar)
  View/includes/footer.php               <dialog> + toast
  View/includes/scripts.php              Model → Controller → page script (+ effects, only if $enableEffects)
  js/guildes.js                          view script: renders the pages, handles events, calls the controller
  css/effects.css   js/effects/          scroll & mouse effects (Front-Office only)

Back-Office/                             administration (CRUD on guilds, UI only)
  Model/GuildeModel.js                   sample tables of the MLD (COMPETENCES, UTILISATEURS, GUILDES, MEMBRES)
  Controller/GuildeController.js         creer, modifier, supprimer
  View/guildes.php                       the admin page
  View/includes/head.php  sidebar.php  footer.php  scripts.php
  js/admin-guildes.js                    view script: table, filters, dialogs; calls the controller
  css/admin.css

shared/                                  used by both sides
  css/guildes.css   js/sidebar.js   img/   lib/ (gsap, lenis, splittype, three)
```

Scripts are classic scripts loaded in MVC order, so the view reads the model's constants directly and
every change goes through the controller. To plug in PHP later, replace the controller functions by
requests to a PHP controller and the model's arrays by what the PHP model returns.

The sections below were written before this reorganisation: read `View/…` as `Front-Office/View/…`,
`View/admin/guildes.php` as `Back-Office/View/guildes.php`, `View/includes/admin-sidebar.php` as
`Back-Office/View/includes/sidebar.php`, `css/guildes.css`, `js/sidebar.js`, `img/` and `lib/` as `shared/…`,
and `css/effects.css`, `js/effects/`, `js/guildes.js`, `css/admin.css`, `js/admin-guildes.js` as the same path inside
their office's folder.

The original template was one HTML file, so the includes are that file cut along the usual lines
(head / sidebar / footer / scripts). If your repo already has these includes, apply only the diffs below.

A page opts in with `$enableEffects = true;`. Without it, `head.php` and `scripts.php` output nothing extra.

## Diffs against the original template

**`<head>` → `View/includes/head.php`**
```diff
-<title>Guildes · WAHSHI</title>
+<title><?= htmlspecialchars($pageTitle ?? 'Yalla Quest') ?></title>      (page sets 'Guildes · Yalla Quest')
+<?php if (!empty($enableEffects)): ?>
+<link rel="stylesheet" href="<?= htmlspecialchars($assetBase) ?>css/effects.css">
+<script>
+  document.documentElement.classList.add('fx-boot');
+  setTimeout(function () { document.documentElement.classList.remove('fx-boot'); }, 1800);
+</script>
+<?php endif; ?>
```

**Sidebar → `View/includes/sidebar.php`**
```diff
-      <strong>WAHSHI</strong><span class="tag">GUILDES</span>
+      <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
```

**Script → `js/guildes.js`** (rebrand ×2, hook ×1 — nothing else)
```diff
-// type = one of the five WAHSHI skills: Force, Endurance, Mobilité, Focus, Nutrition
+// type = one of the five Yalla Quest skills: Force, Endurance, Mobilité, Focus, Nutrition
-  const foot = '<footer class="pagefoot"><img … >WAHSHI · « Chaque habitude est une quête. »</footer>';
+  const foot = '<footer class="pagefoot"><img … >Yalla Quest · « Chaque habitude est une quête. »</footer>';
     $('#mobile-title').textContent = title; document.title = `${title} · Guildes`;
+    document.dispatchEvent(new CustomEvent('yallaquest:render', { detail: { route: location.hash.slice(1) || 'guildes' } }));
   }
```

**Styles → `css/guildes.css`**
```diff
-/* WAHSHI · Guildes — follows the deck's charte graphique (slides 24–26):
+/* Yalla Quest · Guildes — follows the deck's charte graphique (slides 24–26):
-  --accent: #ebc1f2;          /* Lilas Wahshi: logo, primary buttons, counts */
+  --accent: #ebc1f2;          /* Lilas Yalla Quest: logo, primary buttons, counts */
 .brand strong { font-size: 19px; font-weight: 600; letter-spacing: 0.01em; }
+.brand { gap: 8px; }
+.brand strong { font-size: 17px; white-space: nowrap; }
```
The last two lines are the "does it still fit" fix: at 19px "Yalla Quest" wrapped onto two lines in the 240px
sidebar. At 17px with an 8px gap the brand and the GUILDES tag sit on one line (measured: 193px used of 196px).

**End of `<body>` → `View/includes/scripts.php`** (appended, wrapped in `$enableEffects`)
```diff
+<?php if (!empty($enableEffects)): ?>
+<script src="…lib/gsap/gsap.min.js"></script>            + CDN fallback line
+<script src="…lib/gsap/ScrollTrigger.min.js"></script>   + CDN fallback line
+<script src="…lib/lenis/lenis.min.js"></script>          + CDN fallback line
+<script src="…lib/splittype/split-type.min.js"></script> + CDN fallback line
+<script type="importmap">{ "imports": { "three": "…lib/three/three.module.min.js" } }</script>
+<script type="module" src="…js/effects/main.js"></script>
+<?php endif; ?>
```

## How it follows the template's re-renders

`render()` replaces `#canvas.innerHTML` each time, then fires `yallaquest:render`. `main.js` compares the route
with the previous one:

| Case | What plays |
|---|---|
| Route change (`#guildes` → `#g1`…) | scroll to top through Lenis, ink wipe over `#canvas`, then entrances |
| Same route (vote, tab, join/leave) | no entrance replay. The voted card's bar goes from its old widths to the new ones, the legend recounts, the pressed button gets the ink splash, the tab blob slides |

If the hook line is missing, a MutationObserver on `#canvas` does the same job.
Selectors are in `SEL` and every number in `TUNE`, both at the top of `js/effects/main.js`.

## Tuning table (`TUNE` in `js/effects/main.js`)

| Key | Value | Effect |
|---|---|---|
| `lenis.lerp` | 0.1 | scroll inertia (lower = smoother, slower) |
| `ink.fade` | 0.96 | ink kept per frame; with `ink.sub` 0.003 it is fully gone about 1.1 s after the mouse stops |
| `ink.hold` | 1500 ms | hard limit on how long a zone stays alive when idle |
| `ink.simScale` | 0.4 | simulation resolution relative to the zone (0.25–0.5) |
| `ink.noise` / `ink.noiseScale` | 1.1 / 0.012 | swirl strength (px per frame) / swirl size |
| `ink.diffuse` | 0.22 | how much blobs merge |
| `ink.threshold` / `ink.soft` | 0.3 / 0.05 | where the edge sits / how crisp it is |
| `ink.solid.radiusMin` – `radiusMax` | 16 – 52 px | trail radius, grows with mouse speed (`radiusSpeed` 0.8) |
| `ink.cursor` | true | full-screen ink that follows the mouse everywhere and inverts what it covers; `false` = the old zones (title, footer, card art) |
| `ink.look` | 'glass' | cursor ink look: `'glass'` = an almost invisible glass drop; `'solid'` = solid lilac ink that inverts the text it covers |
| `ink.glass.tint` / `rim` / `spec` / `shade` / `normal` | 0.08 / 0.6 / 0.9 / 0.3 / 3 | lilac tint, bright rim, highlight, shadow on the far edge, depth of the relief |
| `ink.cursorRadius` | 30 px | the blob that always stays under the mouse, even when it stops |
| `ink.solid.opacity` / `rim` | 1 / 0 | solid ink (zones mode at 0.26 / 0.85 kept the text readable without inversion) |
| `ink.solid.drip` | 0.6 | downward drift, px per frame |
| `ink.footGain` | 0.6 | footer ink relative to the title ink |
| `ink.reveal.radiusMin` – `radiusMax` | 20 – 60 px | reveal blobs on `.proposal .art` |
| `emblem.lerp` / `emblem.tilt` | 0.08 / 15° | emblem damping / max tilt |
| `emblem.reach` | 420 px | cursor distance that gives full tilt |
| `emblem.bob` / `sheen` | 0.03 / 0.28 | idle float / iridescent sheen strength |
| `wipe.duration` | 0.7 s | page transition |
| `wipe.entranceDelay` | 0.3 s | entrances start this long after the wipe starts |
| `wipe.wave` / `dripLength` | 26 / 110 px | waviness and drip length of the melting edge |
| `entrance.titleStagger` | 0.028 s | per letter (capped so a long title takes 0.5 s at most) |
| `entrance.textStagger` | 0.08 s | kicker, about, sub |
| `entrance.rowStagger` | 0.05 s | table rows and thumbnails |
| `entrance.cardStagger` / `cardDuration` | 0.12 / 0.95 s | proposal cards |
| `entrance.panelStagger` | 0.1 s | rail panels |
| `entrance.barDuration` / `voteDuration` | 0.9 / 0.6 s | bars on entrance / bar after a vote |
| `tilt.max` / `tilt.parallax` | 4° / 6 px | card tilt / art parallax |
| `magnetic.max` / `strength` / `release` | 6 px / 0.3 / 0.8 s | magnetic pull and elastic return |
| `tabs.duration` | 0.5 s | tab blob morph |
| `splash.duration` / `drops` | 0.55 s / 5 | vote button ink splash |

## Test checklist

Run on desktop (wide window, mouse), then at phone width.

- [ ] "Yalla Quest" in the sidebar (one line, next to GUILDES) and in the footer line; search the project for `WAHSHI`: no result
- [ ] Tab title: `Guildes · Yalla Quest` in the page source. Once the script runs it becomes `<page> · Guildes`, as before (see note)
- [ ] `#guildes`, `#g1`, `#quetes`, `#compte`: ink wipe drips down over the canvas, sidebar does not move, title letters rise, rows / cards come in
- [ ] Anywhere on the page: a solid lilac blob stays under the mouse, even when it stops, with a dripping trail; it melts ~1.5 s after the mouse leaves the window
- [ ] Glass look: the drop is almost clear, with a faint lilac tint, a light rim and highlight (light from the top left) and a soft shadow on the far edge; text under it stays readable; buttons and links under it still click
- [ ] With `ink.look: 'solid'`: where the ink touches text it inverts (white text turns dark, black card text turns lilac)
- [ ] Desktop: the button in the top-left corner hides the sidebar (it slides out, the page takes the full width) and shows it again; the choice is remembered after a reload; under 860px the button is hidden and ☰ works as before
- [ ] `#g1`: the emblem tilts toward the cursor
- [ ] Vote Pour, then Contre: bar animates from old to new widths, counts update, button gets the ink splash, toast squashes in, nothing replays, focus stays on the button
- [ ] Switch tabs: the white pill stretches to the new tab
- [ ] "Proposer une quête": page behind does not scroll (`html.fx-locked`, Lenis stopped), dialog fields scroll and type normally; closing restores scrolling
- [ ] Phone width: open and close the menu sheet; no ink following, no tilt; entrances, wipe and vote bar still play
- [ ] Keyboard: Tab through sidebar, tabs, Pour/Contre, "voir les votes"; focus ring always visible; a card below the fold reveals itself when focus reaches it
- [ ] OS setting "reduce motion": no Lenis, no canvas, no wipe; route change is a 150 ms fade
- [ ] Set `$enableEffects = false` in `View/guildes.php`: no effects file is loaded, page is the original

## Boutons ajoutés (placeholders, sans action pour le moment)

| Où | Quoi | Fichiers |
|---|---|---|
| Haut de chaque page | Barre `.topnav` : Quêtes quotidiennes, Guilde, Journal d'activité, Publications (`data-soon`, aucun gestionnaire) | `View/includes/navbar.php`, `View/guildes.php` (colonne `.main-col`), fin de `css/guildes.css` |
| Toutes les guildes | « Créer une guilde » toujours visible ; le formulaire (nom, description) se valide puis se ferme avec un toast, sans rien créer | `js/guildes.js` (`guildsView`, `guildDialog`) |
| Ma guilde, sous la liste des membres | « Quitter la guilde » (tout membre) et « Supprimer la guilde » (fondateur seulement), `data-soon`, aucun gestionnaire | `js/guildes.js` (`guildView`) |

Pour les brancher plus tard : écouter `[data-soon="…"]` dans le gestionnaire de clics de `js/guildes.js`, ou remplacer
les `<button>` de la barre par des liens vers les pages. L'ancien « Quitter la guilde » qui retirait vraiment le membre a
été remplacé par la version sans action ; sa fonction `leave()` et le cas `data-leave` sont toujours dans le script.

## Administration des guildes (back-office, interface seulement)

Adresse : `View/admin/guildes.php` (ex. `http://127.0.0.1:8000/View/admin/guildes.php`).

| Opération | Interface |
|---|---|
| Lire (liste) | tableau trié par nom : guilde, compétence, créateur et date, membres / 10, statut ; recherche (nom, description, créateur) et filtre par statut |
| Lire (détail) | « Voir » : compétence, créateur, création, membres avec leur date d'adhésion |
| Créer | « Nouvelle guilde » : nom, description, compétence, statut, créateur |
| Modifier | « Modifier » : nom, description, compétence, statut (le créateur ne change pas) |
| Supprimer | « Supprimer » : confirmation qui annonce le nombre de membres retirés |

Règles appliquées dans l'interface : nom obligatoire et unique (sans tenir compte des majuscules), description ≤ 160
caractères, 10 membres au plus, un utilisateur dans une seule guilde (les utilisateurs qui ont déjà une guilde sont
grisés dans « Créateur » ; supprimer une guilde libère ses membres).

**Pas de base ni de PHP métier.** Les données sont un échantillon en mémoire dans `js/admin-guildes.js` (15 utilisateurs
du front, plus 3 sans guilde pour pouvoir tester la création) : tout revient à l'état initial au rechargement.
Pour brancher le backend plus tard :
- chaque fenêtre contient un vrai `<form method="post">` dont les champs portent les noms des colonnes du MLD
  (`id`, `nom`, `description`, `id_competence`, `id_createur`, `statut`), avec `data-form="create|update|delete"` ;
- retirer les deux `e.preventDefault()` marqués « à retirer » et mettre l'URL du contrôleur dans `action` ;
- remplacer les tableaux `GUILDES`, `MEMBRES`, `UTILISATEURS`, `COMPETENCES` par les données du modèle ;
- `id_competence` et `date_creation` ne sont pas dans la table Guilde du MLD : à ajouter (module Guilde) ou à retirer ;
- les statuts proposés (`active`, `suspendue`, `archivee`) sont à aligner sur les valeurs de `Guilde.statut` ;
- l'accès réservé à l'admin (`Utilisateur.role`) n'est pas contrôlé : à faire côté PHP avec la session.

Fichiers : `View/admin/guildes.php`, `View/includes/admin-sidebar.php`, `css/admin.css`, `js/admin-guildes.js`,
`img/competences/*.jpg` (les cinq images de compétence, sorties du base64 du template). `head.php` calcule maintenant
le chemin des ressources aussi depuis `View/admin/`. Les effets (encre, transitions) sont coupés sur l'admin
(`$enableEffects = false`).

## Notes

- **Glass look (default).** The drop is drawn in WebGL from the shape of the ink: a light tint, a rim, a highlight and
  a shadow. It does not bend the text behind it: a WebGL canvas cannot read the page under it, and the CSS way
  (`backdrop-filter` masked to a moving shape) is not possible with a live canvas. The text stays sharp under the glass.
- **Cursor ink covers controls (your request).** The original rule was "ink never covers controls". The ink is now
  one full-screen layer drawn above the page with `mix-blend-mode: difference`, which is what inverts the text.
  It stays `pointer-events: none`, so every button, link, tab and field still works underneath it, and
  the inversion keeps them readable. On the ivory cards the ink reads dark green (lilac inverted against ivory).
  Set `ink.cursor: false` to go back to the zone version.
- **Sidebar button.** `View/includes/sidebar.php` (the button), `css/guildes.css` (styles at the end) and
  `js/sidebar.js` (loaded by `scripts.php` on every page). It is part of the template, not the effects, so it
  works with `$enableEffects = false` too. On desktop the sidebar now has 64px of top padding so the button
  sits above the logo; with the sidebar hidden, the page gets 72px of top padding so the title clears the button.

- **Zones mode only:** title ink is translucent there because solid `--accent` behind white text would drop the title to about
  1.6:1 contrast. The ink body is drawn at 26% with a thin bright edge: white text stays above 12:1 and the grey
  lines at about 5:1 over the ink body (computed, not measured with a tool). Raise `ink.solid.opacity` if you
  prefer thicker ink over contrast.
- **Tab title.** The template's `render()` sets `document.title = \`${title} · Guildes\``, so the brand never
  showed in the tab once the script ran. It was left alone (only one logic line was allowed). To show the brand
  there, change that string to `\`${title} · Yalla Quest\``.
- **No cursor ink on touch / under 860px**, and no 3D emblem there (the `<img>` stays). A tap inside the title or
  footer gives a short burst.
- **Three.js is 0.160** because its `three.module.min.js` is a single file; newer versions need a second file.
