# YallaQuest — intégration `integration/merged-mvc`

This branch merges every module from `restructuration-mvc` and the Objectifs module from `objectifs` into one
application with a single entry point (`index.php`).

## Run it

1. Import `back-office/src/sql/yallaquest.sql` into MySQL (it creates the `yallaquest` database).
2. Adjust the credentials in `back-office/src/config.php` if needed (default `root` / no password on `127.0.0.1`).
3. Serve the project root (XAMPP `htdocs/…`, or `php -S 127.0.0.1:8000 -t .`) and open `index.php`.

The administration (`?admin=…`) only answers local requests (127.0.0.1 / ::1), as the old `back-office/.htaccess`
required.

## Routes

| Module | Front-Office | Back-Office |
|---|---|---|
| Communauté (publications, signalements) | `index.php?page=communaute` (home) | `?admin=tableau-de-bord`, `?admin=publications`, `?admin=signalements` |
| Quêtes | `?page=quetes` | `?admin=quetes` |
| Journal d'activité | `?page=journal` | `?admin=activites`, `?admin=types-activite` |
| Compétences & Totems | `?page=competences` | `?admin=competences`, `?admin=totems` |
| Guildes | `?page=guildes` (+ `#g1`, `#quetes`, `#compte`) | `?admin=guildes` |
| Objectifs & étapes | `?page=objectifs` | `?admin=objectifs` |

Every front page starts with the shared module bar (`front-office/includes/modules-nav.php`) and every admin page
with `back-office/app/Views/partials/admin-nav.php`. Both list all the modules.

## Inventory (Phase 1)

| Module | Branch | Storage |
|---|---|---|
| Communauté: publications + signalements | restructuration-mvc | MySQL (PDO) |
| Quêtes | restructuration-mvc | static PHP arrays in the views |
| Journal: activités, types d'activité | restructuration-mvc | static HTML |
| Compétences & Totems | restructuration-mvc | static HTML |
| Guildes | restructuration-mvc | in-memory JS models (`GuildeModel.js`) |
| Objectifs & étapes | objectifs | MySQL (PDO) |

Conflicts found and how they were resolved:

| Conflict | Resolution |
|---|---|
| Two `config.php` / two `Config` classes (`_a_trier/config.php` with an empty DB name; `objectifs/config.php` with `yallaquest` and `die()`) | One `Config` in `back-office/src/config.php`, database `yallaquest`, returns `null` when MySQL is down |
| No SQL file in either branch | `back-office/src/sql/yallaquest.sql`: `publications`, `signalements` (from the model columns), `objectif`, `etape_objectif` (from the README SQL removed in b7d8759) — no duplicate tables |
| 7 entry/redirect files (`index.php` ×5, `objectifs.php` ×2) | All removed; one root `index.php` |
| Two `admin.css` (Communauté vs Objectifs) | Objectifs one renamed `admin-objectifs.css` |
| Two `effects.css` (Guildes vs Objectifs) | Objectifs one renamed `objectifs-effects.css` |
| `includes/head.php` and `includes/footer.php` meant for Compétences/Journal *and* for Guildes | Kept for Compétences/Journal (`head-competences.php`, `footer.php`); Guild versions are `head-guildes.php` / `footer-guildes.php` |
| `logo.jpeg` (Communauté) vs `logo.jpeg` (Objectifs, different image) | Both kept: `img/logo.jpeg`, `img/logo-objectifs.jpeg` |
| gsap / lenis / split-type in both branches | Byte-identical: kept once in `front-office/lib/` |
| `_a_trier/` top-level folder | Sorted into `front-office/` and `back-office/` |

## File mapping (Phase 2)

Front page templates live in `front-office/includes/pages/` because the required front-office tree has no
`Views/` folder. Font files went to `front-office/lib/fonts/`.

### From `restructuration-mvc`

