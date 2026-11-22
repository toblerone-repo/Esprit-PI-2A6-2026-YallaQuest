<?php
(function () {
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
    $pageTitle = 'Communauté — YallaQuest';
    $unavailable = false;
    $selectedCategory = $_GET['categorie'] ?? 'all';
    $search = $_GET['recherche'] ?? '';
    $filteredPublications = [];
    foreach ($publicationsDemo as $publication) {
        $text = $publication['title'] . ' ' . $publication['content'] . ' ' . $publication['author'];
        if ($publication['status'] === 'published'
            && ($selectedCategory === 'all' || $publication['category'] === $selectedCategory)
            && ($search === '' || mb_stripos($text, $search, 0, 'UTF-8') !== false)) {
            $filteredPublications[] = $publication;
        }
    }
    $publicationCount = count($filteredPublications);
    $displayPublications = $filteredPublications;
    $preview = null;
    $flash = null;
    $reportFormId = 0;
    $reportFormReason = '';
    $reportFormDetails = '';
    $formValues = ['title' => '', 'content' => '', 'category' => 'nutrition', 'type' => 'Petite victoire'];
    $standalone = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)
        && realpath(get_included_files()[0] ?? '') === realpath(__FILE__);
?>
<?php if ($standalone) { ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
<?php } ?>
<link rel="stylesheet" href="../../css/communaute-base.css">
<link rel="stylesheet" href="../../css/communaute.css">
<?php if ($standalone) { ?>
</head>
<body class="yq-community-page">
<?php } ?>
<div class="yq-community yq-community-front">
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
  <?php if ($unavailable) { ?>
  <main class="page-shell page-section">
    <h1 class="section-title">La communauté est momentanément indisponible.</h1>
    <p>Les publications n’ont pas pu être chargées. Réessaie dans quelques instants.</p>
    <a href="communaute.php" class="primary-button">Réessayer</a>
  </main>
  <?php } else { ?>
  <main class="page-shell">
    <section class="community-intro">
      <div class="hero-heading">
       
        <h1 class="hero-title">Le feu de camp<span>.</span></h1>
        <p class="hero-caption">La communauté YallaQuest · Chaque habitude est une quête</p>
      </div>
      <div class="welcome-grid">
        <div class="quest-art" aria-hidden="true">
          <img src="../../img/communaute-mobilite.webp" alt="" class="art-card art-mobility">
          <img src="../../img/communaute-focus.webp" alt="" class="art-card art-focus">
          <img src="../../img/communaute-endurance.webp" alt="" class="art-card art-endurance">
          <span class="art-caption">Chacun son chemin. Ensemble, l’aventure.</span>
        </div>
        <article class="welcome-card">
          <div class="welcome-header">
            <span>Bienvenue autour du feu</span>
            <span class="welcome-badge">Communauté</span>
          </div>
          <h2>Un petit pas pour toi.<br>Une étincelle pour<br>quelqu’un d’autre.</h2>
          <p>Une recette réussie, un nouveau chemin, un moment de calme. Partage ce qui t’a fait du bien. Ton aventure peut en inspirer une autre.</p>
          <div class="welcome-actions">
            <a href="#community-discussions">Explorer les récits</a>
            <a href="#community-publier" class="dark-button">Raconter mon aventure →</a>
          </div>
        </article>
      </div>
    </section>
    <section id="community-discussions" class="page-section">
      <div class="section-header">
        <div>
          <p class="eyebrow">Les voix du camp</p>
          <h2 class="section-title">On a tous quelque chose à partager.</h2>
        </div>
        <span class="publication-count"><?= $publicationCount ?> publication<?php if ($publicationCount > 1) { ?>s<?php } ?></span>
      </div>
      <div class="discussion-tools">
        <nav aria-label="Filtrer les publications" class="category-filters">
          <?php foreach ($categories as $key => $label) { ?>
            <a href="communaute.php?categorie=<?= $key ?>&amp;recherche=<?= htmlspecialchars(urlencode($search)) ?>#community-discussions"
               class="filter-chip" <?php if ($selectedCategory == $key) { ?>aria-current="true"<?php } ?>><?= htmlspecialchars($label) ?></a>
          <?php } ?>
        </nav>
        <form action="communaute.php#community-discussions" method="get" class="search-form" novalidate>
          <input type="hidden" name="categorie" value="<?= htmlspecialchars($selectedCategory) ?>">
          <label for="search-publications" class="visually-hidden">Rechercher une publication</label>
          <input id="search-publications" type="search" name="recherche"
                 value="<?= htmlspecialchars($search) ?>" placeholder="Un mot-clé…">
          <button type="submit">Rechercher</button>
        </form>
        <?php if ($search !== '' || $selectedCategory !== 'all') { ?>
          <a href="communaute.php#community-discussions" class="reset-search">Tout afficher</a>
        <?php } ?>
      </div>
      <div class="discussion-layout">
        <div class="publication-list">
          <?php if ($flash) { ?>
            <p role="status" class="feedback <?php if ($flash['type'] == 'error') { ?>feedback-error<?php } ?>"><?= htmlspecialchars($flash['message']) ?></p>
          <?php } ?>
          <?php foreach ($displayPublications as $publication) { $isPreview = $publication['id'] === 'preview'; ?>
            <article id="<?php if ($isPreview) { ?>community-preview<?php } else { ?>community-publication-<?= (int) $publication['id'] ?><?php } ?>" class="publication-card">
              <?php if ($isPreview) { ?><p class="preview-label">Aperçu de ta publication</p><?php } ?>
              <div class="publication-body">
                <div class="publication-header">
                  <div class="publication-author">
                    <span class="publication-avatar"><?= htmlspecialchars($publication['initials']) ?></span>
                    <div>
                      <p class="author-name"><?= htmlspecialchars($publication['author']) ?></p>
                      <p class="author-context"><?php if ($isPreview) { ?>Aperçu de ta publication<?php } else { ?>Autour du feu de camp<?php } ?></p>
                    </div>
                  </div>
                  <span class="publication-category category--<?= htmlspecialchars($publication['category']) ?>"><?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']]); } else { echo htmlspecialchars($publication['category']); } ?></span>
                </div>
                <div class="publication-story <?php if ($publication['image']) { ?>has-image<?php } ?>">
                  <div>
                    <p class="publication-type"><?= htmlspecialchars($publication['type']) ?></p>
                    <h3 class="publication-title"><?= htmlspecialchars($publication['title']) ?></h3>
                    <p class="publication-text"><?= htmlspecialchars($publication['content']) ?></p>
                  </div>
                  <?php if ($publication['image'] && isset($publicationImages[$publication['image']])) { ?>
                    <img src="../../img/communaute-<?= htmlspecialchars($publication['image']) ?>.webp" alt="" loading="lazy" class="publication-image">
                  <?php } ?>
                </div>
                <?php if ($publication['more'] !== '') { ?>
                  <details class="publication-more">
                    <summary>Lire la suite →</summary>
                    <p class="publication-text"><?= htmlspecialchars($publication['more']) ?></p>
                  </details>
                <?php } ?>
              </div>
              <?php if (!$isPreview) { ?>
                <details class="publication-report" <?php if ($reportFormId == $publication['id']) { ?>open<?php } ?>>
                  <summary>Signaler ce contenu</summary>
                  <form id="community-report-form-<?= (int) $publication['id'] ?>" action="communaute.php#community-discussions" method="post" class="community-report-form" novalidate>
                    <input type="hidden" name="action" value="report">
                    <input type="hidden" name="publication_id" value="<?= (int) $publication['id'] ?>">
                    <label for="community-reason-<?= (int) $publication['id'] ?>">Quel est le problème ?</label>
                    <select id="community-reason-<?= (int) $publication['id'] ?>" name="reason">
                      <option value="">Choisir un motif</option>
                      <?php foreach ($reportReasons as $key => $label) { ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?php if ($reportFormId == $publication['id'] && $reportFormReason == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
                      <?php } ?>
                    </select>
                    <label for="community-details-<?= (int) $publication['id'] ?>">Précisions (facultatif)</label>
                    <textarea id="community-details-<?= (int) $publication['id'] ?>" name="details" rows="3"
                              placeholder="Explique ce qui pose problème…"><?php if ($reportFormId == $publication['id']) { echo htmlspecialchars($reportFormDetails); } ?></textarea>
                    <p class="report-help">Ton signalement est transmis à l’équipe de modération.</p>
                    <button type="submit" class="primary-button">Envoyer le signalement →</button>
                  </form>
                </details>
              <?php } ?>
            </article>
          <?php } ?>
          <?php if (empty($filteredPublications)) { ?>
            <div class="empty-publications">
              <p><?php if ($search == '') { ?>Aucun récit n’a encore été partagé.<?php } else { ?>Aucun récit ne correspond à ta recherche.<?php } ?></p>
              <?php if ($search == '') { ?>
                <a href="#community-publier">Raconter la première aventure →</a>
              <?php } else { ?>
                <a href="communaute.php#community-discussions">Revenir à toutes les publications →</a>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
        <aside class="camp-sidebar">
          <section class="charter-card">
            <svg class="charter-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="../../img/communaute-icons.svg#spark"></use></svg>
            <h3>Un camp où<br>chacun a sa place.</h3>
            <p>On s’encourage. On partage ce qu’on a vécu. On avance sans se comparer.</p>
            <ul>
              <li>La bienveillance avant tout.</li>
              <li>Des conseils simples et sourcés.</li>
              <li>Le respect du rythme de chacun.</li>
            </ul>
          </section>
        </aside>
      </div>
    </section>
    <section id="community-publier" class="page-section">
      <details class="publish-panel" <?php if ($preview) { ?>open<?php } ?>>
        <summary class="publish-summary">
          <div>
            <p class="eyebrow">Ta voix compte</p>
            <h2 class="section-title">À ton tour de raconter.</h2>
            <p>Une petite victoire, une idée ou une question. Le camp t’écoute.</p>
          </div>
          <span class="primary-button">Écrire ma publication +</span>
        </summary>
        <form id="community-publication-form" action="communaute.php#community-preview" method="post" class="publish-form" novalidate>
          <input type="hidden" name="action" value="preview">
          <div>
            <label for="community-category">Ton univers</label>
            <select id="community-category" name="category">
              <?php foreach ($categories as $key => $label) { if ($key !== 'all') { ?>
                <option value="<?= htmlspecialchars($key) ?>" <?php if ($formValues['category'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
              <?php } } ?>
            </select>
          </div>
          <div>
            <label for="community-type">Tu partages…</label>
            <select id="community-type" name="type">
              <?php foreach ($publicationTypes as $type) { ?>
                <option <?php if ($formValues['type'] == $type) { ?>selected<?php } ?>><?= htmlspecialchars($type) ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-field-wide">
            <label for="community-title">Le titre de ton histoire</label>
            <input id="community-title" name="title"
                   value="<?= htmlspecialchars($formValues['title']) ?>" placeholder="Aujourd’hui, j’ai fait mon premier pas…">
          </div>
          <div class="form-field-wide">
            <label for="community-content">Raconte-nous</label>
            <textarea id="community-content" name="content" rows="5"
                      placeholder="Qu’est-ce qui t’a fait du bien ?"><?= htmlspecialchars($formValues['content']) ?></textarea>
          </div>
          <div class="form-actions form-field-wide">
            <p>Les mots simples font souvent les plus belles histoires.</p>
            <button type="submit" class="primary-button">Prévisualiser →</button>
          </div>
        </form>
      </details>
    </section>
  </main>
  <?php } ?>
</div>
<script src="../../js/communaute-validation.js" defer></script>
<?php if ($standalone) { ?>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php } ?>
<?php })(); ?>
