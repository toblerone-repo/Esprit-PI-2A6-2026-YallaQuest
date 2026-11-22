<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
?>
<!DOCTYPE html>
<html lang="fr" data-yq-page="guildes">
<head>
  <?php
    $pageTitle = 'Guildes · Yalla Quest';
    $currentPage = 'guildes';
    require_once __DIR__ . '/../includes/head.php';
  ?>
<!-- Effets (scroll & souris) : styles dans Views/css/guilde.css, scripts dans Views/js/effects/. Pour les désactiver,
     retirer le script fx-boot ci-dessous et le bloc « Effets » en bas de page. -->
<script>/* masque #canvas le temps que les effets démarrent (jamais plus de 2,5 s, jamais en mouvement réduit) */
(function (r) { if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  r.classList.add('fx-boot'); setTimeout(function () { r.classList.remove('fx-boot'); }, 2500); })(document.documentElement);</script>
</head>
<body>
<?php require __DIR__ . '/../includes/spinner.php'; ?>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>
<div class="app">
  <main class="canvas" id="canvas" tabindex="-1">
    <!-- Une section par vue ; l'adresse (#guildes, #g1…#g5, #quetes) affiche la section voulue, #g1 par défaut. -->
    <section class="yq-view" id="guildes">
<div class="title-row"><div><p class="kicker">dimanche 15 novembre · guildes</p><h1>Guildes</h1><p class="about">Une seule guilde par membre, dix membres au plus.</p>
      <p class="sub">Vous êtes membre de <a class="glink" href="#g1"><strong>Les Lanternes</strong></a>. <strong>2 votes vous attendent.</strong></p></div>
      <div class="actions"><button class="btn primary" type="button" data-action="new-guild">Créer une guilde</button></div>
      </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes" aria-current="page"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="tabs" role="tablist"><a role="tab" tabindex="0" data-gltab="all" aria-selected="true">Toutes <span class="n">5</span></a><a role="tab" tabindex="0" data-gltab="mine" aria-selected="false">Ma guilde <span class="n">1</span></a><a role="tab" tabindex="0" data-gltab="other" aria-selected="false">À découvrir <span class="n">4</span></a></div>
    <div class="sheet"><div class="table-wrap"><table class="respond">
      <thead><tr><th>Guilde</th><th>Fondée par</th><th>Création</th><th class="n">Membres</th><th class="n">En vote</th><th class="n"><span class="sr">Action</span></th></tr></thead>
      <tbody><tr>
          <td class="id"><span class="gcell"><img src="../../img/competences/mobilite.jpg" alt=""><span><a class="glink" href="#g1"><strong>Les Lanternes</strong></a><span class="desc">Marcher, s'étirer et cuisiner ensemble après les cours.</span></span></span></td>
          <td class="hide-sm">vous</td>
          <td class="hide-sm">10 févr. 2026</td>
          <td class="n hide-sm">5 / 10</td>
          <td class="n second"><span class="pill lilac">2 à voter</span> 3 en vote</td>
          <td class="end"><span class="row-actions"><a class="btn" href="#g1">Ouvrir</a></span></td></tr><tr>
          <td class="id"><span class="gcell"><img src="../../img/competences/focus.jpg" alt=""><span><a class="glink" href="#g2"><strong>Souffle profond</strong></a><span class="desc">Respiration et étirements, dix minutes par jour, sans pression.</span></span></span></td>
          <td class="hide-sm">karim.bs</td>
          <td class="hide-sm">20 oct. 2025</td>
          <td class="n hide-sm">3 / 10</td>
          <td class="n second">1 en vote</td>
          <td class="end"><span class="row-actions"><a class="btn" href="#g2">Voir</a></span></td></tr><tr>
          <td class="id"><span class="gcell"><img src="../../img/competences/endurance.jpg" alt=""><span><a class="glink" href="#g3"><strong>Médina en mouvement</strong></a><span class="desc">Explorer la ville à pied, une ruelle à la fois.</span></span></span></td>
          <td class="hide-sm">leila.m</td>
          <td class="hide-sm">2 avr. 2026</td>
          <td class="n hide-sm">3 / 10</td>
          <td class="n second">1 en vote</td>
          <td class="end"><span class="row-actions"><a class="btn" href="#g3">Voir</a></span></td></tr><tr>
          <td class="id"><span class="gcell"><img src="../../img/competences/nutrition.jpg" alt=""><span><a class="glink" href="#g4"><strong>Cuisine de saison</strong></a><span class="desc">Recettes tunisiennes locales, légères et de saison.</span></span></span></td>
          <td class="hide-sm">omar.g</td>
          <td class="hide-sm">14 juil. 2026</td>
          <td class="n hide-sm">2 / 10</td>
          <td class="n second">0 en vote</td>
          <td class="end"><span class="row-actions"><a class="btn" href="#g4">Voir</a></span></td></tr><tr>
          <td class="id"><span class="gcell"><img src="../../img/competences/force.jpg" alt=""><span><a class="glink" href="#g5"><strong>Coureurs du matin</strong></a><span class="desc">Courir 5 km avant 8 h, trois fois par semaine.</span></span></span></td>
          <td class="hide-sm">sami.j</td>
          <td class="hide-sm">1 sept. 2026</td>
          <td class="n hide-sm">2 / 10</td>
          <td class="n second">1 en vote</td>
          <td class="end"><span class="row-actions"><a class="btn" href="#g5">Voir</a></span></td></tr></tbody></table></div></div>
    </section>
    <section class="yq-view yq-default" id="g1">