| Source path | Target path |
|---|---|
| `README.md` | `README.md (unchanged)` |
| `_a_trier/Controllers/.php` | removed (empty placeholder) |
| `_a_trier/Front-Office/View/unavailable.php` | `front-office/includes/pages/unavailable.php` |
| `_a_trier/Front-Office/communaute.php` | removed — its try/catch moved into index.php (?page=communaute) |
| `_a_trier/Models/.php` | removed (empty placeholder) |
| `_a_trier/README-YallaQuest.md` | `README-YallaQuest.md` |
| `_a_trier/README-effects.md` | `README-guildes-effects.md` |
| `_a_trier/config.php` | back-office/src/config.php (rewritten as the single shared Config) |
| `_a_trier/index.php` | removed — replaced by the root index.php router |
| `_a_trier/logo.jpeg` | `front-office/img/logo.jpeg` |
| `_a_trier/shared/css/common.css` | `front-office/css/common.css` |
| `_a_trier/shared/css/guildes.css` | `front-office/css/guildes.css` |
| `_a_trier/shared/images/endurance.webp` | `front-office/img/publications/endurance.webp` |
| `_a_trier/shared/images/focus.webp` | `front-office/img/publications/focus.webp` |
| `_a_trier/shared/images/force.webp` | `front-office/img/publications/force.webp` |
| `_a_trier/shared/images/mobilite.webp` | `front-office/img/publications/mobilite.webp` |
| `_a_trier/shared/images/nutrition.webp` | `front-office/img/publications/nutrition.webp` |
| `_a_trier/shared/img/logo.png` | `front-office/img/logo.png` |
| `_a_trier/shared/js/sidebar.js` | `front-office/js/sidebar.js` |
| `_a_trier/shared/options.php` | `back-office/src/options.php` |
| `back-office/.htaccess` | `back-office/.htaccess (same place, paths updated)` |
| `back-office/app/Controllers/.htaccess` | `back-office/app/Controllers/.htaccess (same place, paths updated)` |
| `back-office/app/Controllers/GuildeController.js` | `back-office/app/Controllers/GuildeController.js (unchanged)` |
| `back-office/app/Controllers/PublicationsController.php` | `back-office/app/Controllers/PublicationsController.php (same place, paths updated)` |
| `back-office/app/Controllers/SignalementsController.php` | `back-office/app/Controllers/SignalementsController.php (same place, paths updated)` |
| `back-office/app/Models/.htaccess` | `back-office/app/Models/.htaccess (same place, paths updated)` |
| `back-office/app/Models/GuildeModel.js` | `back-office/app/Models/GuildeModel.js (unchanged)` |
| `back-office/app/Models/Publication.php` | `back-office/app/Models/Publication.php (same place, paths updated)` |
| `back-office/app/Models/Signalement.php` | `back-office/app/Models/Signalement.php (same place, paths updated)` |
| `back-office/app/Views/layout.php` | `back-office/app/Views/partials/layout.php` |
| `back-office/app/Views/pages/activites.php` | `back-office/app/Views/pages/activites.php (same place, paths updated)` |
| `back-office/app/Views/pages/competences.php` | `back-office/app/Views/pages/competences.php (same place, paths updated)` |
| `back-office/app/Views/pages/guildes.php` | `back-office/app/Views/pages/guildes.php (same place, paths updated)` |
| `back-office/app/Views/pages/publications.php` | `back-office/app/Views/pages/publications.php (same place, paths updated)` |
| `back-office/app/Views/pages/quetes.php` | `back-office/app/Views/pages/quetes.php (same place, paths updated)` |
| `back-office/app/Views/pages/signalements.php` | `back-office/app/Views/pages/signalements.php (same place, paths updated)` |
| `back-office/app/Views/pages/totems.php` | `back-office/app/Views/pages/totems.php (same place, paths updated)` |
| `back-office/app/Views/pages/types-activite.php` | `back-office/app/Views/pages/types-activite.php (same place, paths updated)` |
| `back-office/app/Views/partials/footer.php` | `back-office/app/Views/partials/footer.php (unchanged)` |
| `back-office/app/Views/partials/head.php` | `back-office/app/Views/partials/head.php (same place, paths updated)` |
| `back-office/app/Views/partials/scripts.php` | `back-office/app/Views/partials/scripts.php (same place, paths updated)` |
| `back-office/app/Views/partials/sidebar.php` | `back-office/app/Views/partials/sidebar.php (same place, paths updated)` |
| `back-office/index.php` | removed — replaced by the root index.php router |
| `back-office/src/css/admin.css` | `back-office/src/css/admin.css (unchanged)` |
| `back-office/src/css/competences-admin.css` | `back-office/src/css/competences-admin.css (unchanged)` |
| `back-office/src/css/journal-admin.css` | `back-office/src/css/journal-admin.css (unchanged)` |
| `back-office/src/js/admin-guildes.js` | `back-office/src/js/admin-guildes.js (same place, paths updated)` |
| `front-office/Controllers/CommunauteController.php` | `front-office/Controllers/CommunauteController.php (same place, paths updated)` |
| `front-office/Controllers/GuildeController.js` | `front-office/Controllers/GuildeController.js (unchanged)` |
| `front-office/Models/GuildeModel.js` | `front-office/Models/GuildeModel.js (unchanged)` |
| `front-office/Models/Publication.php` | `front-office/Models/Publication.php (same place, paths updated)` |
| `front-office/Models/Signalement.php` | `front-office/Models/Signalement.php (same place, paths updated)` |
| `front-office/Views/includes/footer.php` | `front-office/includes/footer.php` |
| `front-office/Views/includes/head.php` | `front-office/includes/head-competences.php` |
| `front-office/Views/includes/navbar.php` | `front-office/includes/navbar.php` |
| `front-office/Views/includes/scripts.php` | removed |
| `front-office/Views/includes/spinner.php` | `front-office/includes/spinner.php` |
| `front-office/Views/pages/communaute.php` | `front-office/includes/pages/communaute.php` |
| `front-office/Views/pages/competences.php` | `front-office/includes/pages/competences.php` |
| `front-office/Views/pages/guildes.php` | removed |
| `front-office/Views/pages/journal.php` | `front-office/includes/pages/journal.php` |
| `front-office/Views/pages/quetes.php` | `front-office/includes/pages/quetes.php` |
| `front-office/css/competences.css` | `front-office/css/competences.css (unchanged)` |
| `front-office/css/effects.css` | `front-office/css/effects.css (same place, paths updated)` |
| `front-office/css/front.css` | `front-office/css/front.css (unchanged)` |
| `front-office/css/journal.css` | `front-office/css/journal.css (unchanged)` |
| `front-office/css/quetes.css` | `front-office/css/quetes.css (unchanged)` |
| `front-office/img/.txt` | removed (empty placeholder) |
| `front-office/img/icons.svg` | `front-office/img/icons.svg (unchanged)` |
| `front-office/index.php` | removed — replaced by the root index.php router |
| `front-office/js/effects/Entrances.js` | `front-office/js/effects/Entrances.js (same place, paths updated)` |
| `front-office/js/effects/FloatingEmblem.js` | removed — Guild mouse effect (Phase 5) |
| `front-office/js/effects/InkField.js` | removed — Guild mouse effect (Phase 5) |
| `front-office/js/effects/Micro.js` | `front-office/js/effects/Micro.js (same place, paths updated)` |
| `front-office/js/effects/Transitions.js` | `front-office/js/effects/Transitions.js (same place, paths updated)` |
| `front-office/js/effects/main.js` | `front-office/js/effects/main.js (same place, paths updated)` |
| `front-office/js/effects/shaders.js` | removed — only used by the Guild mouse effects |
| `front-office/js/guildes.js` | `front-office/js/guildes.js (unchanged)` |
| `front-office/js/quetes.js` | `front-office/js/quetes.js (unchanged)` |
| `front-office/lib/.txt` | removed (empty placeholder) |
| `front-office/lib/gsap/ScrollTrigger.min.js` | `front-office/lib/gsap/ScrollTrigger.min.js (unchanged)` |
| `front-office/lib/gsap/gsap.min.js` | `front-office/lib/gsap/gsap.min.js (unchanged)` |
| `front-office/lib/lenis/lenis.min.js` | `front-office/lib/lenis/lenis.min.js (unchanged)` |
| `front-office/lib/splittype/split-type.min.js` | `front-office/lib/splittype/split-type.min.js (unchanged)` |
| `front-office/lib/three/three.module.min.js` | removed — only used by the Guild mouse effects |

