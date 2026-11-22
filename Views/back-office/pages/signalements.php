<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

(function () {
    // Les mêmes choix sont utilisés par le front-office et l'administration.
    $categories = [
        'all' => 'Tout le camp',
        'nutrition' => 'Nutrition',
        'mouvement' => 'Mouvement',
        'serenite' => 'Sérénité',
    ];
    $publicationTypes = ['Petite victoire', 'Conseil', 'Discussion'];
    $publicationStatuses = [
        'published' => 'Publiée',
        'draft' => 'Brouillon',
        'hidden' => 'Masquée',
    ];
    $publicationImages = [
        '' => 'Aucune illustration',
        'nutrition' => 'Nutrition',
        'endurance' => 'Endurance',
        'mobilite' => 'Mobilité',
        'force' => 'Force',
        'focus' => 'Sérénité et concentration',
    ];
    $reportReasons = [
        'spam' => 'Spam ou publicité',
        'inapproprie' => 'Contenu inapproprié',
        'dangereux' => 'Conseil potentiellement dangereux',
        'autre' => 'Autre motif',
    ];
    $reportStatuses = [
        'pending' => 'À traiter',
        'reviewing' => 'En cours',
        'resolved' => 'Traité',
        'dismissed' => 'Classé sans suite',
    ];
    $reportDecisions = [
        'keep' => 'Publication conservée',
        'hide' => 'Publication masquée',
        'delete' => 'Publication supprimée',
    ];


    // Données d'exemple du template : aucun enregistrement en base de données.
    $publicationsDemo = [
        1 => [
            'id' => 1, 'author' => 'Amina', 'initials' => 'AM', 'category' => 'nutrition',
            'type' => 'Conseil', 'title' => 'Un petit repas, une belle victoire',
            'content' => 'Cette semaine, je prends le temps de cuisiner un repas simple avec des produits frais. Un petit pas qui fait du bien !',
            'more' => 'Mon conseil : commencer avec une recette facile et avancer à son rythme.',
            'image' => 'nutrition', 'status' => 'published', 'created_at' => '2026-11-21 09:00:00', 'updated_at' => '2026-11-21 09:00:00',
        ],
        2 => [
            'id' => 2, 'author' => 'Nour', 'initials' => 'NO', 'category' => 'mouvement',
            'type' => 'Petite victoire', 'title' => 'Mon premier tour du quartier',
            'content' => 'Une marche de vingt minutes avec une amie. On a discuté, découvert un nouveau chemin et terminé avec le sourire.',
            'more' => '', 'image' => 'mobilite', 'status' => 'published',
            'created_at' => '2026-11-21 10:00:00', 'updated_at' => '2026-11-21 10:00:00',
        ],
        3 => [
            'id' => 3, 'author' => 'Youssef', 'initials' => 'YO', 'category' => 'serenite',
            'type' => 'Discussion', 'title' => 'Une pause pour souffler',
            'content' => 'Je garde cinq minutes dans ma journée pour respirer et me recentrer. Quelles sont vos petites habitudes pour retrouver le calme ?',
            'more' => '', 'image' => 'focus', 'status' => 'published',
            'created_at' => '2026-11-21 11:00:00', 'updated_at' => '2026-11-21 11:00:00',
        ],
        4 => [
            'id' => 4, 'author' => 'Équipe YallaQuest', 'initials' => 'ÉQ', 'category' => 'mouvement',
            'type' => 'Conseil', 'title' => 'Préparer sa prochaine aventure',
            'content' => 'Un exemple de brouillon pour préparer un nouveau conseil à partager avec le camp.',
            'more' => '', 'image' => 'force', 'status' => 'draft',
            'created_at' => '2026-11-21 12:00:00', 'updated_at' => '2026-11-21 12:00:00',
        ],
    ];
    $signalementsDemo = [
        1 => [
            'id' => 1, 'publication_id' => 1, 'publication_title' => $publicationsDemo[1]['title'],
            'publication_author' => 'Amina', 'publication_content' => $publicationsDemo[1]['content'],
            'reason' => 'autre', 'details' => 'Exemple de signalement à examiner dans le template.',
            'status' => 'pending', 'moderator_note' => '', 'decision' => null,
            'created_at' => '2026-11-21 12:30:00', 'handled_at' => null,
        ],
        2 => [
            'id' => 2, 'publication_id' => 2, 'publication_title' => $publicationsDemo[2]['title'],
            'publication_author' => 'Nour', 'publication_content' => $publicationsDemo[2]['content'],
            'reason' => 'spam', 'details' => 'Exemple de signalement déjà traité.',
            'status' => 'dismissed', 'moderator_note' => 'La publication respecte la charte du camp.',
            'decision' => 'keep', 'created_at' => '2026-11-21 13:00:00', 'handled_at' => '2026-11-21 14:00:00',
        ],
    ];
    $page = (is_string($_GET['action'] ?? null) ? $_GET['action'] : 'liste');
    $id = (is_scalar($_GET['id'] ?? null) ? (int) $_GET['id'] : 1);
    $report = $signalementsDemo[$id] ?? $signalementsDemo[1];
    $title = $page === 'voir' ? 'Signalement #' . $report['id'] : 'Signalements';
    $flash = null;
    $pageError = '';
    $note = $report['moderator_note'];
    $publication = $publicationsDemo[$report['publication_id']] ?? null;
    $isOpen = $report['status'] === 'pending' || $report['status'] === 'reviewing';
    $selectedStatus = (is_string($_GET['statut'] ?? null) ? $_GET['statut'] : 'open');
    $search = (is_string($_GET['recherche'] ?? null) ? $_GET['recherche'] : '');
    $publicationId = (is_scalar($_GET['publication'] ?? null) ? (int) $_GET['publication'] : 0);
    $reportFilters = ['open' => 'À examiner', 'closed' => 'Historique', 'all' => 'Tous'] + $reportStatuses;
    $counts = ['pending' => 0, 'reviewing' => 0, 'resolved' => 0, 'dismissed' => 0];
    $reports = [];
    foreach ($signalementsDemo as $item) {
        $counts[$item['status']]++;
        $matchesStatus = $selectedStatus === 'all' || $item['status'] === $selectedStatus;
        if ($selectedStatus === 'open') $matchesStatus = $item['status'] === 'pending' || $item['status'] === 'reviewing';
        if ($selectedStatus === 'closed') $matchesStatus = $item['status'] === 'resolved' || $item['status'] === 'dismissed';
        $text = $item['publication_title'] . ' ' . $item['publication_author'] . ' ' . $item['details'];
        if ($matchesStatus && (!$publicationId || $item['publication_id'] == $publicationId)
            && ($search === '' || mb_stripos($text, $search, 0, 'UTF-8') !== false)) {
            $reports[] = $item;
        }
    }
    $standalone = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)
        && realpath(get_included_files()[0] ?? '') === realpath(__FILE__);
?>
<?php if ($standalone) { ?>
<!doctype html>
<html lang="fr">
<head>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> — YallaQuest</title>
<?php } ?>
<link rel="stylesheet" href="../../css/communaute-base.css">
<link rel="stylesheet" href="../../css/communaute-admin.css">
<?php if ($standalone) { ?>
</head>
<body class="yq-community-page">
<?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php include_once __DIR__ . '/../partials/header.php'; ?>
<?php } ?>
<div class="yq-community yq-community-admin">
  <?php if ($flash) { ?>
    <p role="status" class="feedback <?= htmlspecialchars((string) ($flash['type'] == 'error' ? 'feedback-error' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
  <?php } ?>
  <?php if ($pageError != '') { ?>
    <section class="panel empty-state">
      <h1><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
      <a href="signalements.php" class="button">Revenir à la liste</a>
    </section>
  <?php } else { ?>
<?php if ($page == 'liste') { ?>
<div class="page-heading">
  <div><p class="eyebrow">Modération du camp</p><h1>Signalements<span>.</span></h1><p>Examiner les contenus signalés et consulter les décisions.</p></div>
</div>
<div class="stat-grid">
  <?php foreach ($reportStatuses as $key => $label) { ?>
    <article class="stat-card"><p><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><strong><?= (int) ($counts[$key]) ?></strong></article>
  <?php } ?>
</div>
<section class="panel">
  <nav class="tabs" aria-label="État des signalements">
    <?php foreach (['open' => 'À examiner', 'closed' => 'Historique', 'all' => 'Tous'] as $key => $label) { ?>
      <a href="signalements.php?statut=<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>&amp;recherche=<?= htmlspecialchars(urlencode($search), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?php if ($publicationId > 0) { ?>&amp;publication=<?= htmlspecialchars((string) ($publicationId), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?php } ?>" <?php if ($selectedStatus == $key) { ?>aria-current="page"<?php } ?>><?= htmlspecialchars((string) ($label), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
    <?php } ?>
  </nav>
  <form action="signalements.php" method="get" class="filter-form" novalidate>
    <input type="hidden" name="module" value="signalements">
    <?php if ($publicationId) { ?><input type="hidden" name="publication" value="<?= (int) $publicationId ?>"><?php } ?>
    <div><label for="community-admin-search">Rechercher</label><input id="community-admin-search" type="search" name="recherche" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Publication, auteur, précisions…"></div>
    <div><label for="community-admin-status">Statut</label><select id="community-admin-status" name="statut">
      <?php foreach ($reportFilters as $key => $label) { ?>
        <option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($selectedStatus == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
      <?php } ?>
    </select></div>
    <button type="submit" class="button">Filtrer</button><a href="signalements.php" class="text-link">Réinitialiser</a>
  </form>
  <div class="table-scroll">
    <table>
      <caption><?= count($reports) ?> signalement<?php if (count($reports) > 1) { ?>s<?php } ?> affiché<?php if (count($reports) > 1) { ?>s<?php } ?></caption>
      <thead><tr><th scope="col">Publication</th><th scope="col">Motif</th><th scope="col">Statut</th><th scope="col">Reçu le</th><th scope="col">Action</th></tr></thead>
      <tbody>
        <?php foreach ($reports as $report) { ?>
          <tr>
            <td><?= htmlspecialchars($report['publication_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><small><?= htmlspecialchars($report['publication_author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></td>
            <td><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['reason'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></td>
            <td><span class="badge status-<?= htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span></td>
            <td><?= date('d/m/Y à H:i', strtotime($report['created_at'])) ?></td>
            <td><a href="signalements.php?action=voir&amp;id=<?= (int) $report['id'] ?>"><?php if ($report['status'] == 'pending' || $report['status'] == 'reviewing') { ?>Examiner<?php } else { ?>Consulter<?php } ?> →</a></td>
          </tr>
        <?php } ?>
        <?php if (empty($reports)) { ?><tr><td colspan="5" class="empty-state">Aucun signalement à afficher.</td></tr><?php } ?>
      </tbody>
    </table>
  </div>
</section>

<?php } elseif ($page == 'voir') { ?>
<a href="signalements.php" class="back-link">← Signalements</a>
<div class="page-heading">
  <div><p class="eyebrow">Modération du camp</p><h1>Signalement #<?= (int) $report['id'] ?><span>.</span></h1><p>Reçu le <?= date('d/m/Y à H:i', strtotime($report['created_at'])) ?></p></div>
  <span class="badge status-<?= htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span>
</div>
<div class="detail-grid">
  <div class="side-panels">
    <section class="panel">
      <h2>Motif du signalement</h2><p><strong><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['reason'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></strong></p>
      <p class="story-text"><?php if ($report['details'] != '') { echo htmlspecialchars($report['details'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { ?>Aucune précision ajoutée par le visiteur.<?php } ?></p>
    </section>
    <article class="panel story-panel">
      <h2>Contenu au moment du signalement</h2>
      <?php if ($publication) { ?><a href="publications.php?action=voir&amp;id=<?= (int) $publication['id'] ?>" class="text-link">Voir la publication actuelle →</a><?php } ?>
      <div class="complement">
        <p class="muted"><?= htmlspecialchars($report['publication_author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <h3><?= htmlspecialchars($report['publication_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
        <p class="story-text"><?= htmlspecialchars($report['publication_content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
      </div>
      <p class="muted"><?php if ($publication) { ?>Statut actuel : <?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?><?php } else { ?>La publication a été supprimée.<?php } ?></p>
    </article>
  </div>
  <aside class="side-panels">
    <?php if ($isOpen) { ?>
      <section class="panel form-panel">
        <h2>Traiter le signalement</h2>
        <form id="community-moderation-form" action="signalements.php?action=voir&amp;id=<?= (int) $report['id'] ?>" method="post" class="form-panel" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <div><label for="community-admin-note">Note de modération</label><textarea id="community-admin-note" name="note" rows="5"><?= htmlspecialchars($note, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea></div>
          <button type="submit" name="action" value="review" class="button-secondary"><?php if ($report['status'] == 'reviewing') { ?>Enregistrer la note<?php } else { ?>Prendre en charge<?php } ?></button>
          <button type="submit" name="action" value="keep" class="button">Conserver et classer</button>
          <button type="submit" name="action" value="hide" class="button-danger">Masquer la publication</button>
        </form>
      </section>
    <?php } else { ?>
      <section class="panel">
        <h2>Signalement clôturé</h2><p><?php if (isset($reportDecisions[$report['decision']])) { echo htmlspecialchars($reportDecisions[$report['decision']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { ?>Décision enregistrée<?php } ?></p>
        <?php if ($report['handled_at']) { ?><p class="muted"><?= date('d/m/Y à H:i', strtotime($report['handled_at'])) ?></p><?php } ?>
        <h3>Note de modération</h3><p class="story-text"><?php if ($report['moderator_note'] != '') { echo htmlspecialchars($report['moderator_note'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { ?>Aucune note ajoutée.<?php } ?></p>
        <a href="signalements.php?statut=closed" class="button-secondary">Consulter l’historique</a>
      </section>
    <?php } ?>
    <section class="panel charter-panel"><h2>La charte en trois mots</h2><p>Bienveillance. Partage. Respect du rythme de chacun.</p></section>
  </aside>
</div>
<?php } ?>
  <?php } ?>
</div>
<script src="../../js/communaute-validation.js" defer></script>
<?php if ($standalone) { ?>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
<?php } ?>
<?php })(); ?>
