<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'publications';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();


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
    $publication = $publicationsDemo[$id] ?? $publicationsDemo[1];
    $titles = ['liste' => 'Publications', 'voir' => 'Détail de la publication', 'ajouter' => 'Nouvelle publication', 'modifier' => 'Modifier la publication', 'supprimer' => 'Supprimer la publication', 'tableau-de-bord' => 'Tableau de bord'];
    $title = $titles[$page] ?? 'Publications';
    $flash = null;
    $pageError = '';
    $allPublications = array_values($publicationsDemo);
    $search = (is_string($_GET['recherche'] ?? null) ? $_GET['recherche'] : '');
    $selectedStatus = (is_string($_GET['statut'] ?? null) ? $_GET['statut'] : 'all');
    $selectedCategory = (is_string($_GET['categorie'] ?? null) ? $_GET['categorie'] : 'all');
    $counts = ['published' => 0, 'draft' => 0, 'hidden' => 0];
    $categoryCounts = ['nutrition' => 0, 'mouvement' => 0, 'serenite' => 0];
    $publications = [];
    foreach ($allPublications as $item) {
        $counts[$item['status']]++;
        if ($item['status'] === 'published') $categoryCounts[$item['category']]++;
        $item['report_count'] = 0;
        foreach ($signalementsDemo as $report) {
            if ($report['publication_id'] == $item['id']) $item['report_count']++;
        }
        $text = $item['title'] . ' ' . $item['author'] . ' ' . $item['content'];
        if (($selectedStatus === 'all' || $item['status'] === $selectedStatus)
            && ($selectedCategory === 'all' || $item['category'] === $selectedCategory)
            && ($search === '' || mb_stripos($text, $search, 0, 'UTF-8') !== false)) {
            $publications[] = $item;
        }
    }
    $relatedReports = [];
    $openReports = [];
    foreach ($signalementsDemo as $report) {
        if ($report['publication_id'] == $publication['id']) $relatedReports[] = $report;
        if ($report['status'] === 'pending' || $report['status'] === 'reviewing') $openReports[] = $report;
    }
    $publicationCounts = $counts;
    $recentPublications = array_slice($allPublications, 0, 5);
    $recentReports = array_slice($openReports, 0, 4);
    $isEdit = $page === 'modifier';
    $submitLabel = $isEdit ? 'Enregistrer les modifications' : 'Créer la publication';
    $data = $isEdit ? $publication : [
        'author' => 'Équipe YallaQuest', 'title' => '', 'content' => '', 'more' => '',
        'category' => 'nutrition', 'type' => 'Petite victoire', 'image' => '', 'status' => 'draft',
    ];
    $data['image'] = $data['image'] ?? '';
    $standalone = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)
        && realpath(get_included_files()[0] ?? '') === realpath(__FILE__);