### From `objectifs`

| Source path | Target path |
|---|---|
| `config.php` | merged into `back-office/src/config.php` (one Config class, database `yallaquest`) |
| `index.php` | removed — root index.php router |
| `Front-Office/index.php` | removed — root index.php router |
| `Back-Office/index.php` | removed — root index.php router |
| `Front-Office/objectifs.php` | removed (wrapper) — index.php?page=objectifs |
| `Back-Office/objectifs.php` | removed (wrapper) — index.php?admin=objectifs |
| `logo.jpeg` | `front-office/img/logo-objectifs.jpeg` |
| `Front-Office/assets/img/logo.jpeg` | dropped — identical to logo.jpeg |
| `Back-Office/assets/img/logo.jpeg` | dropped — identical to logo.jpeg |
| `README.md` | `README-objectifs.md` (version with SQL, from 719f558) |
| `shared/css/yq.css` | `front-office/css/yq.css` |
| `shared/css/effects.css` | `front-office/css/objectifs-effects.css` |
| `shared/css/portal.css` | `front-office/css/portal.css` |
| `shared/fonts/*.woff2 (7 files)` | `front-office/lib/fonts/*.woff2` |
| `shared/img/piliers/*.jpg (5 files)` | `front-office/img/piliers/*.jpg` |
| `shared/js/effects/effects.js` | `front-office/js/objectifs-effects.js` |
| `shared/js/ui.js` | `front-office/js/ui.js` |
| `shared/lib/gsap/*, lenis/*, splittype/* (4 files)` | dropped — byte-identical to `front-office/lib/*` |
| `Front-Office/Controller/ObjectifC.php` | `front-office/Controllers/ObjectifC.php` |
| `Front-Office/Controller/ObjectifController.php` | `front-office/Controllers/ObjectifController.php` |
| `Front-Office/Controller/AuthController.js` | `front-office/Controllers/AuthController.js` |
| `Front-Office/Controller/ObjectifController.js` | `front-office/Controllers/ObjectifController.js` |
| `Front-Office/Model/{Objectif,EtapeObjectif,Utilisateur}.{php,js}` | `front-office/Models/…` (same names) |
| `Front-Office/View/objectifs.php` | `front-office/includes/pages/objectifs.php` |
| `Front-Office/css/style.css` | `front-office/css/objectifs.css` |
| `Front-Office/js/objectifs.js` | `front-office/js/objectifs.js` |
| `Front-Office/js/validation.js` | `front-office/js/validation.js` |
| `Back-Office/Controller/ObjectifC.php + AdminController.php` | merged into `back-office/app/Controllers/AdminController.php` |
| `Back-Office/Controller/AdminController.js` | `back-office/app/Controllers/AdminController.js` |
| `Back-Office/Model/AdminModel.js` | `back-office/app/Models/AdminModel.js` |
| `Back-Office/Model/Objectif.php, EtapeObjectif.php` | `back-office/app/Models/…` (wrappers) |
| `Back-Office/View/objectifs.php` | `back-office/app/Views/pages/objectifs.php` |
| `Back-Office/css/admin.css` | `back-office/src/css/admin-objectifs.css` (renamed: clashed with the Communauté admin.css) |
| `Back-Office/js/admin-objectifs.js` | `back-office/src/js/admin-objectifs.js` |
| `Back-Office/js/admin-users.js` | `back-office/src/js/admin-users.js` |