<div class="title-row">
      <div class="head"><img class="emblem" src="../../img/competences/mobilite.jpg" alt="">
        <div><p class="kicker">dimanche 15 novembre · guilde</p><h1>Les Lanternes</h1><p class="about">Marcher, s'étirer et cuisiner ensemble après les cours.</p><p class="sub"><strong>2 propositions attendent votre vote</strong>, la première se ferme demain à 23:59.</p></div></div>
      <div class="actions"><button class="btn primary" type="button" data-action="propose" data-guild="g1">Proposer une quête</button></div>
    </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1" aria-current="page"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="split">
      <section class="stack" aria-labelledby="h-props--g1">
        <h2 id="h-props--g1" class="sr">Propositions de quêtes</h2>
        <div class="tabs" role="tablist">
          <a role="tab" tabindex="0" data-gtab="g1:open" aria-selected="true">En vote <span class="n">3</span></a>
          <a role="tab" tabindex="0" data-gtab="g1:closed" aria-selected="false">Votes clôturés <span class="n">2</span></a>
        </div>
        <article class="proposal" aria-labelledby="t-p1">
      <img class="art" src="../../img/competences/nutrition.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/nutrition.jpg" alt="">Nutrition · quête à venir, du 18 nov. au 25 nov. 2026<span class="flag">À voter</span></p>
        <h3 id="t-p1">Préparer une salade méchouia</h3>
        <p class="qdesc">Poivrons et tomates grillés, sans friture. Une photo de l'assiette suffit.</p>
        <div class="votebar" role="img" aria-label="2 pour, 1 contre, 2 sans vote">
        <i class="pour yq-w-40" data-w="40"></i><i class="contre yq-w-20" data-w="20"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>2 pour</span><span><b class="k contre"></b>1 contre</span><span><b class="k none"></b>2 n'ont pas voté</span></p>
        <div class="pfoot"><p class="deadline urgent">Clôture du vote <strong>demain à 23:59</strong></p>
        <div class="choice" role="group" aria-label="Votre vote sur « Préparer une salade méchouia »">
          <button type="button" data-vote="p1" data-answer="contre" aria-pressed="false">Contre</button>
          <button type="button" data-vote="p1" data-answer="pour" aria-pressed="false">Pour</button>
        </div>
        </div>
        <details class="voters">
          <summary>Proposée le 12 nov. · voir 3 votes</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">NH</span><span class="nm">nour.h</span><span class="ans">pour</span><span>12 nov.</span></li><li><span class="avatar sm" aria-hidden="true">AK</span><span class="nm">amira.k</span><span class="ans">pour</span><span>13 nov.</span></li><li><span class="avatar sm" aria-hidden="true">IB</span><span class="nm">ines.b</span><span class="ans">contre</span><span>13 nov.</span></li></ul>
        </details>
      </div>
    </article><article class="proposal" aria-labelledby="t-p2">
      <img class="art" src="../../img/competences/endurance.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/endurance.jpg" alt="">Endurance · quête à venir, du 23 nov. au 30 nov. 2026<span class="flag">À voter</span></p>
        <h3 id="t-p2">Courir 5 km avant midi. Sans écouteurs.</h3>
        <p class="qdesc">Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet habituel.</p>
        <div class="votebar" role="img" aria-label="1 pour, 0 contre, 4 sans vote">
        <i class="pour yq-w-20" data-w="20"></i><i class="contre yq-w-0" data-w="0"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>1 pour</span><span><b class="k contre"></b>0 contre</span><span><b class="k none"></b>4 n'ont pas voté</span></p>
        <div class="pfoot"><p class="deadline">Clôture du vote <strong>le 19 nov. (dans 4 jours)</strong></p>
        <div class="choice" role="group" aria-label="Votre vote sur « Courir 5 km avant midi. Sans écouteurs. »">
          <button type="button" data-vote="p2" data-answer="contre" aria-pressed="false">Contre</button>
          <button type="button" data-vote="p2" data-answer="pour" aria-pressed="false">Pour</button>
        </div>
        </div>
        <details class="voters">
          <summary>Proposée le 14 nov. · voir 1 vote</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">RA</span><span class="nm">rania.a</span><span class="ans">pour</span><span>14 nov.</span></li></ul>
        </details>
      </div>
    </article><article class="proposal" aria-labelledby="t-p3">
      <img class="art" src="../../img/competences/force.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/force.jpg" alt="">Force · quête à venir, du 1 déc. au 30 déc. 2026</p>
        <h3 id="t-p3">30 jours de gainage</h3>
        <p class="qdesc">Renforcement adapté à son niveau, de 20 secondes à 2 minutes.</p>
        <div class="votebar" role="img" aria-label="2 pour, 2 contre, 1 sans vote">
        <i class="pour yq-w-40" data-w="40"></i><i class="contre yq-w-40" data-w="40"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>2 pour</span><span><b class="k contre"></b>2 contre</span><span><b class="k none"></b>1 n'a pas voté</span></p>
        <div class="pfoot"><p class="deadline">Clôture du vote <strong>le 25 nov. (dans 10 jours)</strong></p>
        <div class="choice" role="group" aria-label="Votre vote sur « 30 jours de gainage »">
          <button type="button" data-vote="p3" data-answer="contre" aria-pressed="false">Contre</button>
          <button type="button" data-vote="p3" data-answer="pour" aria-pressed="true">Pour</button>
        </div>
        <p class="mine">Vous avez voté pour le 10 nov. · modifiable jusqu'à la clôture</p></div>
        <details class="voters">
          <summary>Proposée le 10 nov. · voir 4 votes</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">YT</span><span class="nm">vous</span><span class="ans">pour</span><span>10 nov.</span></li><li><span class="avatar sm" aria-hidden="true">NH</span><span class="nm">nour.h</span><span class="ans">contre</span><span>11 nov.</span></li><li><span class="avatar sm" aria-hidden="true">AK</span><span class="nm">amira.k</span><span class="ans">contre</span><span>11 nov.</span></li><li><span class="avatar sm" aria-hidden="true">IB</span><span class="nm">ines.b</span><span class="ans">pour</span><span>12 nov.</span></li></ul>
        </details>
      </div>
    </article>
      </section>
      <aside class="rail">
        <section aria-labelledby="h-mem--g1"><h2 id="h-mem--g1">Membres</h2>
          <div class="panel yq-st-1">
          <div class="cap"><span><b>5 / 10 membres</b> · fondée par vous le 10 févr. 2026</span><div class="capbar"><i class="yq-w-50" data-w="50"></i></div></div>
          <div class="rows"><div class="row me"><span class="avatar sm" aria-hidden="true">YT</span>
              <span class="who"><strong>yasmine.t (vous)</strong><span>A créé la guilde · membre depuis le 10 févr. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/3 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">NH</span>
              <span class="who"><strong>nour.h</strong><span>membre depuis le 12 févr. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">2/3 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">IB</span>
              <span class="who"><strong>ines.b</strong><span>membre depuis le 15 mai 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">2/3 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">AK</span>
              <span class="who"><strong>amira.k</strong><span>membre depuis le 2 juin 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">2/3 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">RA</span>
              <span class="who"><strong>rania.a</strong><span>membre depuis le 20 sept. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/3 <small>votes</small></span></div></div>
          </div>
        </section>
        <section aria-labelledby="h-adopt--g1"><h2 id="h-adopt--g1">Quêtes adoptées · 1</h2>
          <div class="panel yq-st-2"><div class="rows"><div class="row adopted"><img src="../../img/competences/focus.jpg" alt=""><span class="who"><strong>10 minutes de respiration chaque soir</strong><span>Focus · en cours · jusqu'au 17 déc.</span></span></div></div></div>
        </section>
      </aside>
    </div>
    </section>
    <section class="yq-view" id="g2">
