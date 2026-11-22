<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

if (isset($_GET['supprimer']) || isset($_GET['toggle']) || isset($_GET['toggle_etape'])) {
    http_response_code(405);
    header('Allow: POST');
    exit('Cette action doit être envoyée par POST.');
}

$currentPage = 'front-objectifs';
$pageTitle = 'Mes objectifs · Yalla Quest';
$niveau = (int) ($niveau ?? 1);
$xpDansNiveau = max(0, min(100, (float) ($xpDansNiveau ?? 0)));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <?php
    require_once __DIR__ . '/../includes/head.php';
  ?>
</head>
<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>
<div class="app">
<main class="canvas" id="canvas">
    <!-- En-tête -->
    <div class="title-row">
        <div>
            <p class="kicker">Objectifs</p>
            <h1>Mes objectifs</h1>
            <p class="about">« Chaque habitude est une quête. »</p>
            <p class="sub">Planifie un but comme une note pour le futur, avec une heure de début et une heure de fin.</p>
        </div>
        <div class="actions">
            <button class="btn primary" type="button" onclick="ouvrirModal()">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M8 3v10M3 8h10"/></svg>
                Nouveau but
            </button>
        </div>
    </div>

    <!-- Carte Profil Aventurier & Progression XP -->
    <section class="profile" aria-label="Profil aventurier">
        <div class="panel profile-card">
            <img src="../../img/logo.jpeg" alt="Logo" style="width:56px;height:56px;border-radius:14px;object-fit:cover;">
            <div class="who">
                <h2>Aventurier</h2>
                <div class="pills">
                    <span class="pill lilac">Niveau <?= (int) ($niveau ?? 1) ?></span>
                    <span class="pill">Félin Géométrique</span>
                </div>
                <div class="xp">
                    <div class="xp-line">
                        <div class="capbar" role="progressbar" aria-valuenow="<?= max(0, min(100, (float) ($xpDansNiveau ?? 0))) ?>" aria-valuemin="0" aria-valuemax="100">
                            <i style="width: <?= max(0, min(100, (float) ($xpDansNiveau ?? 0))) ?>%;"></i>
                        </div>
                        <strong><?= (int) ($xpTotal ?? 0) ?> XP</strong>
                    </div>
                    <small>Plus que <?= htmlspecialchars((string) ((100 - ($xpDansNiveau ?? 0))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> XP pour atteindre le niveau <?= htmlspecialchars((string) ((($niveau ?? 1) + 1)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                </div>
            </div>
        </div>
        <!-- Compteurs KPI -->
        <div class="kpis" aria-label="Compteurs">
            <div class="kpi"><div class="kpi-label">Total</div><div class="kpi-value"><?= (int) ($totalObjectifs ?? 0) ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot lilac"></span>À venir</div><div class="kpi-value"><?= (int) ($nbAVenir ?? 0) ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot gold"></span>En cours</div><div class="kpi-value"><?= (int) ($nbEnCours ?? 0) ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot sage"></span>Atteints</div><div class="kpi-value"><?= (int) ($nbAtteints ?? 0) ?></div></div>
        </div>
    </section>

    <!-- Onglets de filtrage -->
    <div class="tabs" role="tablist">
        <button type="button" role="tab" aria-selected="true" onclick="filtrerObjectifs('all', this)">Tous <span class="n"><?= (int) ($totalObjectifs ?? 0) ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('a_venir', this)">À venir <span class="n"><?= (int) ($nbAVenir ?? 0) ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('en_cours', this)">En cours <span class="n"><?= (int) ($nbEnCours ?? 0) ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('atteint', this)">Atteints <span class="n"><?= (int) ($nbAtteints ?? 0) ?></span></button>
    </div>

    <!-- Grille des cartes d'objectifs -->
    <div class="cards-grid" id="questsGrid">
        <?php if (empty($listeObjectifs)): ?>
            <div class="panel empty" style="grid-column: 1 / -1; text-align: center; padding: 48px 24px;">
                <h3>Aucun objectif pour le moment</h3>
                <p>Clique sur « Nouveau but » ci-dessus pour planifier ton premier objectif avec ses étapes !</p>
            </div>
        <?php else: ?>
            <?php foreach ($listeObjectifs as $obj): 
                $id = (int) $obj['id'];
                $cat = $obj['categorie'] ?? 'Activité physique';
                $img = $imagesPiliers[$cat] ?? 'focus';
                $isAtteint = ($obj['statut'] === 'atteint');
                $etapes = ($objectifC && method_exists($objectifC, 'recupererEtapes')) ? $objectifC->recupererEtapes($id) : [];
                $totalE = count($etapes);
                $valideesE = 0;
                foreach ($etapes as $e) { if ($e['est_validee']) $valideesE++; }
                $pourcentage = ($totalE > 0) ? round(($valideesE / $totalE) * 100) : ($isAtteint ? 100 : 0);
                
                $dateDebStr = !empty($obj['date_debut']) ? date('d M', strtotime($obj['date_debut'])) : '';
                $dateFinStr = !empty($obj['date_fin']) ? date('d M', strtotime($obj['date_fin'])) : '';
            ?>
            <article class="obj-card ivory on-ivory" data-statut="<?= htmlspecialchars($obj['statut'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <img class="art" src="../../img/piliers/<?= htmlspecialchars((string) ($img), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>.jpg" alt="" loading="lazy">
                <div class="body">
                    <!-- En-tête de la carte -->
                    <p class="meta">
                        <span class="pilier"><?= htmlspecialchars($cat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span> ·
                        du <strong><?= htmlspecialchars((string) ($dateDebStr), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> au <strong><?= htmlspecialchars((string) ($dateFinStr), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                        <span class="flag">+<?= (int) ($obj['xp_recompense'] ?? 50) ?> XP</span>
                    </p>
                    <h3 class="<?= htmlspecialchars((string) ($isAtteint ? 'done' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($obj['titre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
                    
                    <!-- Note / Description -->
                    <div class="desc">
                        <p><?= htmlspecialchars($obj['note'] ?? $obj['description'] ?? 'Aucune note.', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    </div>

                    <!-- Liste des Étapes Obligatoires & Progression -->
                    <?php if (!empty($etapes)): ?>
                    <div class="etapes-container">
                        <div class="etapes-header">
                            <span class="etapes-label">Étapes de progression</span>
                            <span class="etapes-ratio"><?= (int) ($valideesE) ?> / <?= (int) ($totalE) ?> complétée<?= $totalE > 1 ? 's' : '' ?> (<?= max(0, min(100, (float) ($pourcentage))) ?>%)</span>
                        </div>
                        <div class="etapes-progress-bar" role="progressbar" aria-valuenow="<?= max(0, min(100, (float) ($pourcentage))) ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="etapes-progress-fill" style="width: <?= max(0, min(100, (float) ($pourcentage))) ?>%;"></div>
                        </div>
                        <ul class="etapes-list">
                            <?php foreach ($etapes as $etape): 
                                $isEtapeValidee = (bool)$etape['est_validee'];
                                $checkId = 'etape_' . (int) $etape['IdEO'];
                            ?>
                            <li class="etape-row <?= htmlspecialchars((string) ($isEtapeValidee ? 'validee' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                <input class="etape-check" type="checkbox" id="<?= htmlspecialchars((string) ($checkId), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                       <?= htmlspecialchars((string) ($isEtapeValidee ? 'checked' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                       onchange="toggleEtape(<?= (int) ($etape['IdEO']) ?>, this)">
                                <label for="<?= htmlspecialchars((string) ($checkId), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="etape-title"><?= htmlspecialchars($etape['titre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                                <?php if ($isEtapeValidee && !empty($etape['date_validation'])): ?>
                                    <span class="etape-date">✓ <?= date('d M', strtotime($etape['date_validation'])) ?></span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Pied de carte : Statut & Actions -->
                    <div class="pfoot">
                        <p class="times">
                            <span>Début : <strong><?= date('d M à H:i', strtotime($obj['date_debut'])) ?></strong></span>
                            <span>Fin : <strong><?= date('d M à H:i', strtotime($obj['date_fin'])) ?></strong></span>
                        </p>
                        <span class="pill st-<?= htmlspecialchars((string) ($obj['statut']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) ($isAtteint ? 'Accompli' : ($obj['statut'] === 'a_venir' ? 'À venir' : 'En cours')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </span>
                        <div class="actions">
                            <?php if ($isAtteint): ?>
                                <form method="post" action="front-objectifs.php" style="display:contents">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><input type="hidden" name="toggle" value="<?= (int) $id ?>"><button class="btn sm" type="submit">Reprendre</button></form>
                            <?php else: ?>
                                <form method="post" action="front-objectifs.php" style="display:contents">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><input type="hidden" name="toggle" value="<?= (int) $id ?>"><button class="btn primary sm" type="submit">Accomplir</button></form>
                            <?php endif; ?>
                            <form method="post" action="front-objectifs.php" style="display:contents" onsubmit="return confirm('Voulez-vous vraiment supprimer cet objectif ?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><input type="hidden" name="supprimer" value="<?= (int) $id ?>"><button class="btn danger sm icon" type="submit" title="Supprimer">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:14px;height:14px;"><path d="M3 4.5h10M6.5 4.5V3h3v1.5M4.5 4.5l.6 8.5h5.8l.6-8.5"/></svg>
                            </button></form>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- Boîte de dialogue : Formulaire d'ajout d'objectif -->
<dialog class="dlg" id="dlgBut" aria-labelledby="dlgButTitle">
    <form method="POST" action="front-objectifs.php" onsubmit="return validerFormulaireObjectif()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="ajouter_objectif" value="1">
        
        <div class="head">
            <h2 id="dlgButTitle">Nouveau but pour le futur</h2>
            <button class="btn icon" type="button" onclick="fermerModal()" aria-label="Fermer">✕</button>
        </div>

        <div class="body">
            <!-- Champ Titre -->
            <div class="field">
                <label for="butTitre">Intitulé du but <span style="color:#d9534f">*</span></label>
                <input class="input" type="text" id="butTitre" name="titre" placeholder="Ex. Méditer 10 minutes chaque matin" required>
                <p class="field-error" id="butTitreErr" style="color:#d9534f;font-size:13px;"></p>
            </div>

            <!-- Champ Note -->
            <div class="field">
                <label for="butNote">Note ou motivation</label>
                <textarea class="input" id="butNote" name="note" rows="2" placeholder="Pourquoi ce but compte pour toi ?"></textarea>
            </div>

            <!-- Pilier de santé / Catégorie -->
            <div class="field">
                <label for="butCat">Pilier bien-être</label>
                <select class="input" id="butCat" name="categorie">
                    <option value="Activité physique">Activité physique</option>
                    <option value="Nutrition & Santé">Nutrition & Santé</option>
                    <option value="Santé mentale">Santé mentale</option>
                    <option value="Sommeil & Récupération">Sommeil & Récupération</option>
                    <option value="Habitudes de vie">Habitudes de vie</option>
                </select>
            </div>

            <!-- Période : Début & Fin obligatoires -->
            <div class="field-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label for="butDateDebut">Date & heure de début <span style="color:#d9534f">*</span></label>
                    <input class="input" type="datetime-local" id="butDateDebut" name="date_debut" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="field">
                    <label for="butDateFin">Date & heure de fin <span style="color:#d9534f">*</span></label>
                    <input class="input" type="datetime-local" id="butDateFin" name="date_fin" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>" required>
                </div>
            </div>
            <p class="field-error" id="butDateErr" style="color:#d9534f;font-size:13px;"></p>

            <!-- Section des Étapes Obligatoires (Composition) -->
            <div class="field etapes-form-section" style="margin-top:16px;">
                <label>Étapes obligatoires pour accomplir ce but <span style="color:#d9534f">*</span></label>
                <p class="field-help" style="font-size:13px;color:#aaa;margin-bottom:8px;">Chaque but doit être découpé en au moins une étape concrète.</p>
                
                <div class="add-etape-row" style="display:flex;gap:8px;margin-bottom:8px;">
                    <input class="input" type="text" id="nouvelleEtapeInput" placeholder="Ex. Acheter un carnet de méditation..." style="flex:1;">
                    <button type="button" class="btn primary sm" onclick="ajouterChampEtape()">+ Ajouter</button>
                </div>
                <p class="field-error" id="etapeErr" style="color:#d9534f;font-size:13px;"></p>

                <!-- Liste des étapes ajoutées -->
                <div id="listeChampsEtapes" style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;">
                </div>
            </div>
        </div>

        <div class="foot">
            <button class="btn" type="button" onclick="fermerModal()">Annuler</button>
            <button class="btn primary" type="submit">Enregistrer l'objectif</button>
        </div>
    </form>
</dialog>

<?php require_once __DIR__ . '/../includes/scripts.php'; ?>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
