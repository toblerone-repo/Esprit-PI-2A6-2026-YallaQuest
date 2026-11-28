<?php
// Amine : guildes. Houssem : protection CSRF/XSS. Guildes : données de la base, formulaires « Créer une guilde » et « Proposer une quête ».
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'guildes';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireAuth();

require_once __DIR__ . '/../../../Controllers/guildeC.php';
require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

/* Vue = affichage uniquement : SQL et règles dans Controllers/guildeC.php.
   Une <section class="yq-view"> par adresse : #guildes, #g<id> (une par guilde), #quetes,
   #creer-guilde et #proposer (les deux formulaires). Sans adresse, la section .yq-default s'affiche. */

$guildeC = new GuildeC();
$idUser = (int) $_SESSION['user_id'];
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$erreursGuilde = [];
$erreursQuete = [];
$oldGuilde = ['nom' => '', 'description' => ''];
$oldQuete = ['titre' => '', 'difficulte' => '', 'xp' => '50', 'id_categorie' => '', 'id_competence' => '', 'date_limite_vote' => ''];
$vueFormulaire = null;   // formulaire (ou guilde) à réafficher avec ses erreurs
$messageErreur = null;   // erreur d'une action sans formulaire à remplir (rejoindre, voter)

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $formulaire = (string) ($_POST['form'] ?? '');

    if ($formulaire === 'guilde') {
        $vueFormulaire = 'creer-guilde';
        $oldGuilde = [
            'nom' => GuildeC::espaces((string) ($_POST['nom'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];
        $erreursGuilde = $guildeC->verifierGuilde($oldGuilde, $idUser);
        if (empty($erreursGuilde)) {
            $guilde = new Guilde($oldGuilde['nom'], $oldGuilde['description'], $idUser);
            if ($guildeC->creerGuilde($guilde)) {
                header('Location: guildes.php?ok=guilde#g' . $guilde->getId(), true, 303);
                exit;
            }
            $erreursGuilde['general'] = 'La guilde n’a pas pu être créée. Vérifiez que vous n’êtes pas déjà membre d’une guilde, puis réessayez.';
        }
    } elseif ($formulaire === 'proposition') {
        $vueFormulaire = 'proposer';
        foreach (array_keys($oldQuete) as $champ) {
            $oldQuete[$champ] = trim((string) ($_POST[$champ] ?? ''));
        }
        $oldQuete['titre'] = GuildeC::espaces($oldQuete['titre']);
        $erreursQuete = $guildeC->verifierProposition($oldQuete, $idUser);
        $idGuildeMembre = $guildeC->getIdGuildeDuMembre($idUser);
        if (empty($erreursQuete) && $idGuildeMembre !== null) {
            $quete = new Quete($oldQuete['titre'], $oldQuete['difficulte'], (int) $oldQuete['xp']);
            $quete->setIdCat((int) $oldQuete['id_categorie']);
            $quete->setIdComp((int) $oldQuete['id_competence']);
            $proposition = new Proposition($idGuildeMembre, 0, '', $oldQuete['date_limite_vote']);
            if ($guildeC->proposerQuete($quete, $proposition)) {
                header('Location: guildes.php?ok=proposition#g' . $idGuildeMembre, true, 303);
                exit;
            }
            $erreursQuete['general'] = 'La quête n’a pas pu être proposée. Réessayez.';
        }
    } elseif ($formulaire === 'rejoindre') {
        $idG = ctype_digit((string) ($_POST['id_guilde'] ?? '')) ? (int) $_POST['id_guilde'] : 0;
        $erreurs = $guildeC->verifierAdhesion($idG, $idUser);
        if (empty($erreurs)) {
            if ($guildeC->rejoindreGuilde($idG, $idUser)) {
                header('Location: guildes.php?ok=adhesion#g' . $idG, true, 303);
                exit;
            }
            $erreurs['general'] = 'Vous n’avez pas pu rejoindre cette guilde. Réessayez.';
        }
        $messageErreur = $erreurs['general'];
        $vueFormulaire = 'g' . $idG;
    } elseif ($formulaire === 'vote') {
        $idP = ctype_digit((string) ($_POST['id_proposition'] ?? '')) ? (int) $_POST['id_proposition'] : 0;
        $reponse = (string) ($_POST['reponse'] ?? '');
        $prop = $guildeC->getProposition($idP);
        $erreurs = $guildeC->verifierVote($idP, $reponse, $idUser);
        if (empty($erreurs)) {
            if ($guildeC->voter($idP, $idUser, $reponse)) {
                header('Location: guildes.php?ok=vote#g' . (int) $prop['id_guilde'], true, 303);
                exit;
            }
            $erreurs['general'] = 'Votre vote n’a pas pu être enregistré. Réessayez.';
        }
        $messageErreur = $erreurs['general'];
        $vueFormulaire = $prop ? 'g' . (int) $prop['id_guilde'] : 'guildes';
    }
}

// ---------- Données affichées ----------
$aujourdhui = $guildeC->aujourdhui();
$kicker = GuildeC::jourFr($aujourdhui);
$guildes = $guildeC->listGuildes();
$idMaGuilde = $guildeC->getIdGuildeDuMembre($idUser);
$maGuilde = null;
foreach ($guildes as $g) {
    if ((int) $g['id_guilde'] === $idMaGuilde) {
        $maGuilde = $g;
    }
}
$propositions = $guildeC->listPropositions();
$propsParGuilde = [];
foreach ($propositions as $p) {
    $propsParGuilde[(int) $p['id_guilde']][] = $p;
}
$membresParGuilde = [];
foreach ($guildes as $g) {
    $membresParGuilde[(int) $g['id_guilde']] = $guildeC->getMembres((int) $g['id_guilde']);
}
$votes = $guildeC->listVotes();
$monVote = static function (int $idProposition) use ($votes, $idUser): ?array {
    foreach ($votes[$idProposition] ?? [] as $v) {
        if ((int) $v['id_utilisateur'] === $idUser) {
            return $v;
        }
    }
    return null;
};
// Propositions de ma guilde encore ouvertes où je n'ai pas voté
$aVoter = 0;
if ($maGuilde) {
    foreach ($propsParGuilde[(int) $maGuilde['id_guilde']] ?? [] as $p) {
        $aVoter += ($p['statut'] === 'en_vote' && $monVote((int) $p['id_proposition']) === null) ? 1 : 0;
    }
}
$competences = $guildeC->listCompetences();
$categories = $guildeC->listCategories();

$moi = (new UtilisateurC())->getUtilisateur($idUser);
$monNom = $moi ? GuildeC::nomAffiche($moi->getPseudo(), $moi->getPrenom(), $moi->getNom()) : 'membre';

$dateMin = (new DateTimeImmutable($aujourdhui))->modify('+1 day')->format('Y-m-d');
$dateMax = (new DateTimeImmutable($aujourdhui))->modify('+' . GuildeC::VOTE_JOURS_MAX . ' days')->format('Y-m-d');
if ($oldQuete['date_limite_vote'] === '') {
    $oldQuete['date_limite_vote'] = (new DateTimeImmutable($aujourdhui))->modify('+7 days')->format('Y-m-d');
}

$vueParDefaut = $vueFormulaire ?? ($maGuilde ? 'g' . (int) $maGuilde['id_guilde'] : 'guildes');
$vuesExistantes = array_merge(['guildes', 'quetes', 'creer-guilde', 'proposer'], array_map(static fn ($g) => 'g' . (int) $g['id_guilde'], $guildes));
if (!in_array($vueParDefaut, $vuesExistantes, true)) {
    $vueParDefaut = 'guildes';
}
$messageOk = [
    'guilde' => 'Votre guilde a été créée. Vous en êtes le premier membre.',
    'proposition' => 'Votre quête a été proposée à la guilde. Le vote est ouvert.',
    'adhesion' => 'Bienvenue dans la guilde ! Vous pouvez maintenant voter et proposer des quêtes.',
    'vote' => 'Votre vote est enregistré. Vous pouvez le modifier jusqu’à la clôture.',
][$_GET['ok'] ?? ''] ?? null;

$libelleStatut = ['en_vote' => 'En vote', 'adoptee' => 'Adoptée', 'rejetee' => 'Rejetée'];

// Navigation commune à toutes les vues
$nav = static function (string $route) use ($e, $maGuilde, $aVoter, $monNom): void {
    $courant = static fn (string $r): string => $r === $route ? ' aria-current="page"' : '';
    ?>
    <nav class="guildnav" aria-label="Navigation">
      <div class="brand"><strong>Yalla Quest</strong><span class="tag">GUILDES</span></div>
      <div class="navlinks">
        <a class="nav-item" href="#guildes" data-route="guildes"<?= $courant('guildes') ?>><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h10v5.5c0 3-2.2 5-5 6-2.8-1-5-3-5-6z"></path></svg>Toutes les guildes</a>
        <a class="nav-item" href="#quetes" data-route="quetes"<?= $courant('quetes') ?>><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 2.5h7.5L13 5v8.5H3z"></path><path d="M5.5 7h5M5.5 9.5h5M5.5 12h3"></path></svg>Quêtes</a>
        <p class="group-label">Ma guilde</p>
        <div class="my-guilds">
          <?php if ($maGuilde): $r = 'g' . (int) $maGuilde['id_guilde']; ?>
            <a class="nav-item" href="#<?= $r ?>" data-route="<?= $r ?>"<?= $courant($r) ?>><img class="gdot" src="../../img/competences/<?= $e(GuildeC::image(null, (int) $maGuilde['id_guilde'])) ?>" alt=""><?= $e($maGuilde['nom']) ?><?php if ($aVoter > 0): ?><span class="count" aria-label="<?= $aVoter ?> à voter"><?= $aVoter ?></span><?php endif; ?></a>
          <?php else: ?>
            <a class="nav-item" href="#creer-guilde" data-route="creer-guilde"<?= $courant('creer-guilde') ?>>+ Créer une guilde</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="user-row">
        <span class="avatar" aria-hidden="true"><?= $e(GuildeC::initiales($monNom)) ?></span>
        <div><strong><?= $e($monNom) ?></strong><span><?= $maGuilde ? 'Membre de ' . $e($maGuilde['nom']) : 'Sans guilde' ?></span></div>
      </div>
    </nav>
    <?php
};

// Message d'erreur sous un champ
$erreurChamp = static function (array $erreurs, string $champ) use ($e): void {
    if (isset($erreurs[$champ])) {
        echo '<span class="field-error" id="err-' . $e($champ) . '">' . $e($erreurs[$champ]) . '</span>';
    }
};
$boutonRejoindre = static function (array $g, string $classe) use ($e, $csrfToken): void {
    if ((int) $g['nb_membres'] >= GuildeC::MEMBRES_MAX) {
        echo '<span class="pill full">Complète</span>';
        return;
    } ?>
    <form method="post" action="guildes.php#g<?= (int) $g['id_guilde'] ?>" class="inline-form">
      <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
      <input type="hidden" name="form" value="rejoindre">
      <input type="hidden" name="id_guilde" value="<?= (int) $g['id_guilde'] ?>">
      <button class="<?= $e($classe) ?>" type="submit">Rejoindre</button>
    </form>
    <?php
};
$invalide = static fn (array $erreurs, string $champ): string =>
    isset($erreurs[$champ]) ? ' aria-invalid="true" aria-describedby="err-' . $champ . '"' : '';
?>
<!DOCTYPE html>
<html lang="fr" data-yq-page="guildes">
<head>
<meta name="csrf-token" content="<?= $e($csrfToken) ?>">
  <?php
    $pageTitle = 'Guildes · Yalla Quest';
    $currentPage = 'guildes';
    require_once __DIR__ . '/../includes/head.php';
  ?>
<!-- Effets (scroll & souris) : styles dans Views/css/guilde.css, scripts dans Views/js/effects/. Pour les désactiver,
     retirer le script fx-boot ci-dessous et le bloc « Effets » en bas de page. -->
<script nonce="<?= $e($cspNonce ?? '') ?>">/* masque #canvas le temps que les effets démarrent (jamais plus de 2,5 s, jamais en mouvement réduit) */
(function (r) { if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  r.classList.add('fx-boot'); setTimeout(function () { r.classList.remove('fx-boot'); }, 2500); })(document.documentElement);</script>
</head>
<body>
<?php require __DIR__ . '/../includes/spinner.php'; ?>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>
<div class="app">
  <main class="canvas" id="canvas" tabindex="-1">
    <?php if ($messageOk): ?>
      <p class="gnotice" role="status"><?= $e($messageOk) ?></p>
    <?php endif; ?>
    <?php if ($messageErreur): ?>
      <p class="gnotice err" role="alert"><?= $e($messageErreur) ?></p>
    <?php endif; ?>

    <!-- ===================== Toutes les guildes ===================== -->
    <section class="yq-view<?= $vueParDefaut === 'guildes' ? ' yq-default' : '' ?>" id="guildes">
      <div class="title-row"><div><p class="kicker"><?= $e($kicker) ?> · guildes</p><h1>Guildes</h1><p class="about">Une seule guilde par membre, <?= GuildeC::MEMBRES_MAX ?> membres au plus.</p>
        <p class="sub"><?php if ($maGuilde): ?>Vous êtes membre de <a class="glink" href="#g<?= (int) $maGuilde['id_guilde'] ?>"><strong><?= $e($maGuilde['nom']) ?></strong></a>.<?php if ($aVoter > 0): ?> <strong><?= $aVoter ?> vote<?= $aVoter > 1 ? 's' : '' ?> vous attend<?= $aVoter > 1 ? 'ent' : '' ?>.</strong><?php endif; ?><?php else: ?>Vous n’avez pas encore de guilde : rejoignez-en une ou créez la vôtre.<?php endif; ?></p></div>
        <div class="actions"><?php if (!$maGuilde): ?><a class="btn primary" href="#creer-guilde">Créer une guilde</a><?php else: ?><a class="btn primary" href="#proposer">Proposer une quête</a><?php endif; ?></div>
      </div>
      <?php $nav('guildes'); ?>
      <?php if (empty($guildes)): ?>
        <div class="empty">Aucune guilde pour l’instant. Créez la première !</div>
      <?php else: ?>
      <div class="sheet"><div class="table-wrap"><table class="respond">
        <thead><tr><th>Guilde</th><th>Fondée par</th><th>Création</th><th class="n">Membres</th><th class="n">En vote</th><th class="n"><span class="sr">Action</span></th></tr></thead>
        <tbody>
        <?php foreach ($guildes as $g): $id = (int) $g['id_guilde']; $estMienne = $id === $idMaGuilde; ?>
          <tr>
            <td class="id"><span class="gcell"><img src="../../img/competences/<?= $e(GuildeC::image(null, $id)) ?>" alt=""><span><a class="glink" href="#g<?= $id ?>"><strong><?= $e($g['nom']) ?></strong></a><?php if ((string) $g['description'] !== ''): ?><span class="desc"><?= $e($g['description']) ?></span><?php endif; ?></span></span></td>
            <td class="hide-sm"><?= (int) $g['id_utilisateur_createur'] === $idUser ? 'vous' : $e($g['createur']) ?></td>
            <td class="hide-sm"><?= $e(GuildeC::dateFr($g['date_creation'])) ?></td>
            <td class="n hide-sm"><?= (int) $g['nb_membres'] ?> / <?= GuildeC::MEMBRES_MAX ?></td>
            <td class="n second"><?php if ((int) $g['nb_en_vote'] > 0): ?><span class="pill lilac"><?= (int) $g['nb_en_vote'] ?> en vote</span><?php else: ?>0 en vote<?php endif; ?></td>
            <td class="end"><span class="row-actions"><a class="btn" href="#g<?= $id ?>"><?= $estMienne ? 'Ouvrir' : 'Voir' ?></a><?php if (!$maGuilde) { $boutonRejoindre($g, 'btn primary'); } ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php endif; ?>
    </section>

    <!-- ===================== Une section par guilde ===================== -->
    <?php foreach ($guildes as $g):
        $id = (int) $g['id_guilde'];
        $estMienne = $id === $idMaGuilde;
        $props = $propsParGuilde[$id] ?? [];
        $membres = $membresParGuilde[$id] ?? [];
        $nbMembres = count($membres);
        $enVote = array_values(array_filter($props, static fn ($p) => $p['statut'] === 'en_vote'));
        $adoptees = array_values(array_filter($props, static fn ($p) => $p['statut'] === 'adoptee'));
        $img = GuildeC::image(null, $id);
    ?>
    <section class="yq-view<?= $vueParDefaut === 'g' . $id ? ' yq-default' : '' ?>" id="g<?= $id ?>">
      <div class="title-row">
        <div class="head"><img class="emblem" src="../../img/competences/<?= $e($img) ?>" alt="">
          <div><p class="kicker"><?= $e($kicker) ?> · guilde</p><h1><?= $e($g['nom']) ?></h1><?php if ((string) $g['description'] !== ''): ?><p class="about"><?= $e($g['description']) ?></p><?php endif; ?>
            <p class="sub"><strong><?= count($enVote) ?> proposition<?= count($enVote) > 1 ? 's' : '' ?> en vote.</strong><?php if ($estMienne && $aVoter > 0): ?> <?= $aVoter ?> attend<?= $aVoter > 1 ? 'ent' : '' ?> votre vote.<?php elseif (!$estMienne): ?> Seuls les membres de <?= $e($g['nom']) ?> votent ici.<?php endif; ?></p></div></div>
        <div class="actions">
          <?php if ($estMienne): ?>
            <a class="btn primary" href="#proposer">Proposer une quête</a>
          <?php elseif ($maGuilde): ?>
            <span class="pill">Vous êtes membre de <?= $e($maGuilde['nom']) ?></span>
          <?php else: $boutonRejoindre($g, 'btn primary'); endif; ?>
        </div>
      </div>
      <?php $nav('g' . $id); ?>
      <div class="split">
        <section class="stack" aria-labelledby="h-props--g<?= $id ?>">
          <h2 id="h-props--g<?= $id ?>" class="sr">Propositions de quêtes</h2>
          <?php if (empty($props)): ?>
            <div class="empty">Aucune quête proposée pour l’instant.<?php if ($estMienne): ?> <a class="glink" href="#proposer">Proposer la première</a><?php endif; ?></div>
          <?php endif; ?>
          <?php foreach ($props as $p):
              $pid = (int) $p['id_proposition'];
              $total = max($nbMembres, $p['pour'] + $p['contre'], 1);
              $wPour = round($p['pour'] * 100 / $total, 4);
              $wContre = round($p['contre'] * 100 / $total, 4);
              $sansVote = max(0, $nbMembres - $p['pour'] - $p['contre']);
              $pImg = GuildeC::image($p['visuel'], (int) $p['id_quete']);
              $joursRestants = (int) (new DateTimeImmutable($aujourdhui))->diff(new DateTimeImmutable($p['date_limite_vote']))->format('%r%a');
              $vote = $monVote($pid);
              $votants = $votes[$pid] ?? [];
          ?>
          <article class="proposal" aria-labelledby="t-p<?= $pid ?>">
            <img class="art" src="../../img/competences/<?= $e($pImg) ?>" alt="" loading="lazy">
            <div class="body">
              <p class="qline"><img src="../../img/competences/<?= $e($pImg) ?>" alt=""><?= $e($p['competence'] ?? 'Compétence') ?> · <?= $e($p['categorie'] ?? 'Catégorie') ?> · <?= $e($p['difficulte']) ?> · <?= (int) $p['xp'] ?> XP<span class="flag"><?= $e($libelleStatut[$p['statut']]) ?></span></p>
              <h3 id="t-p<?= $pid ?>"><?= $e($p['titre']) ?></h3>
              <div class="votebar" role="img" aria-label="<?= $p['pour'] ?> pour, <?= $p['contre'] ?> contre, <?= $sansVote ?> sans vote">
                <i class="pour" data-w="<?= $wPour ?>" style="width:<?= $wPour ?>%"></i><i class="contre" data-w="<?= $wContre ?>" style="width:<?= $wContre ?>%"></i></div>
              <p class="votelegend"><span><b class="k pour"></b><?= $p['pour'] ?> pour</span><span><b class="k contre"></b><?= $p['contre'] ?> contre</span><span><b class="k none"></b><?= $sansVote ?> <?= $sansVote > 1 ? 'n’ont' : 'n’a' ?> pas voté</span></p>
              <div class="pfoot">
                <?php if ($p['statut'] === 'en_vote'): ?>
                  <p class="deadline<?= $joursRestants <= 1 ? ' urgent' : '' ?>">Clôture du vote <strong><?= $joursRestants === 0 ? 'aujourd’hui à 23:59' : ($joursRestants === 1 ? 'demain à 23:59' : 'le ' . $e(GuildeC::dateFr($p['date_limite_vote'])) . ' (dans ' . $joursRestants . ' jours)') ?></strong><?= !$estMienne && !$maGuilde ? ' · rejoignez la guilde pour voter' : '' ?></p>
                  <?php if ($estMienne): ?>
                  <form class="choice" role="group" aria-label="Votre vote sur « <?= $e($p['titre']) ?> »" method="post" action="guildes.php#g<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="form" value="vote">
                    <input type="hidden" name="id_proposition" value="<?= $pid ?>">
                    <?php foreach (['contre' => 'Contre', 'pour' => 'Pour'] as $valeur => $libelle): ?>
                      <button type="submit" name="reponse" value="<?= $valeur ?>" data-vote="p<?= $pid ?>" data-answer="<?= $valeur ?>" aria-pressed="<?= $vote && $vote['reponse_vote'] === $valeur ? 'true' : 'false' ?>"><?= $libelle ?></button>
                    <?php endforeach; ?>
                  </form>
                  <?php if ($vote): ?><p class="mine">Vous avez voté <?= $e($vote['reponse_vote']) ?> le <?= $e(GuildeC::dateFr($vote['date_vote'])) ?> · modifiable jusqu’à la clôture</p><?php endif; ?>
                  <?php endif; ?>
                <?php else: ?>
                  <p class="deadline">Vote clos le <strong><?= $e(GuildeC::dateFr($p['date_limite_vote'])) ?></strong></p>
                  <span class="result <?= $p['statut'] === 'adoptee' ? 'yes' : 'no' ?>"><?= $e($libelleStatut[$p['statut']]) ?></span>
                <?php endif; ?>
              </div>
              <?php if (empty($votants)): ?>
                <p class="mine">Proposée le <?= $e(GuildeC::dateFr($p['date_proposition'])) ?> · aucun vote pour l’instant</p>
              <?php else: ?>
              <details class="voters">
                <summary>Proposée le <?= $e(GuildeC::dateFr($p['date_proposition'])) ?> · voir <?= count($votants) ?> vote<?= count($votants) > 1 ? 's' : '' ?></summary>
                <ul><?php foreach ($votants as $v): $estMoi = (int) $v['id_utilisateur'] === $idUser; ?><li><span class="avatar sm" aria-hidden="true"><?= $e(GuildeC::initiales($v['nom_affiche'])) ?></span><span class="nm"><?= $estMoi ? 'vous' : $e($v['nom_affiche']) ?></span><span class="ans"><?= $e($v['reponse_vote']) ?></span><span><?= $e(GuildeC::dateFr($v['date_vote'])) ?></span></li><?php endforeach; ?></ul>
              </details>
              <?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </section>
        <aside class="rail">
          <section aria-labelledby="h-mem--g<?= $id ?>"><h2 id="h-mem--g<?= $id ?>">Membres</h2>
            <div class="panel yq-st-1">
              <?php $wCap = min(100, $nbMembres * 100 / GuildeC::MEMBRES_MAX); ?>
              <div class="cap"><span><b><?= $nbMembres ?> / <?= GuildeC::MEMBRES_MAX ?> membres</b> · fondée par <?= (int) $g['id_utilisateur_createur'] === $idUser ? 'vous' : $e($g['createur']) ?> le <?= $e(GuildeC::dateFr($g['date_creation'])) ?></span><div class="capbar"><i data-w="<?= $wCap ?>" style="width:<?= $wCap ?>%"></i></div></div>
              <div class="rows">
                <?php foreach ($membres as $m): $estMoi = (int) $m['id_utilisateur'] === $idUser; $fondateur = (int) $m['id_utilisateur'] === (int) $g['id_utilisateur_createur']; ?>
                  <div class="row<?= $estMoi ? ' me' : '' ?>"><span class="avatar sm" aria-hidden="true"><?= $e(GuildeC::initiales($m['nom_affiche'])) ?></span>
                    <span class="who"><strong><?= $e($m['nom_affiche']) ?><?= $estMoi ? ' (vous)' : '' ?></strong><span><?= $fondateur ? 'A créé la guilde · ' : '' ?>membre depuis le <?= $e(GuildeC::dateFr($m['date_adhesion'])) ?></span></span></div>
                <?php endforeach; ?>
              </div>
            </div>
          </section>
          <section aria-labelledby="h-adopt--g<?= $id ?>"><h2 id="h-adopt--g<?= $id ?>">Quêtes adoptées · <?= count($adoptees) ?></h2>
            <div class="panel yq-st-2"><div class="rows">
              <?php if (empty($adoptees)): ?>
                <div class="row"><span class="who"><span>Aucune quête adoptée pour l’instant.</span></span></div>
              <?php endif; ?>
              <?php foreach ($adoptees as $p): ?>
                <div class="row adopted"><img src="../../img/competences/<?= $e(GuildeC::image($p['visuel'], (int) $p['id_quete'])) ?>" alt=""><span class="who"><strong><?= $e($p['titre']) ?></strong><span><?= $e($p['competence'] ?? '') ?> · adoptée le <?= $e(GuildeC::dateFr($p['date_limite_vote'])) ?></span></span></div>
              <?php endforeach; ?>
            </div></div>
          </section>
        </aside>
      </div>
    </section>
    <?php endforeach; ?>

    <!-- ===================== Quêtes proposées ===================== -->
    <section class="yq-view" id="quetes">
      <div class="title-row"><div><p class="kicker"><?= $e($kicker) ?> · catalogue</p><h1>Quêtes</h1><p class="about">Chaque quête crédite une compétence principale.</p>
        <p class="sub"><strong><?= count($propositions) ?> quête<?= count($propositions) > 1 ? 's' : '' ?> proposée<?= count($propositions) > 1 ? 's' : '' ?>.</strong> Une quête rejoint une guilde quand ses membres adoptent la proposition par vote.</p></div>
        <div class="actions"><?php if ($maGuilde): ?><a class="btn primary" href="#proposer">Proposer une quête</a><?php endif; ?></div>
      </div>
      <?php $nav('quetes'); ?>
      <?php if (empty($propositions)): ?>
        <div class="empty">Aucune quête proposée pour l’instant.</div>
      <?php else: ?>
      <div class="sheet"><div class="table-wrap"><table class="respond">
        <thead><tr><th>Quête</th><th>Type</th><th>Proposée le</th><th>Statut</th><th>Guilde</th></tr></thead>
        <tbody>
        <?php foreach ($propositions as $p): ?>
          <tr>
            <td class="id"><span class="qt"><?= $e($p['titre']) ?></span><span class="desc"><?= $e($p['categorie'] ?? '') ?> · <?= $e($p['difficulte']) ?> · <?= (int) $p['xp'] ?> XP</span></td>
            <td class="hide-sm"><span class="skill"><img src="../../img/competences/<?= $e(GuildeC::image($p['visuel'], (int) $p['id_quete'])) ?>" alt=""><?= $e($p['competence'] ?? '') ?></span></td>
            <td class="second yq-st-3"><?= $e(GuildeC::dateFr($p['date_proposition'])) ?></td>
            <td class="hide-sm"><?= $e($libelleStatut[$p['statut']]) ?></td>
            <td class="hide-sm"><span class="chips"><a class="chip" href="#g<?= (int) $p['id_guilde'] ?>"><?= $e($p['guilde']) ?></a></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php endif; ?>
    </section>

    <!-- ===================== Formulaire : créer une guilde ===================== -->
    <section class="yq-view<?= $vueParDefaut === 'creer-guilde' ? ' yq-default' : '' ?>" id="creer-guilde">
      <div class="title-row"><div><p class="kicker"><?= $e($kicker) ?> · nouvelle guilde</p><h1>Créer une guilde</h1><p class="about">Vous en serez le fondateur et le premier membre.</p></div></div>
      <?php $nav('creer-guilde'); ?>
      <?php if ($maGuilde && $vueFormulaire !== 'creer-guilde'): ?>
        <div class="empty">Vous êtes déjà membre de <a class="glink" href="#g<?= (int) $maGuilde['id_guilde'] ?>"><strong><?= $e($maGuilde['nom']) ?></strong></a> : un membre ne peut appartenir qu’à une seule guilde.</div>
      <?php else: ?>
      <form class="gform panel" method="post" action="guildes.php#creer-guilde">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <input type="hidden" name="form" value="guilde">
        <?php if (isset($erreursGuilde['general'])): ?><p class="alert" role="alert"><?= $e($erreursGuilde['general']) ?></p><?php endif; ?>
        <div class="field full">
          <label for="guilde-nom">Nom de la guilde <span class="req">*</span></label>
          <input class="input" type="text" id="guilde-nom" name="nom" value="<?= $e($oldGuilde['nom']) ?>" placeholder="Ex : Les Lanternes" required minlength="3" maxlength="100" autocomplete="off"<?= $invalide($erreursGuilde, 'nom') ?>>
          <span class="hint">3 à 100 caractères : lettres, chiffres, espaces, apostrophes et tirets. Doit être unique.</span>
          <?php $erreurChamp($erreursGuilde, 'nom'); ?>
        </div>
        <div class="field full">
          <label for="guilde-description">Description</label>
          <textarea class="input" id="guilde-description" name="description" rows="3" maxlength="300" placeholder="Ex : Marcher, s'étirer et cuisiner ensemble après les cours."<?= $invalide($erreursGuilde, 'description') ?>><?= $e($oldGuilde['description']) ?></textarea>
          <span class="hint">Facultative, 300 caractères au plus.</span>
          <?php $erreurChamp($erreursGuilde, 'description'); ?>
        </div>
        <div class="gform-actions full">
          <button class="btn primary" type="submit">Créer la guilde</button>
          <a class="btn" href="#guildes">Annuler</a>
        </div>
      </form>
      <?php endif; ?>
    </section>

    <!-- ===================== Formulaire : proposer une quête ===================== -->
    <section class="yq-view<?= $vueParDefaut === 'proposer' ? ' yq-default' : '' ?>" id="proposer">
      <div class="title-row"><div><p class="kicker"><?= $e($kicker) ?> · nouvelle proposition</p><h1>Proposer une quête</h1>
        <p class="about"><?php if ($maGuilde): ?>La quête sera soumise au vote des membres de <strong><?= $e($maGuilde['nom']) ?></strong>.<?php else: ?>Seuls les membres d’une guilde peuvent proposer une quête.<?php endif; ?></p></div></div>
      <?php $nav('proposer'); ?>
      <?php if (!$maGuilde): ?>
        <div class="empty">Vous n’avez pas encore de guilde. <a class="glink" href="#creer-guilde"><strong>Créer une guilde</strong></a></div>
      <?php elseif (empty($competences) || empty($categories)): ?>
        <div class="empty">Aucune compétence ou catégorie n’est encore enregistrée : importez yalla_quest.sql pour les ajouter.</div>
      <?php else: ?>
      <form class="gform panel" method="post" action="guildes.php#proposer">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <input type="hidden" name="form" value="proposition">
        <?php if (isset($erreursQuete['general'])): ?><p class="alert" role="alert"><?= $e($erreursQuete['general']) ?></p><?php endif; ?>
        <div class="field full">
          <label for="quete-titre">Titre de la quête <span class="req">*</span></label>
          <input class="input" type="text" id="quete-titre" name="titre" value="<?= $e($oldQuete['titre']) ?>" placeholder="Ex : Marcher 30 minutes dans la médina" required minlength="3" maxlength="150" autocomplete="off"<?= $invalide($erreursQuete, 'titre') ?>>
          <?php $erreurChamp($erreursQuete, 'titre'); ?>
        </div>
        <div class="field">
          <label for="quete-competence">Compétence <span class="req">*</span></label>
          <select class="input" id="quete-competence" name="id_competence" required<?= $invalide($erreursQuete, 'id_competence') ?>>
            <option value="">Choisir…</option>
            <?php foreach ($competences as $c): ?>
              <option value="<?= (int) $c['id_competence'] ?>"<?= (string) $c['id_competence'] === $oldQuete['id_competence'] ? ' selected' : '' ?>><?= $e($c['nom']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php $erreurChamp($erreursQuete, 'id_competence'); ?>
        </div>
        <div class="field">
          <label for="quete-categorie">Catégorie <span class="req">*</span></label>
          <select class="input" id="quete-categorie" name="id_categorie" required<?= $invalide($erreursQuete, 'id_categorie') ?>>
            <option value="">Choisir…</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id_categorie'] ?>"<?= (string) $c['id_categorie'] === $oldQuete['id_categorie'] ? ' selected' : '' ?>><?= $e($c['nom']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php $erreurChamp($erreursQuete, 'id_categorie'); ?>
        </div>
        <div class="field">
          <label for="quete-difficulte">Difficulté <span class="req">*</span></label>
          <select class="input" id="quete-difficulte" name="difficulte" required<?= $invalide($erreursQuete, 'difficulte') ?>>
            <option value="">Choisir…</option>
            <?php foreach (GuildeC::DIFFICULTES as $d): ?>
              <option value="<?= $e($d) ?>"<?= $d === $oldQuete['difficulte'] ? ' selected' : '' ?>><?= $e($d) ?></option>
            <?php endforeach; ?>
          </select>
          <?php $erreurChamp($erreursQuete, 'difficulte'); ?>
        </div>
        <div class="field">
          <label for="quete-xp">XP gagnés <span class="req">*</span></label>
          <input class="input" type="number" id="quete-xp" name="xp" value="<?= $e($oldQuete['xp']) ?>" min="<?= GuildeC::XP_MIN ?>" max="<?= GuildeC::XP_MAX ?>" step="1" required<?= $invalide($erreursQuete, 'xp') ?>>
          <span class="hint">Entre <?= GuildeC::XP_MIN ?> et <?= GuildeC::XP_MAX ?>.</span>
          <?php $erreurChamp($erreursQuete, 'xp'); ?>
        </div>
        <div class="field full">
          <label for="quete-date">Fin du vote <span class="req">*</span></label>
          <input class="input" type="date" id="quete-date" name="date_limite_vote" value="<?= $e($oldQuete['date_limite_vote']) ?>" min="<?= $e($dateMin) ?>" max="<?= $e($dateMax) ?>" required<?= $invalide($erreursQuete, 'date_limite_vote') ?>>
          <span class="hint">Les membres votent jusqu’à ce jour inclus (de demain à <?= GuildeC::VOTE_JOURS_MAX ?> jours au plus).</span>
          <?php $erreurChamp($erreursQuete, 'date_limite_vote'); ?>
        </div>
        <div class="gform-actions full">
          <button class="btn primary" type="submit">Proposer la quête</button>
          <a class="btn" href="#g<?= (int) $maGuilde['id_guilde'] ?>">Annuler</a>
        </div>
      </form>
      <?php endif; ?>
    </section>
  </main>
</div>
<!-- Effets : bibliothèques locales (Views/lib/), avec un CDN de secours si un fichier manque -->
<script nonce="<?= $e($cspNonce ?? '') ?>" src="../../lib/gsap/gsap.min.js"></script>
<script nonce="<?= $e($cspNonce ?? '') ?>">window.gsap || document.write('<script nonce="<?= $e($cspNonce ?? '') ?>" src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"><\/script>');</script>
<script nonce="<?= $e($cspNonce ?? '') ?>" src="../../lib/gsap/ScrollTrigger.min.js"></script>
<script nonce="<?= $e($cspNonce ?? '') ?>">window.ScrollTrigger || document.write('<script nonce="<?= $e($cspNonce ?? '') ?>" src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"><\/script>');</script>
<script nonce="<?= $e($cspNonce ?? '') ?>" src="../../lib/lenis/lenis.min.js"></script>
<script nonce="<?= $e($cspNonce ?? '') ?>">window.Lenis || document.write('<script nonce="<?= $e($cspNonce ?? '') ?>" src="https://cdn.jsdelivr.net/npm/lenis@1.1.14/dist/lenis.min.js"><\/script>');</script>
<script nonce="<?= $e($cspNonce ?? '') ?>" src="../../lib/splittype/split-type.min.js"></script>
<script nonce="<?= $e($cspNonce ?? '') ?>">window.SplitType || document.write('<script nonce="<?= $e($cspNonce ?? '') ?>" src="https://cdn.jsdelivr.net/npm/split-type@0.3.4/umd/index.min.js"><\/script>');</script>
<script nonce="<?= $e($cspNonce ?? '') ?>" type="importmap">{"imports":{"three":"../../lib/three/three.module.min.js"}}</script>
<script nonce="<?= $e($cspNonce ?? '') ?>" type="module" src="../../js/effects/main.js" onerror="document.documentElement.classList.remove('fx-boot')"></script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