<div class="title-row">
      <div class="head"><img class="emblem" src="../../img/competences/focus.jpg" alt="">
        <div><p class="kicker">dimanche 15 novembre · guilde</p><h1>Souffle profond</h1><p class="about">Respiration et étirements, dix minutes par jour, sans pression.</p><p class="sub"><strong>1 proposition est en vote.</strong> Vous êtes membre de Les Lanternes : seuls les membres de Souffle profond votent ici.</p></div></div>
      <div class="actions"><span class="pill" title="Le fondateur reste dans sa guilde">Vous avez fondé Les Lanternes</span></div>
    </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="split">
      <section class="stack" aria-labelledby="h-props--g2">
        <h2 id="h-props--g2" class="sr">Propositions de quêtes</h2>
        <div class="tabs" role="tablist">
          <a role="tab" tabindex="0" data-gtab="g2:open" aria-selected="true">En vote <span class="n">1</span></a>
          <a role="tab" tabindex="0" data-gtab="g2:closed" aria-selected="false">Votes clôturés <span class="n">1</span></a>
        </div>
        <article class="proposal" aria-labelledby="t-p7">
      <img class="art" src="../../img/competences/endurance.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/endurance.jpg" alt="">Endurance · quête à venir, du 23 nov. au 30 nov. 2026</p>
        <h3 id="t-p7">Courir 5 km avant midi. Sans écouteurs.</h3>
        <p class="qdesc">Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet habituel.</p>
        <div class="votebar" role="img" aria-label="1 pour, 1 contre, 1 sans vote">
        <i class="pour yq-w-33-3333" data-w="33.3333"></i><i class="contre yq-w-33-3333" data-w="33.3333"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>1 pour</span><span><b class="k contre"></b>1 contre</span><span><b class="k none"></b>1 n'a pas voté</span></p>
        <div class="pfoot"><p class="deadline">Clôture du vote <strong>le 18 nov. (dans 3 jours)</strong> · rejoignez la guilde pour voter</p></div>
        <details class="voters">
          <summary>Proposée le 13 nov. · voir 2 votes</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">KB</span><span class="nm">karim.bs</span><span class="ans">pour</span><span>13 nov.</span></li><li><span class="avatar sm" aria-hidden="true">MC</span><span class="nm">mehdi.c</span><span class="ans">contre</span><span>14 nov.</span></li></ul>
        </details>
      </div>
    </article>
      </section>
      <aside class="rail">
        <section aria-labelledby="h-mem--g2"><h2 id="h-mem--g2">Membres</h2>
          <div class="panel yq-st-1">
          <div class="cap"><span><b>3 / 10 membres</b> · fondée par karim.bs le 20 oct. 2025</span><div class="capbar"><i class="yq-w-30" data-w="30"></i></div></div>
          <div class="rows"><div class="row"><span class="avatar sm" aria-hidden="true">KB</span>
              <span class="who"><strong>karim.bs</strong><span>A créé la guilde · membre depuis le 20 oct. 2025</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">YD</span>
              <span class="who"><strong>youssef.d</strong><span>membre depuis le 5 nov. 2025</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">0/1 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">MC</span>
              <span class="who"><strong>mehdi.c</strong><span>membre depuis le 3 juil. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div></div>
          </div>
        </section>
        <section aria-labelledby="h-adopt--g2"><h2 id="h-adopt--g2">Quêtes adoptées · 1</h2>
          <div class="panel yq-st-2"><div class="rows"><div class="row adopted"><img src="../../img/competences/mobilite.jpg" alt=""><span class="who"><strong>Séance d'étirements guidée</strong><span>Mobilité · en cours · jusqu'au 30 nov.</span></span></div></div></div>
        </section>
      </aside>
    </div>
    </section>
    <section class="yq-view" id="g3">