## Fixes (Phase 4)

| File | What was wrong | Change |
|---|---|---|
| `back-office/app/Controllers/PublicationsController.php`, `SignalementsController.php` | Required `../Model/…`, `../../shared/options.php`, `../View/layout.php`, none of which existed after the restructure (fatal error) | Point to `../Models/`, `back-office/src/options.php`, `../Views/partials/layout.php`; URLs and redirects go through `index.php?admin=…` |
| `back-office/app/Controllers/PublicationsController.php` | No server-side validation: empty author/title/content and any category/type/status/image were accepted | `validate()`: required fields, max lengths matching the schema, values limited to the lists in `options.php` |
| `back-office/app/Models/Publication.php`, `Signalement.php` | Required `../../config.php` (missing) | Use the shared `back-office/src/config.php` |
| `back-office/app/Models/Signalement.php` | Any report reason and unlimited details accepted | Reason must be in `$reportReasons`, details ≤ 2000 chars (new "invalid" message in `CommunauteController`) |
| `front-office/Models/Publication.php`, `Signalement.php` | Required `../../Back-Office/Model/…` (wrong folder and letter case → fatal on Linux) | Require `back-office/app/Models/…` |
| `front-office/Controllers/CommunauteController.php` | Required `../Model/…`, `../../shared/options.php`, `../View/communaute.php` | Fixed paths |
| `back-office/app/Views/partials/layout.php` (was `Views/layout.php`) | `../logo.jpeg`, `../shared/css/*.css` → 404; required `__DIR__/publications.php` (wrong folder); links to `../Front-Office/communaute.php` and the deleted back-office index | Asset, include and link paths fixed; shared admin nav added |
| `back-office/app/Views/pages/publications.php`, `signalements.php` | GET filter forms posted to `index.php` and lost the module; images under `../shared/images/` → 404 | Hidden `admin` field; images from `front-office/img/publications/` |
| `front-office/includes/pages/communaute.php`, `unavailable.php` | `../shared/css/*`, `../logo.jpeg`, `../shared/images/*`, `../shared/icons.svg` → 404; forms and links to `communaute.php` (removed) | Root-relative assets and router links; GET search keeps `page=communaute` |
| `front-office/includes/pages/competences.php` | `require 'includes/head.php'` relative to the working directory; `css/competences.css` → 404; `img/competences/*.jpg` never existed | Absolute include; CSS path fixed; images now use the five pillar pictures in `img/piliers/` (same five skills) |
| `front-office/includes/pages/journal.php` | `css/journal.css` → 404; relative `require 'includes/footer.php'` | Fixed |
| `front-office/includes/pages/quetes.php` | `includes/assets/css|js/quetes.*` → 404; `<form action="POST">` submitted to a URL called "POST" | Paths fixed; `method="post" action="index.php?page=quetes"` |
| `back-office/app/Views/pages/quetes.php` | Included `includes/sidebar.php` (missing → fatal); assets 404; both forms posted to `/submit-post` (404); quest data echoed without escaping (XSS); broken markup `class=''actions-boutons>` and `</td></div>` | Sidebar partial, asset paths, form actions fixed; `htmlspecialchars()` on every cell; markup repaired |
| `back-office/app/Views/pages/activites.php`, `types-activite.php` | CSS `../../../src/css/journal-admin.css` → 404; `require '../partials/footer.php'` relative to the working directory | Fixed |
| `back-office/app/Views/partials/head.php` (Compétences / Totems admin) | CSS path → 404 | Fixed; shared admin nav added |
| `front-office/includes/pages/guildes.php`, `back-office/app/Views/pages/guildes.php` | Included `pages/includes/head.php`, `footer.php`, `sidebar.php`: that folder does not exist (fatal); the Guild `<head>` and footer (`#dialog`, `#toast`) were never committed, so `js/guildes.js` crashed on `dlg.showModal()` / `toast()` | Recreated `front-office/includes/head-guildes.php` and `footer-guildes.php`; includes fixed |
| `front-office/includes/scripts.php`, `back-office/app/Views/partials/scripts.php`, `navbar.php` | Loaded `../Model/GuildeModel.js`, `../Controller/…`, `../../shared/…` → 404 | Root-relative paths to `Models/`, `Controllers/`, `js/`, `lib/` |
| `back-office/src/js/admin-guildes.js` | Images from `../../shared/img/competences/*.jpg` (missing) | `front-office/img/piliers/*.jpg` |
| `back-office/app/Controllers/.htaccess`, `back-office/app/Models/.htaccess` | `Require all denied` also blocked `GuildeModel.js` / `GuildeController.js`, which the admin Guild page loads in the browser | Deny `*.php` only; same rule added to `front-office/Controllers|Models`; `Views/` and `includes/` fully denied |
| `back-office/app/Views/partials/sidebar.php` | Links were `#` or `quetes-listes.php` (non-existent); declared a global `navActive()` function | Router links to every admin module; function removed |
| `front-office/Controllers/ObjectifC.php` | `$db->query()` on a failed/missing connection → fatal; the old config fell back to a connection with no database, then died with "No database selected" | Each method returns an empty result when the DB is unavailable |
| `front-office/includes/pages/objectifs.php` | `$messageErreur` was computed but never shown (invalid forms failed silently); no length or date-format checks (a title over 150 chars made the INSERT `die()`); requires and redirects to `objectifs.php` / `../../config.php` | Error shown in an `.alert`; title ≤ 150, step ≤ 255, valid dates; router paths |
| `back-office/app/Views/pages/objectifs.php` | Same paths; invalid input silently ignored; unbounded XP | Error message shown; XP limited to 0–1000; uses `AdminController` |
| `back-office/app/Controllers/AdminController.php` | `Back-Office/Controller/ObjectifC.php` declared `class AdminController`, and `AdminController.php` only required it (confusing duplicate) | One file, `AdminController extends ObjectifC` |
| `front-office/js/objectifs.js` | AJAX/redirect to `objectifs.php?toggle_etape=` (removed) | `index.php?page=objectifs&toggle_etape=` |
| `front-office/css/yq.css` | Fonts `url(../fonts/…)` | `url(../lib/fonts/…)` |
| All view guards (`header('Location: ../index.php')`) | Pointed at removed index files | Redirect to the root router |

