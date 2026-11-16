<?php
// Barre de navigation du Front-Office, en haut de la page (il n'y a plus de menu latéral) :
// logo, liens des pages, « Ma guilde » (rempli par js/guildes.js dans #my-guilds), sections à venir, compte.
// Sur téléphone : barre mobile + #sidebar en feuille plein écran (bouton menu), comme avant.
// Le lien actif (aria-current) est posé par js/guildes.js selon le hash.
$logo = 'front-office/img/logo.png';
?>
  <header class="mobilebar">
    <button class="btn ghost menu-toggle" type="button" aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4h12M2 8h12M2 12h12"/></svg>
    </button>
    <img src="<?= $logo ?>" alt="" width="26" height="26">
    <span class="mobile-title" id="mobile-title">Guildes</span>
  </header>

  <header class="topbar">
    <a class="brand top-brand" href="#guildes">
      <img src="<?= $logo ?>" alt="" width="30" height="30">
      <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
    </a>

    <nav class="sidebar" id="sidebar" aria-label="Navigation">
      <div class="brand sheet-brand">
        <img src="<?= $logo ?>" alt="" width="30" height="30">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
        <button class="btn ghost close-menu" type="button" aria-label="Fermer le menu">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 3l10 10M13 3L3 13"/></svg>
        </button>
      </div>

      <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"/></svg>Toutes les guildes</a>
      <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"/><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"/></svg>Quêtes</a>

      <p class="group-label">Ma guilde</p>
      <div id="my-guilds"></div>

      <div class="bottom">
        <a class="nav-item" href="#compte" data-route="compte"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="8" cy="5.5" r="2.5"/><path d="M3 13.5c.6-2.6 2.6-4 5-4s4.4 1.4 5 4"/></svg>Mon compte</a>
        <div class="user-row">
          <span class="avatar" aria-hidden="true">YT</span>
          <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
        </div>
      </div>
    </nav>

    <!-- sections à venir : les boutons ne font rien pour le moment (data-soon) -->
    <nav class="topnav" aria-label="Sections">
      <button type="button" data-soon="quetes-quotidiennes">Quêtes quotidiennes</button>
      <button type="button" data-soon="guilde">Guilde</button>
      <button type="button" data-soon="journal">Journal d'activité</button>
      <button type="button" data-soon="publications">Publications</button>
    </nav>

    <!-- compte sur grand écran (sur téléphone : « Mon compte » et l'utilisateur sont dans la feuille du menu) -->
    <a class="nav-item account" href="#compte" data-route="compte" title="yasmine.t · Inscrite le 19 janv. 2026">
      <span class="avatar sm" aria-hidden="true">YT</span><span class="account-name">yasmine.t</span><span class="sr">, mon compte</span>
    </a>
  </header>