<div class="title-row">
      <div class="head"><img class="emblem" src="../../img/competences/endurance.jpg" alt="">
        <div><p class="kicker">dimanche 15 novembre · guilde</p><h1>Médina en mouvement</h1><p class="about">Explorer la ville à pied, une ruelle à la fois.</p><p class="sub"><strong>1 proposition est en vote.</strong> Vous êtes membre de Les Lanternes : seuls les membres de Médina en mouvement votent ici.</p></div></div>
      <div class="actions"><span class="pill" title="Le fondateur reste dans sa guilde">Vous avez fondé Les Lanternes</span></div>
    </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="split">
      <section class="stack" aria-labelledby="h-props--g3">
        <h2 id="h-props--g3" class="sr">Propositions de quêtes</h2>
        <div class="tabs" role="tablist">
          <a role="tab" tabindex="0" data-gtab="g3:open" aria-selected="true">En vote <span class="n">1</span></a>
          <a role="tab" tabindex="0" data-gtab="g3:closed" aria-selected="false">Votes clôturés <span class="n">0</span></a>
        </div>
        <article class="proposal" aria-labelledby="t-p8">
      <img class="art" src="../../img/competences/endurance.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/endurance.jpg" alt="">Endurance · quête à venir, du 21 nov. au 5 déc. 2026</p>
        <h3 id="t-p8">Marcher 30 minutes dans la médina</h3>
        <p class="qdesc">Un parcours différent chaque jour, à son rythme.</p>
        <div class="votebar" role="img" aria-label="2 pour, 0 contre, 1 sans vote">
        <i class="pour yq-w-66-6667" data-w="66.6667"></i><i class="contre yq-w-0" data-w="0"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>2 pour</span><span><b class="k contre"></b>0 contre</span><span><b class="k none"></b>1 n'a pas voté</span></p>
        <div class="pfoot"><p class="deadline">Clôture du vote <strong>le 17 nov. (dans 2 jours)</strong> · rejoignez la guilde pour voter</p></div>
        <details class="voters">
          <summary>Proposée le 11 nov. · voir 2 votes</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">LM</span><span class="nm">leila.m</span><span class="ans">pour</span><span>11 nov.</span></li><li><span class="avatar sm" aria-hidden="true">SR</span><span class="nm">salma.r</span><span class="ans">pour</span><span>12 nov.</span></li></ul>
        </details>
      </div>
    </article>
      </section>
      <aside class="rail">
        <section aria-labelledby="h-mem--g3"><h2 id="h-mem--g3">Membres</h2>
          <div class="panel yq-st-1">
          <div class="cap"><span><b>3 / 10 membres</b> · fondée par leila.m le 2 avr. 2026</span><div class="capbar"><i class="yq-w-30" data-w="30"></i></div></div>
          <div class="rows"><div class="row"><span class="avatar sm" aria-hidden="true">LM</span>
              <span class="who"><strong>leila.m</strong><span>A créé la guilde · membre depuis le 2 avr. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">SR</span>
              <span class="who"><strong>salma.r</strong><span>membre depuis le 10 avr. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">HB</span>
              <span class="who"><strong>hedi.b</strong><span>membre depuis le 12 nov. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">0/1 <small>votes</small></span></div></div>
          </div>
        </section>
        <section aria-labelledby="h-adopt--g3"><h2 id="h-adopt--g3">Quêtes adoptées · 0</h2>
          <div class="panel yq-st-2"><div class="rows"><div class="row"><span class="who"><span>Aucune quête adoptée pour l'instant.</span></span></div></div></div>
        </section>
      </aside>
    </div>
    </section>
    <section class="yq-view" id="g4">
