<?php
if (!isset($page)) {
    header('Location: ../index.php');
    exit;
}
?>
<?php if ($page == 'liste') { ?>
<div class="page-heading">
  <div><p class="eyebrow">Les récits du camp</p><h1>Publications<span>.</span></h1><p>Consulter et gérer les publications de la communauté.</p></div>
  <a href="index.php?action=ajouter" class="button">Nouvelle publication +</a>
</div>
<div class="stat-grid">
  <article class="stat-card"><p>Total</p><strong><?= count($allPublications) ?></strong></article>
  <?php foreach ($publicationStatuses as $key => $label) { ?>
    <article class="stat-card"><p><?= htmlspecialchars($label) ?></p><strong><?= $counts[$key] ?></strong></article>
  <?php } ?>
</div>
<section class="panel">
  <form action="index.php" method="get" class="filter-form">
    <div><label for="search">Rechercher</label><input id="search" type="search" name="recherche" placeholder="Titre, auteur, contenu…" value="<?= htmlspecialchars($search) ?>"></div>
    <div><label for="status">Statut</label><select id="status" name="statut">
      <option value="all">Tous les statuts</option>
      <?php foreach ($publicationStatuses as $key => $label) { ?><option value="<?= $key ?>" <?php if ($selectedStatus == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option><?php } ?>
    </select></div>
    <div><label for="category">Catégorie</label><select id="category" name="categorie">
      <?php foreach ($categories as $key => $label) { ?><option value="<?= $key ?>" <?php if ($selectedCategory == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option><?php } ?>
    </select></div>
    <button type="submit" class="button">Filtrer</button>
    <a href="index.php" class="text-link">Réinitialiser</a>
  </form>
  <div class="table-scroll">
    <table>
      <caption><?= count($publications) ?> publication<?php if (count($publications) > 1) { ?>s<?php } ?> affichée<?php if (count($publications) > 1) { ?>s<?php } ?></caption>
      <thead><tr><th scope="col">Publication</th><th scope="col">Auteur</th><th scope="col">Catégorie</th><th scope="col">Statut</th><th scope="col">Signalements</th><th scope="col">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($publications as $publication) { ?>
          <tr>
            <td><a href="index.php?action=voir&amp;id=<?= (int) $publication['id'] ?>"><?= htmlspecialchars($publication['title']) ?></a><small><?= htmlspecialchars($publication['type']) ?></small></td>
            <td><?= htmlspecialchars($publication['author']) ?></td>
            <td><?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']]); } else { echo htmlspecialchars($publication['category']); } ?></td>
            <td><span class="badge status-<?= htmlspecialchars($publication['status']) ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']]); } else { echo htmlspecialchars($publication['status']); } ?></span></td>
            <td><?= (int) $publication['report_count'] ?></td>
            <td class="table-actions"><a href="index.php?action=voir&amp;id=<?= (int) $publication['id'] ?>">Consulter</a><a href="index.php?action=modifier&amp;id=<?= (int) $publication['id'] ?>">Modifier</a><a href="index.php?action=supprimer&amp;id=<?= (int) $publication['id'] ?>" class="danger-link">Supprimer</a></td>
          </tr>
        <?php } ?>
        <?php if (empty($publications)) { ?><tr><td colspan="6" class="empty-state">Aucune publication à afficher.</td></tr><?php } ?>
      </tbody>
    </table>
  </div>
</section>

<?php } elseif ($page == 'voir') { ?>
<a href="index.php" class="back-link">← Publications</a>
<div class="page-heading">
  <div><p class="eyebrow">Publication #<?= (int) $publication['id'] ?></p><h1>Détail de la publication<span>.</span></h1></div>
  <div class="actions"><a href="index.php?action=modifier&amp;id=<?= (int) $publication['id'] ?>" class="button">Modifier</a><a href="index.php?action=supprimer&amp;id=<?= (int) $publication['id'] ?>" class="button-secondary">Supprimer</a></div>
</div>
<div class="detail-grid">
  <article class="panel story-panel">
    <div class="publication-meta"><div><strong><?= htmlspecialchars($publication['author']) ?></strong><small><?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']]); } else { echo htmlspecialchars($publication['category']); } ?></small></div><span class="badge status-<?= htmlspecialchars($publication['status']) ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']]); } else { echo htmlspecialchars($publication['status']); } ?></span></div>
    <p class="eyebrow"><?= htmlspecialchars($publication['type']) ?></p>
    <h2><?= htmlspecialchars($publication['title']) ?></h2>
    <p class="story-text"><?= htmlspecialchars($publication['content']) ?></p>
    <?php if ($publication['more'] !== '') { ?><section class="complement"><h3>Complément</h3><p class="story-text"><?= htmlspecialchars($publication['more']) ?></p></section><?php } ?>
    <?php if ($publication['image'] && isset($publicationImages[$publication['image']])) { ?><img src="../shared/images/<?= htmlspecialchars($publication['image']) ?>.webp" alt="Illustration de la publication" class="story-image"><?php } ?>
  </article>
  <aside class="side-panels">
    <section class="panel">
      <h2>Informations</h2>
      <dl class="metadata">
        <div><dt>Créée le</dt><dd><?= date('d/m/Y à H:i', strtotime($publication['created_at'])) ?></dd></div>
        <div><dt>Dernière modification</dt><dd><?= date('d/m/Y à H:i', strtotime($publication['updated_at'])) ?></dd></div>
        <div><dt>Signalements reçus</dt><dd><?= count($relatedReports) ?></dd></div>
      </dl>
      <?php if ($publication['status'] === 'published') { ?><a href="../Front-Office/communaute.php#publication-<?= (int) $publication['id'] ?>" class="button-secondary">Voir sur le site ↗</a><?php } ?>
    </section>
    <section class="panel">
      <h2>Signalements liés</h2>
      <?php foreach ($relatedReports as $report) { ?>
        <a href="index.php?module=signalements&amp;action=voir&amp;id=<?= (int) $report['id'] ?>" class="report-link"><span class="badge status-<?= htmlspecialchars($report['status']) ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']]); } else { echo htmlspecialchars($report['status']); } ?></span><p><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']]); } else { echo htmlspecialchars($report['reason']); } ?></p></a>
      <?php } ?>
      <?php if (empty($relatedReports)) { ?><p class="muted">Aucun signalement pour ce récit.</p><?php } ?>
    </section>
  </aside>