SQL injection: every query in the merged code was already either a prepared statement or a constant string, so no
query had to be rewritten. No duplicate PHP class or function remains (checked with `grep`). The front and admin
`GuildeModel.js` / `GuildeController.js` declare the same constants but are never loaded on the same page.

## Guild section — mouse effects removed (Phase 5)

- `front-office/js/effects/InkField.js`: WebGL ink trail following the cursor (+ `pointermove`, `pointerdown`,
  `pointerleave` listeners). Deleted.
- `front-office/js/effects/FloatingEmblem.js`: 3D emblem tilting toward the cursor. Deleted.
- `front-office/js/effects/shaders.js` and `front-office/lib/three/three.module.min.js`: only used by the two
  above. Deleted, with the `three` import map in `includes/scripts.php`.
- `front-office/js/effects/Micro.js`: card 3D tilt + illustration parallax on `pointermove`, magnetic buttons
  (`pointermove`/`pointerdown`/`pointerup`/`pointerleave`/`blur` listeners, gsap ticker). Removed.
- `front-office/js/effects/main.js`, `Entrances.js`, `Transitions.js`: ink / emblem / tilt / magnetic settings,
  Three.js loading, WebGL-lost and pointer-type (`hover: none`) handling. Removed.
- `front-office/css/effects.css`: `.fx-gl`, `.fx-ink`, `.fx-over`, `.fx-invert` (cursor ink with
  `mix-blend-mode: difference`), `.emblem.fx-emblem-live`, and the `.btn.primary` transition override used by the
  magnetic buttons. Removed.