<div class="title-row">
      <div class="head"><img class="emblem" src="../../img/competences/nutrition.jpg" alt="">
        <div><p class="kicker">dimanche 15 novembre · guilde</p><h1>Cuisine de saison</h1><p class="about">Recettes tunisiennes locales, légères et de saison.</p><p class="sub"><strong>0 proposition est en vote.</strong> Vous êtes membre de Les Lanternes : seuls les membres de Cuisine de saison votent ici.</p></div></div>
      <div class="actions"><span class="pill" title="Le fondateur reste dans sa guilde">Vous avez fondé Les Lanternes</span></div>
    </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="split">
      <section class="stack" aria-labelledby="h-props--g4">
        <h2 id="h-props--g4" class="sr">Propositions de quêtes</h2>
        <div class="tabs" role="tablist">
          <a role="tab" tabindex="0" data-gtab="g4:open" aria-selected="true">En vote <span class="n">0</span></a>
          <a role="tab" tabindex="0" data-gtab="g4:closed" aria-selected="false">Votes clôturés <span class="n">1</span></a>
        </div>
        <div class="panel empty-state"><p><strong>Aucune proposition en vote.</strong></p></div>
      </section>
      <aside class="rail">
        <section aria-labelledby="h-mem--g4"><h2 id="h-mem--g4">Membres</h2>
          <div class="panel yq-st-1">
          <div class="cap"><span><b>2 / 10 membres</b> · fondée par omar.g le 14 juil. 2026</span><div class="capbar"><i class="yq-w-20" data-w="20"></i></div></div>
          <div class="rows"><div class="row"><span class="avatar sm" aria-hidden="true">OG</span>
              <span class="who"><strong>omar.g</strong><span>A créé la guilde · membre depuis le 14 juil. 2026</span></span>
              </div><div class="row"><span class="avatar sm" aria-hidden="true">AF</span>
              <span class="who"><strong>anis.f</strong><span>membre depuis le 20 juil. 2026</span></span>
              </div></div>
          </div>
        </section>
        <section aria-labelledby="h-adopt--g4"><h2 id="h-adopt--g4">Quêtes adoptées · 1</h2>
          <div class="panel yq-st-2"><div class="rows"><div class="row adopted"><img src="../../img/competences/nutrition.jpg" alt=""><span class="who"><strong>Cuisiner un plat de saison</strong><span>Nutrition · terminée · jusqu'au 26 oct.</span></span></div></div></div>
        </section>
      </aside>
    </div>
    </section>
    <section class="yq-view" id="g5">
