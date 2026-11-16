<?php
if (!isset($page)) {
    header('Location: ../../../../index.php?admin=signalements');
    exit;
}
?>
<?php if ($page == 'liste') { ?>
<div class="page-heading">
  <div><p class="eyebrow">Modération du camp</p><h1>Signalements<span>.</span></h1><p>Examiner les contenus signalés et consulter les décisions.</p></div>
</div>
<div class="stat-grid">
  <?php foreach ($reportStatuses as $key => $label) { ?>
    <article class="stat-card"><p><?= htmlspecialchars($label) ?></p><strong><?= $counts[$key] ?></strong></article>
  <?php } ?>
</div>
<section class="panel">
  <nav class="tabs" aria-label="État des signalements">
    <?php foreach (['open' => 'À examiner', 'closed' => 'Historique', 'all' => 'Tous'] as $key => $label) { ?>
      <a href="index.php?admin=signalements&amp;statut=<?= $key ?>&amp;recherche=<?= htmlspecialchars(urlencode($search)) ?><?php if ($publicationId > 0) { ?>&amp;publication=<?= $publicationId ?><?php } ?>" <?php if ($selectedStatus == $key) { ?>aria-current="page"<?php } ?>><?= $label ?></a>
    <?php } ?>
  </nav>
  <form action="index.php" method="get" class="filter-form">
    <input type="hidden" name="admin" value="signalements">
    <?php if ($publicationId) { ?><input type="hidden" name="publication" value="<?= (int) $publicationId ?>"><?php } ?>
    <div><label for="search">Rechercher</label><input id="search" type="search" name="recherche" value="<?= htmlspecialchars($search) ?>" placeholder="Publication, auteur, précisions…"></div>
    <div><label for="status">Statut</label><select id="status" name="statut">
      <?php foreach ($reportFilters as $key => $label) { ?>
        <option value="<?= $key ?>" <?php if ($selectedStatus == $key) { ?>selected<?php } ?>><?= htmlspecialchars($label) ?></option>
      <?php } ?>
    </select></div>
    <button type="submit" class="button">Filtrer</button><a href="index.php?admin=signalements" class="text-link">Réinitialiser</a>
  </form>
  <div class="table-scroll">
    <table>
      <caption><?= count($reports) ?> signalement<?php if (count($reports) > 1) { ?>s<?php } ?> affiché<?php if (count($reports) > 1) { ?>s<?php } ?></caption>
      <thead><tr><th scope="col">Publication</th><th scope="col">Motif</th><th scope="col">Statut</th><th scope="col">Reçu le</th><th scope="col">Action</th></tr></thead>
      <tbody>
        <?php foreach ($reports as $report) { ?>
          <tr>
            <td><?= htmlspecialchars($report['publication_title']) ?><small><?= htmlspecialchars($report['publication_author']) ?></small></td>
            <td><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']]); } else { echo htmlspecialchars($report['reason']); } ?></td>
            <td><span class="badge status-<?= htmlspecialchars($report['status']) ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']]); } else { echo htmlspecialchars($report['status']); } ?></span></td>
            <td><?= date('d/m/Y à H:i', strtotime($report['created_at'])) ?></td>
            <td><a href="index.php?admin=signalements&amp;action=voir&amp;id=<?= (int) $report['id'] ?>"><?php if ($report['status'] == 'pending' || $report['status'] == 'reviewing') { ?>Examiner<?php } else { ?>Consulter<?php } ?> →</a></td>
          </tr>
        <?php } ?>
        <?php if (empty($reports)) { ?><tr><td colspan="5" class="empty-state">Aucun signalement à afficher.</td></tr><?php } ?>
      </tbody>
    </table>
  </div>
</section>

<?php } elseif ($page == 'voir') { ?>
<a href="index.php?admin=signalements" class="back-link">← Signalements</a>
<div class="page-heading">
  <div><p class="eyebrow">Modération du camp</p><h1>Signalement #<?= (int) $report['id'] ?><span>.</span></h1><p>Reçu le <?= date('d/m/Y à H:i', strtotime($report['created_at'])) ?></p></div>
  <span class="badge status-<?= htmlspecialchars($report['status']) ?>"><?php if (isset($reportStatuses[$report['status']])) { echo htmlspecialchars($reportStatuses[$report['status']]); } else { echo htmlspecialchars($report['status']); } ?></span>
</div>
<div class="detail-grid">
  <div class="side-panels">
    <section class="panel">
      <h2>Motif du signalement</h2><p><strong><?php if (isset($reportReasons[$report['reason']])) { echo htmlspecialchars($reportReasons[$report['reason']]); } else { echo htmlspecialchars($report['reason']); } ?></strong></p>
      <p class="story-text"><?php if ($report['details'] != '') { echo htmlspecialchars($report['details']); } else { ?>Aucune précision ajoutée par le visiteur.<?php } ?></p>
    </section>
    <article class="panel story-panel">
      <h2>Contenu au moment du signalement</h2>
      <?php if ($publication) { ?><a href="index.php?admin=publications&amp;action=voir&amp;id=<?= (int) $publication['id'] ?>" class="text-link">Voir la publication actuelle →</a><?php } ?>
      <div class="complement">
        <p class="muted"><?= htmlspecialchars($report['publication_author']) ?></p>
        <h3><?= htmlspecialchars($report['publication_title']) ?></h3>
        <p class="story-text"><?= htmlspecialchars($report['publication_content']) ?></p>
      </div>
      <p class="muted"><?php if ($publication) { ?>Statut actuel : <?php if (isset($publicationStatuses[$publication['status']])) { echo htmlspecialchars($publicationStatuses[$publication['status']]); } else { echo htmlspecialchars($publication['status']); } ?><?php } else { ?>La publication a été supprimée.<?php } ?></p>
    </article>
  </div>
  <aside class="side-panels">
    <?php if ($isOpen) { ?>
      <section class="panel form-panel">
        <h2>Traiter le signalement</h2>
        <form action="index.php?admin=signalements&amp;action=voir&amp;id=<?= (int) $report['id'] ?>" method="post" class="form-panel">
          <div><label for="note">Note de modération</label><textarea id="note" name="note" rows="5"><?= htmlspecialchars($note) ?></textarea></div>
          <button type="submit" name="action" value="review" class="button-secondary"><?php if ($report['status'] == 'reviewing') { ?>Enregistrer la note<?php } else { ?>Prendre en charge<?php } ?></button>
          <button type="submit" name="action" value="keep" class="button">Conserver et classer</button>
          <button type="submit" name="action" value="hide" class="button-danger">Masquer la publication</button>
        </form>
      </section>
    <?php } else { ?>
      <section class="panel">
        <h2>Signalement clôturé</h2><p><?php if (isset($reportDecisions[$report['decision']])) { echo htmlspecialchars($reportDecisions[$report['decision']]); } else { ?>Décision enregistrée<?php } ?></p>
        <?php if ($report['handled_at']) { ?><p class="muted"><?= date('d/m/Y à H:i', strtotime($report['handled_at'])) ?></p><?php } ?>
        <h3>Note de modération</h3><p class="story-text"><?php if ($report['moderator_note'] != '') { echo htmlspecialchars($report['moderator_note']); } else { ?>Aucune note ajoutée.<?php } ?></p>
        <a href="index.php?admin=signalements&amp;statut=closed" class="button-secondary">Consulter l’historique</a>
      </section>
    <?php } ?>
    <section class="panel charter-panel"><h2>La charte en trois mots</h2><p>Bienveillance. Partage. Respect du rythme de chacun.</p></section>
  </aside>
</div>
<?php } ?>