</div>

<?php } elseif ($page == 'ajouter' || $page == 'modifier') { ?>
<a href="index.php" class="back-link">← Publications</a>
<div class="page-heading"><div><h1><?= htmlspecialchars($title) ?><span>.</span></h1><p>Un récit clair, une voix et un univers.</p></div></div>
<form action="<?= htmlspecialchars($formAction) ?>" method="post" class="detail-grid">
  <section class="panel form-panel" aria-label="Contenu de la publication">
    <div><label for="title">Titre</label><input id="title" name="title" value="<?= htmlspecialchars($data['title']) ?>" placeholder="Le titre de l’histoire"></div>
    <div class="form-columns">
      <div><label for="author">Auteur</label><input id="author" name="author" value="<?= htmlspecialchars($data['author']) ?>"></div>
      <div><label for="category">Catégorie</label><select id="category" name="category">
        <?php foreach ($categories as $key => $label) { if ($key !== 'all') { ?>
          <option value="<?= $key ?>" <?php if ($data['category'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
        <?php } } ?>
      </select></div>
    </div>
    <div><label for="type">Type de publication</label><select id="type" name="type">
      <?php foreach ($publicationTypes as $type) { ?><option <?php if ($data['type'] == $type) { ?>selected<?php } ?>><?= htmlspecialchars($type) ?></option><?php } ?>
    </select></div>
    <div><label for="content">Message</label><textarea id="content" name="content" rows="9" placeholder="Raconter une petite victoire, partager un conseil…"><?= htmlspecialchars($data['content']) ?></textarea></div>
    <details class="complement" <?php if ($data['more'] != '') { ?>open<?php } ?>>
      <summary>Ajouter un complément (facultatif)</summary>
      <label for="more">Texte derrière « Lire la suite »</label><textarea id="more" name="more" rows="4"><?= htmlspecialchars($data['more']) ?></textarea>
    </details>
  </section>
  <aside class="panel form-panel" aria-label="Options de publication">
    <h2>Visibilité</h2>
    <div><label for="status">Statut</label><select id="status" name="status">
      <?php foreach ($publicationStatuses as $key => $label) { ?><option value="<?= $key ?>" <?php if ($data['status'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option><?php } ?>
    </select></div>
    <p class="muted">Publiée : visible dans la communauté.<br>Brouillon : conservée dans l’administration.<br>Masquée : retirée du fil.</p>
    <div><label for="image">Illustration</label><select id="image" name="image">
      <?php foreach ($publicationImages as $key => $label) { ?><option value="<?= $key ?>" <?php if ($data['image'] == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option><?php } ?>
    </select></div>
    <button type="submit" class="button"><?= htmlspecialchars($submitLabel) ?></button>
    <a href="index.php" class="button-secondary">Annuler</a>
  </aside>
</form>

<?php } elseif ($page == 'supprimer') { ?>
<a href="index.php?action=voir&amp;id=<?= (int) $publication['id'] ?>" class="back-link">← Retour à la publication</a>
<section class="panel delete-panel">
  <h1>Supprimer cette publication ?</h1>
  <p>Elle sera supprimée définitivement du site. Les signalements reçus resteront consultables dans l’historique, avec une copie du contenu signalé.</p>
  <div class="complement"><strong><?= htmlspecialchars($publication['title']) ?></strong><p class="muted"><?= htmlspecialchars($publication['author']) ?> · <?php if (isset($categories[$publication['category']])) { echo htmlspecialchars($categories[$publication['category']]); } else { echo htmlspecialchars($publication['category']); } ?></p></div>
  <form action="index.php?action=supprimer&amp;id=<?= (int) $publication['id'] ?>" method="post">
    <div class="actions"><button type="submit" class="button-danger">Confirmer la suppression</button><a href="index.php?action=voir&amp;id=<?= (int) $publication['id'] ?>" class="button-secondary">Annuler</a></div>
  </form>
  <p class="muted">Pour retirer temporairement le récit, choisir le statut « Masquée » dans le formulaire de modification.</p>
</section>

<?php } elseif ($page == 'tableau-de-bord') { ?>
<div class="page-heading"><div><p class="eyebrow">Vue d’ensemble du camp</p><h1>Tableau de bord<span>.</span></h1><p>Les publications et signalements de la communauté.</p></div><a href="index.php?action=ajouter" class="button">Nouvelle publication +</a></div>
<div class="stat-grid">
  <article class="stat-card"><p>Publications</p><strong><?= count($publications) ?></strong></article>
  <article class="stat-card"><p>Publiées</p><strong><?= $publicationCounts['published'] ?></strong></article>
  <article class="stat-card"><p>Brouillons</p><strong><?= $publicationCounts['draft'] ?></strong></article>
  <article class="stat-card"><p>Signalements ouverts</p><strong><?= count($openReports) ?></strong></article>
</div>
<div class="dashboard-grid">
  <section class="panel">
    <div class="panel-heading"><h2>Dernières publications</h2><a href="index.php">Tout consulter →</a></div>
    <?php foreach ($recentPublications as $publication) { ?><a href="index.php?action=voir&amp;id=<?= (int) $publication['id'] ?>" class="recent-item"><div><strong><?= htmlspecialchars($publication['title']) ?></strong><small><?= htmlspecialchars($publication['author']) ?></small></div><span class="badge status-<?= htmlspecialchars($publication['status']) ?>"><?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']]); } else { echo htmlspecialchars($publication['status']); } ?></span></a><?php } ?>
    <?php if (empty($recentPublications)) { ?><p class="empty-state">Aucune publication pour le moment.</p><?php } ?>
  </section>
  <section class="panel">
    <div class="panel-heading"><h2>Signalements à examiner</h2><a href="index.php?module=signalements">Tout consulter →</a></div>
    <?php foreach ($recentReports as $report) { ?><a href="index.php?module=signalements&amp;action=voir&amp;id=<?= (int) $report['id'] ?>" class="recent-item"><div><strong><?= htmlspecialchars($report['publication_title']) ?></strong><small><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']]); } else { echo htmlspecialchars($report['reason']); } ?></small></div><span class="badge status-<?= htmlspecialchars($report['status']) ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']]); } else { echo htmlspecialchars($report['status']); } ?></span></a><?php } ?>
    <?php if (empty($recentReports)) { ?><p class="empty-state">Aucun signalement à traiter.</p><?php } ?>
  </section>
</div>
<section class="panel">
  <h2>Publications visibles par catégorie</h2>
  <div class="category-grid"><?php foreach ($categoryCounts as $key => $count) { ?><div><p><?= htmlspecialchars($categories[$key]) ?></p><strong><?= $count ?></strong></div><?php } ?></div>
</section>
<?php } ?>
