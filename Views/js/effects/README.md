# Yalla Quest · Guildes — espace membre, chargement, en-tête d'administration et effets

Ma partie :

| Fichier | Rôle |
|---|---|
| `Views/front-office/includes/spinner.php` | indicateur de chargement, commun aux pages du front-office |
| `Views/back-office/partials/header.php` | en-tête commun des pages du back-office (tous les membres) |
| `Views/back-office/pages/guilde_admin.php` | administration des guildes (statique, même gabarit que le back-office, aucun effet) |
| `Views/front-office/pages/guildes.php` | la page Guildes de l'espace membre, en HTML statique (+ `spinner.php`) : vues `#guildes`, `#g1`…`#g5`, `#quetes` |
| `Views/img/competences/` | les 5 vignettes de compétences (force, endurance, mobilité, focus, nutrition) |
| `Views/css/guilde.css` | feuille de style unique du projet (voir « Feuille de style ») |
| `Views/js/effects/`, `Views/lib/gsap/`, `lenis/`, `splittype/`, `three/` | effets de scroll et de souris |

La page n'a plus ni `<style>`, ni attribut `style=""`, ni JavaScript à elle : seul le JavaScript des effets reste. Changements de mise en page : le menu latéral est remplacé par un bandeau de navigation dans le corps de la page, sous le titre ; la partie « Mon compte » est retirée.

## Ouvrir la page

Avec XAMPP/WAMP, ou depuis la racine du projet :

```
php -S localhost:8000
```

puis `http://localhost:8000/Views/front-office/pages/guildes.php`.

## Indicateur de chargement (front-office) — `includes/spinner.php`

Juste après `<body>` d'une page PHP de `Views/front-office/pages/` (c'est le cas de `guildes.php`) :

```php
<?php require __DIR__ . '/../includes/spinner.php'; ?>
```

`spinner.php` ne contient que le balisage : ses styles sont dans `Views/css/guilde.css` (partie « indicateur de chargement », chargée par toutes les pages) et son script dans `Views/js/spinner.js` (inclus par `spinner.php`). Il couvre la page jusqu'à l'événement `load`, s'efface en 0,4 s puis envoie l'événement `yallaquest:loaded` (une page peut l'écouter pour lancer ses animations ; les effets de `guildes` l'attendent, 4 s au plus). Sans JavaScript il n'apparaît pas (masqué par défaut, affiché par le script) ; il disparaît de toute façon au bout de 8 s. Mouvement réduit : pas de rotation. Couleurs : `--ground`, `--accent`, `--muted` de la page (valeurs de repli sinon). Aucune variable PHP à définir.

## En-tête commun du back-office — `partials/header.php`

Pour toutes les pages d'administration, de tous les membres :

```php
<?php
$currentPage = 'utilisateurs';                                // clé du lien actif
$pageTitle   = 'Aventuriers · Administration · Yalla Quest';
require __DIR__ . '/../partials/header.php';
?>
<div class="app"><main class="canvas" id="canvas" tabindex="-1"> … </main></div>
```

Il ouvre le document (feuille `guilde.css`, chemins relatifs depuis `Views/back-office/pages/`, aucun effet de mouvement) et affiche la barre « Yalla Quest · ADMINISTRATION » avec un lien par page. **Chaque membre ajoute sa page** dans le tableau `$adminPages` en haut du fichier : `'clé' => ['Libellé', 'fichier.php']`. Le lien dont la clé vaut `$currentPage` est marqué `aria-current="page"` (pastille blanche). Titre par défaut : « Administration · Yalla Quest ».

## Feuille de style — `Views/css/guilde.css`

Le dossier `Views/css/` ne contient plus qu'un fichier. `guilde.css` réunit, dans cet ordre et sans changer une déclaration, les anciens `yq.css` (commun), `style.css` (connexion / inscription), `admin.css` (back-office) et `effects.css` (effets Guildes). `header.php` et `guildes.php` le chargent ; **les pages des autres membres qui chargeaient `yq.css`, `style.css` ou `admin.css` doivent charger `../../css/guilde.css` à la place** (une seule ligne).

