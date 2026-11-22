<?php
$currentPage = 'back-objectifs';
$pageTitle = 'Objectifs · Administration · Yalla Quest';
?>
<?php
require_once __DIR__ . '/../partials/head.php';
?>
<div class="app">
<main class="canvas" id="canvas">
    <!-- En-tête Back-Office -->
    <div class="title-row">
        <div>
            <p class="kicker">Administration · objectifs</p>
            <h1>Objectifs</h1>
            <p class="about">Les buts planifiés pour le futur, leurs notes et leurs périodes, pour chaque aventurier.</p>
            <p class="sub"><?= $totalQuests ?? 0 ?> objectif<?= ($totalQuests ?? 0) > 1 ? 's' : '' ?> enregistré<?= ($totalQuests ?? 0) > 1 ? 's' : '' ?></p>
        </div>
        <div class="actions">
            <button class="btn primary" type="button" onclick="document.getElementById('dlgQuest').showModal();">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M8 3v10M3 8h10"/></svg>
                Assigner un but
            </button>
        </div>
    </div>

    <!-- Barre de recherche et filtre -->
    <div class="filters" role="search">
        <div class="search">
            <input class="input" type="search" id="questSearch" placeholder="Rechercher par but ou note..." oninput="filtrerTableau()">
        </div>
        <select class="input" id="questStatutFilter" onchange="filtrerTableau()">
            <option value="all">Tous les états</option>
            <option value="en_cours">En cours</option>
            <option value="a_venir">À venir</option>
            <option value="atteint">Atteints</option>
        </select>
    </div>

    <!-- Tableau de supervision -->
    <div class="sheet">
        <div class="table-wrap">
            <table class="respond admin-table" id="adminTable">
                <thead>
                    <tr>
                        <th>But & Note</th>
                        <th>Pilier</th>
                        <th>Période</th>
                        <th>Étapes</th>
                        <th>État</th>
                        <th>Gain</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listeObjectifs)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:24px;">Aucun objectif trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listeObjectifs as $obj): 
                            $id = $obj['id'];
                            $etapes = ($objectifC && method_exists($objectifC, 'recupererEtapes')) ? $objectifC->recupererEtapes($id) : [];
                            $totalE = count($etapes);
                            $valideesE = 0;
                            foreach ($etapes as $e) { if ($e['est_validee']) $valideesE++; }
                            $pct = ($totalE > 0) ? round(($valideesE / $totalE) * 100) : (($obj['statut'] === 'atteint') ? 100 : 0);
                            $statut = $obj['statut'];
                            $statutLibelle = ($statut === 'atteint') ? 'Accompli' : (($statut === 'a_venir') ? 'À venir' : 'En cours');
                        ?>
                        <tr data-statut="<?= htmlspecialchars($statut) ?>">
                            <td>
                                <strong><?= htmlspecialchars($obj['titre']) ?></strong>
                                <?php if (!empty($obj['note']) || !empty($obj['description'])): ?>
                                    <div style="font-size:12px;color:#aaa;margin-top:2px;">
                                        📝 <?= htmlspecialchars($obj['note'] ?? $obj['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="pill" style="font-size:11px;"><?= htmlspecialchars($obj['categorie'] ?? 'Activité physique') ?></span></td>
                            <td>
                                <span style="font-size:12px;">
                                    <?= date('d M', strtotime($obj['date_debut'])) ?> → <?= date('d M', strtotime($obj['date_fin'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="pill" style="font-size:11px;"><?= $valideesE ?> / <?= $totalE ?> (<?= $pct ?>%)</span>
                            </td>
                            <td>
                                <span class="pill st-<?= $statut ?>"><?= $statutLibelle ?></span>
                            </td>
                            <td><strong>+<?= $obj['xp_recompense'] ?? 50 ?> XP</strong></td>
                            <td style="text-align:right;">
                                <a class="btn danger sm" href="back-objectifs.php?supprimer=<?= $id ?>" onclick="return confirm('Confirmer la suppression de cet objectif ?');">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>

<!-- Modal d'assignation d'objectif par l'Admin -->
<dialog id="dlgQuest" class="dlg">
    <form method="POST" action="back-objectifs.php" onsubmit="return validerAdminObjectif()">
        <input type="hidden" name="ajouter_objectif_admin" value="1">
        <div class="head">
            <h2>Assigner un objectif</h2>
            <button class="btn icon" type="button" onclick="document.getElementById('dlgQuest').close()">✕</button>
        </div>
        <div class="body">
            <div class="field">
                <label for="adminTitre">Intitulé du but *</label>
                <input class="input" type="text" id="adminTitre" name="titre" placeholder="Ex. Atelier nutrition" required>
            </div>
            <div class="field">
                <label for="adminCat">Pilier bien-être</label>
                <select class="input" id="adminCat" name="categorie">
                    <option value="Activité physique">Activité physique</option>
                    <option value="Nutrition & Santé">Nutrition & Santé</option>
                    <option value="Santé mentale">Santé mentale</option>
                    <option value="Sommeil & Récupération">Sommeil & Récupération</option>
                    <option value="Habitudes de vie">Habitudes de vie</option>
                </select>
            </div>
            <div class="field">
                <label for="adminNote">Note ou consigne pour l'aventurier</label>
                <textarea class="input" id="adminNote" name="note" rows="2" placeholder="Note facultative..."></textarea>
            </div>
            <div class="field-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label for="adminDebut">Date & heure début *</label>
                    <input class="input" type="datetime-local" id="adminDebut" name="date_debut" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="field">
                    <label for="adminFin">Date & heure fin *</label>
                    <input class="input" type="datetime-local" id="adminFin" name="date_fin" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>" required>
                </div>
            </div>
            <div class="field" style="margin-top:12px;">
                <label>Étapes obligatoires</label>
                <div style="display:flex;gap:8px;">
                    <input class="input" type="text" id="adminEtapeTxt" placeholder="Libellé de l'étape...">
                    <button class="btn primary sm" type="button" onclick="ajouterEtapeAdmin()">+ Ajouter</button>
                </div>
                <div id="adminListeEtapes" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;"></div>
            </div>
        </div>
        <div class="foot">
            <button class="btn" type="button" onclick="document.getElementById('dlgQuest').close()">Annuler</button>
            <button class="btn primary" type="submit">Enregistrer l'objectif</button>
        </div>
    </form>
</dialog>

<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