<div class="title-row">
      <div class="head"><img class="emblem" src="../../img/competences/force.jpg" alt="">
        <div><p class="kicker">dimanche 15 novembre · guilde</p><h1>Coureurs du matin</h1><p class="about">Courir 5 km avant 8 h, trois fois par semaine.</p><p class="sub"><strong>1 proposition est en vote.</strong> Vous êtes membre de Les Lanternes : seuls les membres de Coureurs du matin votent ici.</p></div></div>
      <div class="actions"><span class="pill" title="Le fondateur reste dans sa guilde">Vous avez fondé Les Lanternes</span></div>
    </div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="split">
      <section class="stack" aria-labelledby="h-props--g5">
        <h2 id="h-props--g5" class="sr">Propositions de quêtes</h2>
        <div class="tabs" role="tablist">
          <a role="tab" tabindex="0" data-gtab="g5:open" aria-selected="true">En vote <span class="n">1</span></a>
          <a role="tab" tabindex="0" data-gtab="g5:closed" aria-selected="false">Votes clôturés <span class="n">0</span></a>
        </div>
        <article class="proposal" aria-labelledby="t-p10">
      <img class="art" src="../../img/competences/endurance.jpg" alt="" loading="lazy">
      <div class="body">
        <p class="qline"><img src="../../img/competences/endurance.jpg" alt="">Endurance · quête à venir, le 6 déc. 2026</p>
        <h3 id="t-p10">Courir un semi-marathon en groupe</h3>
        <p class="qdesc">Le semi de Carthage, allure libre, on part et on arrive ensemble.</p>
        <div class="votebar" role="img" aria-label="1 pour, 1 contre">
        <i class="pour yq-w-50" data-w="50"></i><i class="contre yq-w-50" data-w="50"></i></div>
      <p class="votelegend"><span><b class="k pour"></b>1 pour</span><span><b class="k contre"></b>1 contre</span><span><b class="k none"></b>0 n'a pas voté</span></p>
        <div class="pfoot"><p class="deadline">Clôture du vote <strong>le 20 nov. (dans 5 jours)</strong> · rejoignez la guilde pour voter</p></div>
        <details class="voters">
          <summary>Proposée le 9 nov. · voir 2 votes</summary>
          <ul><li><span class="avatar sm" aria-hidden="true">SJ</span><span class="nm">sami.j</span><span class="ans">pour</span><span>9 nov.</span></li><li><span class="avatar sm" aria-hidden="true">HZ</span><span class="nm">hiba.z</span><span class="ans">contre</span><span>14 nov.</span></li></ul>
        </details>
      </div>
    </article>
      </section>
      <aside class="rail">
        <section aria-labelledby="h-mem--g5"><h2 id="h-mem--g5">Membres</h2>
          <div class="panel yq-st-1">
          <div class="cap"><span><b>2 / 10 membres</b> · fondée par sami.j le 1 sept. 2026</span><div class="capbar"><i class="yq-w-20" data-w="20"></i></div></div>
          <div class="rows"><div class="row"><span class="avatar sm" aria-hidden="true">SJ</span>
              <span class="who"><strong>sami.j</strong><span>A créé la guilde · membre depuis le 1 sept. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div><div class="row"><span class="avatar sm" aria-hidden="true">HZ</span>
              <span class="who"><strong>hiba.z</strong><span>membre depuis le 3 sept. 2026</span></span>
              <span class="num" title="Votes exprimés sur les propositions en cours">1/1 <small>votes</small></span></div></div>
          </div>
        </section>
        <section aria-labelledby="h-adopt--g5"><h2 id="h-adopt--g5">Quêtes adoptées · 0</h2>
          <div class="panel yq-st-2"><div class="rows"><div class="row"><span class="who"><span>Aucune quête adoptée pour l'instant.</span></span></div></div></div>
        </section>
      </aside>
    </div>
    </section>
    <section class="yq-view" id="quetes">