La page Guildes n'a plus de `<style>` : ses styles (l'ancien `<style>` du template, sans ses polices en base64 — ce sont les mêmes que `Views/lib/fonts/`) sont dans la dernière partie de `guilde.css`, préfixés par `:where(html[data-yq-page="guildes"])`. Elle porte `data-yq-page="guildes"` sur `<html>`, et les sélecteurs des règles communes sont préfixés par `:where(html:not([data-yq-page="guildes"]))`. `:where()` ne compte pas dans la spécificité : sur toutes les autres pages, la cascade est exactement celle des anciens fichiers. Vérifié dans le navigateur en comparant tous les styles calculés, ancien CSS contre `guilde.css` : connexion, inscription et administration des aventuriers identiques (0 différence sur 288 400 valeurs) ; page Guildes identique sur ses 4 routes, à 1440 px et à 375 px (plus d'un million de valeurs, seule différence : l'ondulation aléatoire d'une carte pas encore révélée).
Nouvelle règle commune : lui donner le même préfixe. Nouvelle règle pour la page Guildes : sans préfixe (partie effets, en fin de fichier) ou dans le `<style>` de la page.

## Fichiers des effets

| Fichier | Rôle |
|---|---|
| `Views/css/guilde.css` (avant-dernière partie) | styles des effets (tout sous `html.fx`, couleurs = variables du template) |
| `Views/js/effects/main.js` | détection des capacités, sélecteurs (`SEL`), réglages (`TUNE`), Lenis + GSAP (une seule boucle `gsap.ticker`), suivi de la vue active (`hashchange`) |
| `Views/js/effects/Entrances.js` | titre SplitType, textes, cartes (bord liquide + ScrollTrigger `once`), barres de vote, compteurs, lignes de tableau, jauge |
| `Views/js/effects/Transitions.js` | vague d'encre lilas puis noire sur `#canvas` au changement de route |
| `Views/js/effects/InkField.js` | canvas WebGL partagé, simulation d'encre par zone (ping-pong), modes `solid` et `reveal` |
| `Views/js/effects/FloatingEmblem.js` | tuile 3D de l'emblème (même canvas, même renderer) |
| `Views/js/effects/Micro.js` | inclinaison des cartes, aimant, pastille d'onglet, tache d'encre du vote, toast |
| `Views/js/effects/shaders.js` | GLSL (chaînes exportées) |
| `Views/lib/gsap/` · `lenis/` · `splittype/` · `three/` | copies locales : GSAP 3.12.5 + ScrollTrigger, Lenis 1.1.14, SplitType 0.3.4, Three.js r170 (`three.module.min.js`, un seul fichier). CDN jsDelivr en secours. |

Désactiver les effets : dans `guildes.php`, retirer le script `fx-boot` du `<head>` et le bloc « Effets » en bas de page. Les styles des effets restent dans `guilde.css` mais ne s'appliquent qu'une fois `main.js` lancé (classe `html.fx`) : la page redevient celle du template.

## Choix à connaître

- **Page statique.** Le JavaScript du template (données, rendu, routeur, votes, onglets, fenêtres, rejoindre / quitter) est supprimé. Chaque vue est écrite en HTML dans `guildes.php` : une `<section class="yq-view" id="…">` par adresse (`#guildes`, `#g1`…`#g5`, `#quetes`), dans l'état de départ des données d'exemple (onglet « En vote », aucun vote ajouté). La section ciblée par l'adresse s'affiche (CSS `:target`), `#g1` par défaut, et toute autre adresse (`#compte`…) ouvre aussi `#g1`. Les boutons de vote, les onglets, « Proposer une quête », « Rejoindre » et « Quitter » sont affichés mais ne font plus rien ; les liens (`Ouvrir`, `Voir`, navigation) et « voir les votes » (`<details>`) marchent. Identifiants dupliqués entre vues suffixés (`h-mem--g2`…). Anciens `style=""` → classes `yq-w-…` (largeurs, avec `data-w` lu par les effets) et `yq-st-…` dans `guilde.css`.
- **Navigation dans le corps de la page.** Un bandeau `.guildnav` (« Yalla Quest · GUILDES » sans logo, « Toutes les guildes », « Quêtes », « Ma guilde », l'utilisateur) sous le titre de chaque vue ; chaque copie porte déjà son lien actif (`aria-current="page"`).
- **Plus de « Mon compte ».** La vue `accountView()`, la route `#compte` (une adresse `#compte` ouvre maintenant la page par défaut, comme toute adresse inconnue), le lien de navigation, les actions « Supprimer mon compte » et l'enregistrement des formulaires du compte, ainsi que leurs styles (`.settings`, `.setting`, `.form`, `.form-foot`) sont retirés.
- **Titre de l'onglet.** « Guildes · Yalla Quest » pour toutes les vues (plus de script pour le changer).
- **Un renderer, deux couches.** Le canvas WebGL est fixe et *derrière* le contenu : l'encre du titre y est dessinée directement (scissor sur la zone). L'image `.art` est sous le fond ivoire de la carte : la zone `reveal` est rendue hors écran dans le même canvas puis copiée dans un petit canvas 2D posé sur l'image (il suit l'inclinaison de la carte).
- **Contraste.** Sous chaque ligne de texte du titre, l'opacité de l'encre est plafonnée : h1 0,42 (≈ 6,5:1), `.sub` 0,40, `.kicker` et `.about` 0,20 (texte gris, ≈ 6:1). Elle est nulle sous `.actions`.
- **Haut de page.** Au changement d'adresse, la vue s'ouvre en haut (CSS `scroll-margin-top`, et Lenis recalé sur 0).
- **Vue active.** Les effets s'attachent à la section visible (`.yq-view:target`, sinon `.yq-default`) et se rattachent à la nouvelle à chaque `hashchange` (vague, entrées).


## Réglages (`TUNE` en haut de `main.js`)

| Réglage | Valeur | Effet |
|---|---|---|
| `lenis.lerp` | 0,1 | inertie du défilement (plus petit = plus glissant) |
| `ink.fade` | 0,96 / image (60 i/s) | vitesse de fonte de l'encre |
| `ink.life` | 2,2 s | après ce délai sans geste, la simulation s'arrête et se vide |
| `ink.radius` · `radiusMin` · `radiusMax` · `radiusSpeed` | 30 · 16 · 80 px · +0,03 px par px/s | taille des taches selon la vitesse de la souris |
| `ink.amount` · `amountSpeed` · `amountMax` | 0,5 · +0,0014 par px/s · 1,25 | quantité d'encre par tache |
| `ink.threshold` · `ink.soft` | 0,32 · 0,07 | seuil smoothstep : bord net et gluant |
| `ink.noise` · `ink.noiseScale` | 0,11 · 2,3 | force et échelle du tourbillon (curl noise) |
| `ink.drip` | 0,22 | gravité sur l'encre épaisse (gouttes) |
| `ink.drag` | 0,08 | traînée de l'encre quand on défile (part de la vitesse de scroll) |
| `ink.simScale` · `maxSim` | 0,35 (0,49 pour `reveal`) · 512 px | résolution de la simulation |
| `ink.capTitle` · `capSub` · `capMuted` | 0,42 · 0,40 · 0,20 | opacité maximale de l'encre sous le texte |
| `ink.feather` · `ink.edge` | 14 · 14 px | adoucissement des plafonds et des bords de zone |
| `ink.titleReach` | 28 px | débord de la zone du titre dans la marge vide en dessous |
| `ink.tapBurst` | 6 taches | giclée au toucher (tactile, écran étroit) |
| `emblem.tilt` · `emblem.lerp` | 15° · 0,08 | inclinaison maximale et amortissement de l'emblème |
| `emblem.bob` · `bobSpeed` | 0,025 · 1,1 | flottement au repos |
| `emblem.sheen` · `chroma` | 1 · 0,004 | reflet irisé et décalage chromatique |
| `emblem.introDuration` | 1,1 s | apparition de la tuile |
| `wipe.duration` · `wipe.revealAt` | 0,7 s · 0,3 s | durée de la vague ; début des entrées |
| `wipe.drips` · `dripDepth` · `points` | 7 · 11 % · 64 | gouttes du bord qui fond |
| `enter.titleDuration` · `titleStagger` | 0,9 s · 0,022 s | lettres du titre |
| `enter.textStagger` · `blockStagger` · `railStagger` | 0,08 · 0,08 · 0,1 s | kicker/about/sub ; blocs ; colonne de droite |
| `enter.cardDuration` · `cardStagger` · `cardWave` | 0,95 s · 0,12 s · 7 % | révélation liquide des cartes |
| `enter.rowStagger` · `rowY` · `imgScale` | 0,05 s · 16 px · 0,6 | lignes de tableau et vignettes |
| `enter.barDuration` · `countDuration` · `updateBar` | 0,9 · 0,9 · 0,6 s | barres de vote (entrée / après un vote) |
| `echoWindow` | 700 ms | re-rendu de la même route rattaché au changement de route |
| `tilt.max` · `tilt.lerp` · `parallax` · `artScale` | 4° · 0,12 · 6 px · 1,06 | cartes |
| `magnetic.max` · `strength` · `release` | 6 px · 0,3 · 0,9 s (élastique) | aimant |
| `tabs.lead` · `trail` · `lag` · `squash` | 0,32 · 0,44 · 0,07 s · 0,86 | pastille d'onglet |
| `splash.duration` · `toast.sx` · `toast.sy` | 0,42 s · 1,16 · 0,8 | tache du vote ; écrasement du toast |

## Liste de tests

Ouvrir `Views/front-office/pages/guildes.php` (voir « Ouvrir la page »).

- [ ] `guildes.php` : aucun `<style>`, aucun attribut `style="`, un seul bloc de scripts (les effets) ; `Views/img/competences/` contient les 5 vignettes.
- [ ] Au chargement : l'indicateur (encre lilas, « Chargement… ») couvre la page puis s'efface ; la vague et l'entrée des effets jouent ensuite.
- [ ] Sans adresse : la page de la guilde « Les Lanternes ». `#guildes`, `#g1`…`#g5`, `#quetes` : une seule vue visible, ouverte en haut ; `#compte` ou une adresse inconnue : « Les Lanternes ». Liens « Ouvrir » / « Voir » et navigation : changent de vue.
- [ ] Chaque changement de vue : vague lilas puis noire, titre lettre par lettre, textes, cartes révélées au défilement, barres de vote qui se remplissent, lignes de tableau.
- [ ] Bandeau de navigation sous le titre de chaque vue, lien courant en pastille blanche, pas de logo, pas de « Mon compte » ; à 375 px les liens défilent horizontalement, lien courant visible.
- [ ] Souris sur l'en-tête : encre lilas derrière le titre, plus claire sous le texte, absente sous « Proposer une quête » ; elle fond en ~1,5 s. Survol d'une image de carte : bichromie révélée par l'encre, carte légèrement inclinée.
- [ ] Page d'une guilde : l'emblème est une tuile 3D qui suit le curseur ; à sa place, aucun décalage.
- [ ] Les boutons de vote, les onglets, « Proposer une quête », « Rejoindre », « Quitter » ne font rien (fonctions retirées) ; « voir les votes » s'ouvre.
- [ ] `prefers-reduced-motion` : pas de Lenis, d'encre, d'emblème 3D, de vague ; fondus de 150 ms.
- [ ] Onglet Réseau : aucun 404 (`css/guilde.css`, `img/competences/*.jpg`, `lib/…`, `js/effects/*.js`, polices `lib/fonts/*.woff2`).
- [ ] Back-office : une page qui inclut `partials/header.php` est inchangée (la partie « page Guildes » de `guilde.css` ne s'y applique pas).