Kept: links, buttons, the simple `:hover` colour changes and `cursor: pointer` in `guildes.css`, Lenis smooth
scrolling, entrance animations, the page-change wipe, the tab blob, the click splash on vote buttons, the toast
animation and every Guild feature (join, leave, switch, vote, propose, admin CRUD).

## Needs a human decision

- **Missing images:** `front-office/img/totems/{belier,voile,plume,arbre,graine}.jpg` are used by the
  Compétences page but exist in no branch. They still 404.
- **Missing Guild styles:** `README-guildes-effects.md` describes `Front-Office/css/front.css` (top-bar shell),
  `Back-Office/css/admin.css` and `View/includes/admin-sidebar.php` for the Guildes module. They were never
  committed, so the Guild pages run on `guildes.css` alone and the admin Guild page uses the generic admin sidebar.
  Ask the Guild author for these files.
- **No database for Quêtes, Journal, Compétences/Totems, Guildes:** these modules use hard-coded data and their
  forms do not save anything. No tables were invented for them.
- **Inferred column types:** no dump existed for `publications` / `signalements`. Check the types in the SQL file
  against your real database if you have one.
- **Unreferenced Objectifs files were kept, not deleted:** `AuthController.js`, `ObjectifController.js`,
  `Models/*.js`, `Utilisateur.php`, `back-office/app/Controllers/AdminController.js`, `Models/AdminModel.js`,
  `src/js/admin-objectifs.js`, `admin-users.js`, `js/validation.js` (it redirects to a `login.php` that does not
  exist), `js/ui.js`, `js/objectifs-effects.js`, `css/objectifs-effects.css`, `css/portal.css`. Decide whether to
  wire them up or delete them.
- **Local-only admin:** `index.php` still refuses `?admin=…` from non-local IPs, as the old `.htaccess` did. Remove
  that check before deploying to a server.
- **CDN dependencies:** the Quêtes pages load jQuery and Bootstrap from CDNs; offline, `quetes.js` fails with
  `$ is not defined`.
- **GET actions:** Objectifs delete/toggle are plain links (`?supprimer=`, `?toggle=`). This was already the case
  and was not changed.