<div class="title-row"><div><p class="kicker">dimanche 15 novembre · catalogue</p><h1>Quêtes</h1><p class="about">Chaque quête crédite une compétence principale.</p>
      <p class="sub"><strong>5 quêtes à venir.</strong> Une quête rejoint une guilde quand ses membres adoptent la proposition par vote.</p></div></div><nav class="guildnav" aria-label="Navigation">
      <div class="brand">
        <strong>Yalla Quest</strong><span class="tag">GUILDES</span>
      </div>

      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes" aria-current="page"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds"><a class="nav-item" href="#g1" data-route="g1"><img class="gdot" src="../../img/competences/mobilite.jpg" alt="">Les Lanternes<span class="count" aria-label="2 à voter">2</span></a></div>
      </div>

      <div class="user-row">
        <span class="avatar" aria-hidden="true">YT</span>
        <div><strong>yasmine.t</strong><span>Inscrite le 19 janv. 2026</span></div>
      </div>
    </nav>
    <div class="tabs" role="tablist"><a role="tab" tabindex="0" data-qtab="all" aria-selected="true">Toutes <span class="n">8</span></a><a role="tab" tabindex="0" data-qtab="À venir" aria-selected="false">À venir <span class="n">5</span></a><a role="tab" tabindex="0" data-qtab="En cours" aria-selected="false">En cours <span class="n">2</span></a><a role="tab" tabindex="0" data-qtab="Terminée" aria-selected="false">Terminées <span class="n">1</span></a></div>
    <div class="sheet"><div class="table-wrap"><table class="respond">
      <thead><tr><th>Quête</th><th>Type</th><th>Période</th><th>Statut</th><th>Propositions</th><th class="n"><span class="sr">Action</span></th></tr></thead>
      <tbody><tr>
          <td class="id"><span class="qt">10 minutes de respiration chaque soir</span><span class="desc">Respiration guidée avant de dormir, téléphone dans une autre pièce.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/focus.jpg" alt="">Focus</span></td>
          <td class="second yq-st-3">1 oct. → 17 déc. 2026</td>
          <td class="hide-sm">En cours</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g1">Les Lanternes · adoptée</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q8" aria-label="Proposer « 10 minutes de respiration chaque soir » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">Cuisiner un plat de saison</span><span class="desc">Un légume du marché, une recette de famille, sans friture.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/nutrition.jpg" alt="">Nutrition</span></td>
          <td class="second yq-st-3">5 oct. → 26 oct. 2026</td>
          <td class="hide-sm">Terminée</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g1">Les Lanternes · rejetée</a><a class="chip" href="#g4">Cuisine de saison · adoptée</a></span></td>
          <td class="end"><span class="row-actions"></span></td></tr><tr>
          <td class="id"><span class="qt">Séance d'étirements guidée</span><span class="desc">Quinze minutes, trois fois par semaine, le dos et les hanches.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/mobilite.jpg" alt="">Mobilité</span></td>
          <td class="second yq-st-3">2 nov. → 30 nov. 2026</td>
          <td class="hide-sm">En cours</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g2">Souffle profond · adoptée</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q5" aria-label="Proposer « Séance d'étirements guidée » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">Préparer une salade méchouia</span><span class="desc">Poivrons et tomates grillés, sans friture. Une photo de l'assiette suffit.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/nutrition.jpg" alt="">Nutrition</span></td>
          <td class="second yq-st-3">18 nov. → 25 nov. 2026</td>
          <td class="hide-sm">À venir</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g1">Les Lanternes · en vote</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q2" aria-label="Proposer « Préparer une salade méchouia » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">Marcher 30 minutes dans la médina</span><span class="desc">Un parcours différent chaque jour, à son rythme.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/endurance.jpg" alt="">Endurance</span></td>
          <td class="second yq-st-3">21 nov. → 5 déc. 2026</td>
          <td class="hide-sm">À venir</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g3">Médina en mouvement · en vote</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q3" aria-label="Proposer « Marcher 30 minutes dans la médina » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">Courir 5 km avant midi. Sans écouteurs.</span><span class="desc">Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet habituel.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/endurance.jpg" alt="">Endurance</span></td>
          <td class="second yq-st-3">23 nov. → 30 nov. 2026</td>
          <td class="hide-sm">À venir</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g1">Les Lanternes · en vote</a><a class="chip" href="#g2">Souffle profond · en vote</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q1" aria-label="Proposer « Courir 5 km avant midi. Sans écouteurs. » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">30 jours de gainage</span><span class="desc">Renforcement adapté à son niveau, de 20 secondes à 2 minutes.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/force.jpg" alt="">Force</span></td>
          <td class="second yq-st-3">1 déc. → 30 déc. 2026</td>
          <td class="hide-sm">À venir</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g1">Les Lanternes · en vote</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q4" aria-label="Proposer « 30 jours de gainage » à Les Lanternes">Proposer</button></span></td></tr><tr>
          <td class="id"><span class="qt">Courir un semi-marathon en groupe</span><span class="desc">Le semi de Carthage, allure libre, on part et on arrive ensemble.</span></td>
          <td class="hide-sm"><span class="skill"><img src="../../img/competences/endurance.jpg" alt="">Endurance</span></td>
          <td class="second yq-st-3">6 déc. 2026</td>
          <td class="hide-sm">À venir</td>
          <td class="hide-sm"><span class="chips"><a class="chip" href="#g5">Coureurs du matin · en vote</a></span></td>
          <td class="end"><span class="row-actions"><button class="btn" type="button" data-action="propose" data-quest="q7" aria-label="Proposer « Courir un semi-marathon en groupe » à Les Lanternes">Proposer</button></span></td></tr></tbody></table></div></div>
    </section>
  </main>
</div>
<!-- Effets : bibliothèques locales (Views/lib/), avec un CDN de secours si un fichier manque -->
<script src="../../lib/gsap/gsap.min.js"></script>
<script>window.gsap || document.write('<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"><\/script>');</script>
<script src="../../lib/gsap/ScrollTrigger.min.js"></script>
<script>window.ScrollTrigger || document.write('<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"><\/script>');</script>
<script src="../../lib/lenis/lenis.min.js"></script>
<script>window.Lenis || document.write('<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.14/dist/lenis.min.js"><\/script>');</script>
<script src="../../lib/splittype/split-type.min.js"></script>
<script>window.SplitType || document.write('<script src="https://cdn.jsdelivr.net/npm/split-type@0.3.4/umd/index.min.js"><\/script>');</script>
<script type="importmap">{"imports":{"three":"../../lib/three/three.module.min.js"}}</script>
<script type="module" src="../../js/effects/main.js" onerror="document.documentElement.classList.remove('fx-boot')"></script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