?>
<?php if ($standalone) {
    $pageTitle   = $title . ' — YallaQuest';
    $currentPage = 'publications';
    require_once __DIR__ . '/../partials/head.php';
} else { ?>
<link rel="stylesheet" href="../../css/communaute-base.css">
<link rel="stylesheet" href="../../css/communaute-admin.css">
<?php } ?>
<div class="yq-community yq-community-admin">
  <?php if ($flash) { ?>
    <p role="status" class="feedback <?= htmlspecialchars((string) ($flash['type'] == 'error' ? 'feedback-error' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
  <?php } ?>
  <?php if ($pageError != '') { ?>
    <section class="panel empty-state">
      <h1><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
      <a href="publications.php" class="button">Revenir à la liste</a>
    </section>
  <?php } else { ?>
<?php if ($page == 'liste') { ?>
<div class="page-heading">
  <div><p class="eyebrow">Les récits du camp</p><h1>Publications<span>.</span></h1><p>Consulter et gérer les publications de la communauté.</p></div>
  <a href="publications.php?action=ajouter" class="button">Nouvelle publication +</a>
</div>
<div class="stat-grid">
  <article class="stat-card"><p>Total</p><strong><?= htmlspecialchars((string) (count($allPublications)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
  <?php foreach ($publicationStatuses as $key => $label) { ?>
    <article class="stat-card"><p><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><strong><?= htmlspecialchars((string) ((int) ($counts[$key])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
  <?php } ?>
</div>
<section class="panel">
  <form action="publications.php" method="get" class="filter-form" novalidate>
    <input type="hidden" name="module" value="publications">
    <div><label for="community-admin-search">Rechercher</label><input id="community-admin-search" type="search" name="recherche" placeholder="Titre, auteur, contenu…" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></div>
    <div><label for="community-admin-status">Statut</label><select id="community-admin-status" name="statut">
      <option value="all">Tous les statuts</option>
      <?php foreach ($publicationStatuses as $key => $label) { ?><option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($selectedStatus == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php } ?>
    </select></div>
    <div><label for="community-admin-category">Catégorie</label><select id="community-admin-category" name="categorie">
      <?php foreach ($categories as $key => $label) { ?><option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($selectedCategory == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php } ?>
    </select></div>
    <button type="submit" class="button">Filtrer</button>
    <a href="publications.php" class="text-link">Réinitialiser</a>
  </form>
  <div class="table-scroll">
    <table>
      <caption><?= htmlspecialchars((string) (count($publications)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> publication<?php if (count($publications) > 1) { ?>s<?php } ?> affichée<?php if (count($publications) > 1) { ?>s<?php } ?></caption>
      <thead><tr><th scope="col">Publication</th><th scope="col">Auteur</th><th scope="col">Catégorie</th><th scope="col">Statut</th><th scope="col">Signalements</th><th scope="col">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($publications as $publication) { ?>
          <tr>
            <td><a href="publications.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($publication['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a><small><?= htmlspecialchars($publication['type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></td>
            <td><?= htmlspecialchars($publication['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
            <td><?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></td>
            <td><span class="badge status-<?= htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span></td>
            <td><?= htmlspecialchars((string) ((int) $publication['report_count']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
            <td class="table-actions"><a href="publications.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Consulter</a><a href="publications.php?action=modifier&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Modifier</a><a href="publications.php?action=supprimer&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="danger-link">Supprimer</a></td>
          </tr>
        <?php } ?>
        <?php if (empty($publications)) { ?><tr><td colspan="6" class="empty-state">Aucune publication à afficher.</td></tr><?php } ?>
      </tbody>
    </table>
  </div>
</section>

<?php } elseif ($page == 'voir') { ?>
<a href="publications.php" class="back-link">← Publications</a>
<div class="page-heading">
  <div><p class="eyebrow">Publication #<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><h1>Détail de la publication<span>.</span></h1></div>
  <div class="actions"><a href="publications.php?action=modifier&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="button">Modifier</a><a href="publications.php?action=supprimer&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="button-secondary">Supprimer</a></div>
</div>
<div class="detail-grid">
  <article class="panel story-panel">
    <div class="publication-meta"><div><strong><?= htmlspecialchars($publication['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong><small><?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></small></div><span class="badge status-<?= htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span></div>
    <p class="eyebrow"><?= htmlspecialchars($publication['type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <h2><?= htmlspecialchars($publication['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
    <p class="story-text"><?= htmlspecialchars($publication['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php if ($publication['more'] !== '') { ?><section class="complement"><h3>Complément</h3><p class="story-text"><?= htmlspecialchars($publication['more'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p></section><?php } ?>
    <?php if ($publication['image'] && isset($publicationImages[$publication['image']])) { ?><img src="../../img/communaute-<?= htmlspecialchars($publication['image'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>.webp" alt="Illustration de la publication" class="story-image"><?php } ?>
  </article>
  <aside class="side-panels">
    <section class="panel">
      <h2>Informations</h2>
      <dl class="metadata">
        <div><dt>Créée le</dt><dd><?= htmlspecialchars((string) (date('d/m/Y à H:i', strtotime($publication['created_at']))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd></div>
        <div><dt>Dernière modification</dt><dd><?= htmlspecialchars((string) (date('d/m/Y à H:i', strtotime($publication['updated_at']))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd></div>
        <div><dt>Signalements reçus</dt><dd><?= htmlspecialchars((string) (count($relatedReports)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd></div>
      </dl>
      <?php if ($publication['status'] === 'published') { ?><a href="../../front-office/pages/communaute.php#community-publication-<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="button-secondary">Voir sur le site ↗</a><?php } ?>
    </section>
    <section class="panel">
      <h2>Signalements liés</h2>
      <?php foreach ($relatedReports as $report) { ?>
        <a href="signalements.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $report['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="report-link"><span class="badge status-<?= htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span><p><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['reason'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></p></a>
      <?php } ?>
      <?php if (empty($relatedReports)) { ?><p class="muted">Aucun signalement pour ce récit.</p><?php } ?>
    </section>
  </aside>
</div>

<?php } elseif ($page == 'ajouter' || $page == 'modifier') { ?>
<a href="publications.php" class="back-link">← Publications</a>
<div class="page-heading"><div><h1><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><span>.</span></h1><p>Un récit clair, une voix et un univers.</p></div></div>
<form id="community-admin-publication-form" action="publications.php?action=<?= htmlspecialchars((string) ($isEdit ? 'modifier' : 'ajouter'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?php if ($isEdit) { ?>&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?php } ?>" method="post" class="detail-grid" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
  <section class="panel form-panel" aria-label="Contenu de la publication">
    <div><label for="community-admin-title">Titre</label><input id="community-admin-title" name="title" value="<?= htmlspecialchars($data['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Le titre de l’histoire"></div>
    <div class="form-columns">
      <div><label for="community-admin-author">Auteur</label><input id="community-admin-author" name="author" value="<?= htmlspecialchars($data['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></div>
      <div><label for="community-admin-category">Catégorie</label><select id="community-admin-category" name="category">
        <?php foreach ($categories as $key => $label) { if ($key !== 'all') { ?>
          <option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($data['category'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
        <?php } } ?>
      </select></div>
    </div>
    <div><label for="community-admin-type">Type de publication</label><select id="community-admin-type" name="type">
      <?php foreach ($publicationTypes as $type) { ?><option <?php if ($data['type'] == $type) { ?>selected<?php } ?>><?= htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php } ?>
    </select></div>
    <div><label for="community-admin-content">Message</label><textarea id="community-admin-content" name="content" rows="9" placeholder="Raconter une petite victoire, partager un conseil…"><?= htmlspecialchars($data['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea></div>
    <details class="complement" <?php if ($data['more'] != '') { ?>open<?php } ?>>
      <summary>Ajouter un complément (facultatif)</summary>
      <label for="community-admin-more">Texte derrière « Lire la suite »</label><textarea id="community-admin-more" name="more" rows="4"><?= htmlspecialchars($data['more'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
    </details>
  </section>
  <aside class="panel form-panel" aria-label="Options de publication">
    <h2>Visibilité</h2>
    <div><label for="community-admin-status">Statut</label><select id="community-admin-status" name="status">
      <?php foreach ($publicationStatuses as $key => $label) { ?><option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($data['status'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php } ?>
    </select></div>
    <p class="muted">Publiée : visible dans la communauté.<br>Brouillon : conservée dans l’administration.<br>Masquée : retirée du fil.</p>
    <div><label for="community-admin-image">Illustration</label><select id="community-admin-image" name="image">
      <?php foreach ($publicationImages as $key => $label) { ?><option value="<?= htmlspecialchars((string) ($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?php if ($data['image'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php } ?>
    </select></div>
    <button type="submit" class="button"><?= htmlspecialchars($submitLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></button>
    <a href="publications.php" class="button-secondary">Annuler</a>
  </aside>
</form>

<?php } elseif ($page == 'supprimer') { ?>
<a href="publications.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="back-link">← Retour à la publication</a>
<section class="panel delete-panel">
  <h1>Supprimer cette publication ?</h1>
  <p>Elle sera supprimée définitivement du site. Les signalements reçus resteront consultables dans l’historique, avec une copie du contenu signalé.</p>
  <div class="complement"><strong><?= htmlspecialchars($publication['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong><p class="muted"><?= htmlspecialchars($publication['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> · <?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></p></div>
  <form action="publications.php?action=supprimer&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <div class="actions"><button type="submit" class="button-danger">Confirmer la suppression</button><a href="publications.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="button-secondary">Annuler</a></div>
  </form>
  <p class="muted">Pour retirer temporairement le récit, choisir le statut « Masquée » dans le formulaire de modification.</p>
</section>

<?php } elseif ($page == 'tableau-de-bord') { ?>
<div class="page-heading"><div><p class="eyebrow">Vue d’ensemble du camp</p><h1>Tableau de bord<span>.</span></h1><p>Les publications et signalements de la communauté.</p></div><a href="publications.php?action=ajouter" class="button">Nouvelle publication +</a></div>
<div class="stat-grid">
  <article class="stat-card"><p>Publications</p><strong><?= htmlspecialchars((string) (count($publications)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
  <article class="stat-card"><p>Publiées</p><strong><?= htmlspecialchars((string) ((int) ($publicationCounts['published'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
  <article class="stat-card"><p>Brouillons</p><strong><?= htmlspecialchars((string) ((int) ($publicationCounts['draft'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
  <article class="stat-card"><p>Signalements ouverts</p><strong><?= htmlspecialchars((string) (count($openReports)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></article>
</div>
<div class="dashboard-grid">
  <section class="panel">
    <div class="panel-heading"><h2>Dernières publications</h2><a href="publications.php">Tout consulter →</a></div>
    <?php foreach ($recentPublications as $publication) { ?><a href="publications.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $publication['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="recent-item"><div><strong><?= htmlspecialchars($publication['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong><small><?= htmlspecialchars($publication['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></div><span class="badge status-<?= htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($publication['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span></a><?php } ?>
    <?php if (empty($recentPublications)) { ?><p class="empty-state">Aucune publication pour le moment.</p><?php } ?>
  </section>
  <section class="panel">
    <div class="panel-heading"><h2>Signalements à examiner</h2><a href="signalements.php">Tout consulter →</a></div>
    <?php foreach ($recentReports as $report) { ?><a href="signalements.php?action=voir&amp;id=<?= htmlspecialchars((string) ((int) $report['id']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="recent-item"><div><strong><?= htmlspecialchars($report['publication_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong><small><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['reason'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></small></div><span class="badge status-<?= htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } else { echo htmlspecialchars($report['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } ?></span></a><?php } ?>
    <?php if (empty($recentReports)) { ?><p class="empty-state">Aucun signalement à traiter.</p><?php } ?>
  </section>
</div>
<section class="panel">
  <h2>Publications visibles par catégorie</h2>
  <div class="category-grid"><?php foreach ($categoryCounts as $key => $count) { ?><div><p><?= htmlspecialchars($categories[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><strong><?= htmlspecialchars((string) ((int) ($count)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></div><?php } ?></div>
</section>
<?php } ?>
  <?php } ?>
</div>
<script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/regex.js" defer></script>
<script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/communaute-validation.js" defer></script>
<?php if ($standalone) { ?>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
<?php } ?>
<?php })(); ?>
