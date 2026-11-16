<?php
if (!isset($pageTitle)) {
    header('Location: ../communaute.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="La communauté YallaQuest : partage tes petites victoires et découvre des conseils pour bouger, bien manger et souffler.">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" href="../logo.jpeg" type="image/jpeg">
  <link rel="stylesheet" href="../shared/css/common.css">
  <link rel="stylesheet" href="../shared/css/front.css">
</head>
<body class="community-page">
  <main class="page-shell">
    <section class="community-intro">
      <div class="hero-heading">
        <p class="hero-lead">Les petites victoires deviennent de belles histoires.<br>Et les belles histoires nous font avancer.</p>
        <h1 class="hero-title">Le feu de camp<span>.</span></h1>
        <p class="hero-caption">La communauté YallaQuest · Chaque habitude est une quête</p>
      </div>
      <div class="welcome-grid">
        <div class="quest-art" aria-hidden="true">
          <img src="../shared/images/mobilite.webp" alt="" class="art-card art-mobility">
          <img src="../shared/images/focus.webp" alt="" class="art-card art-focus">
          <img src="../shared/images/endurance.webp" alt="" class="art-card art-endurance">
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
            <a href="#discussions">Explorer les récits</a>
            <a href="#publier" class="dark-button">Raconter mon aventure →</a>
          </div>
        </article>
      </div>
    </section>
    <section id="discussions" class="page-section">
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
            <a href="communaute.php?categorie=<?= $key ?>&amp;recherche=<?= htmlspecialchars(urlencode($search)) ?>#discussions"
               class="filter-chip" <?php if ($selectedCategory == $key) { ?>aria-current="true"<?php } ?>><?= htmlspecialchars($label) ?></a>
          <?php } ?>
        </nav>
        <form action="communaute.php#discussions" method="get" class="search-form">
          <input type="hidden" name="categorie" value="<?= htmlspecialchars($selectedCategory) ?>">
          <label for="search-publications" class="visually-hidden">Rechercher une publication</label>
          <input id="search-publications" type="search" name="recherche"
                 value="<?= htmlspecialchars($search) ?>" placeholder="Un mot-clé…">
          <button type="submit">Rechercher</button>
        </form>
        <?php if ($search !== '' || $selectedCategory !== 'all') { ?>
          <a href="communaute.php#discussions" class="reset-search">Tout afficher</a>
        <?php } ?>
      </div>
      <div class="discussion-layout">
        <div class="publication-list">
          <?php if ($flash) { ?>
            <p role="status" class="feedback <?php if ($flash['type'] == 'error') { ?>feedback-error<?php } ?>"><?= htmlspecialchars($flash['message']) ?></p>
          <?php } ?>
          <?php foreach ($displayPublications as $publication) { $isPreview = $publication['id'] === 'preview'; ?>
            <article id="<?php if ($isPreview) { ?>publication-apercu<?php } else { ?>publication-<?= (int) $publication['id'] ?><?php } ?>" class="publication-card">
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
                    <img src="../shared/images/<?= htmlspecialchars($publication['image']) ?>.webp" alt="" loading="lazy" class="publication-image">
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
                  <form action="communaute.php#discussions" method="post" class="report-form">
                    <input type="hidden" name="action" value="report">
                    <input type="hidden" name="publication_id" value="<?= (int) $publication['id'] ?>">
                    <label for="reason-<?= (int) $publication['id'] ?>">Quel est le problème ?</label>
                    <select id="reason-<?= (int) $publication['id'] ?>" name="reason">
                      <option value="">Choisir un motif</option>
                      <?php foreach ($reportReasons as $key => $label) { ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?php if ($reportFormId == $publication['id'] && $reportFormReason == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
                      <?php } ?>
                    </select>
                    <label for="details-<?= (int) $publication['id'] ?>">Précisions (facultatif)</label>
                    <textarea id="details-<?= (int) $publication['id'] ?>" name="details" rows="3"
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
                <a href="#publier">Raconter la première aventure →</a>
              <?php } else { ?>
                <a href="communaute.php#discussions">Revenir à toutes les publications →</a>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
        <aside class="camp-sidebar">
          <section class="charter-card">
            <svg class="charter-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="../shared/icons.svg#spark"></use></svg>
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
    <section id="publier" class="page-section">
      <details class="publish-panel" <?php if ($preview) { ?>open<?php } ?>>
        <summary class="publish-summary">
          <div>
            <p class="eyebrow">Ta voix compte</p>
            <h2 class="section-title">À ton tour de raconter.</h2>
            <p>Une petite victoire, une idée ou une question. Le camp t’écoute.</p>
          </div>
          <span class="primary-button">Écrire ma publication +</span>
        </summary>
        <form action="communaute.php#publication-apercu" method="post" class="publish-form">
          <input type="hidden" name="action" value="preview">
          <div>
            <label for="publication-category">Ton univers</label>
            <select id="publication-category" name="category">
              <?php foreach ($categories as $key => $label) { if ($key !== 'all') { ?>
                <option value="<?= htmlspecialchars($key) ?>" <?php if ($formValues['category'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
              <?php } } ?>
            </select>
          </div>
          <div>
            <label for="publication-type">Tu partages…</label>
            <select id="publication-type" name="type">
              <?php foreach ($publicationTypes as $type) { ?>
                <option <?php if ($formValues['type'] == $type) { ?>selected<?php } ?>><?= htmlspecialchars($type) ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-field-wide">
            <label for="publication-title">Le titre de ton histoire</label>
            <input id="publication-title" name="title"
                   value="<?= htmlspecialchars($formValues['title']) ?>" placeholder="Aujourd’hui, j’ai fait mon premier pas…">
          </div>
          <div class="form-field-wide">
            <label for="publication-content">Raconte-nous</label>
            <textarea id="publication-content" name="content" rows="5"
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
</body>
</html>
