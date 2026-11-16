<?php
$currentPage = 'objectifs';
$pageTitle = 'Mes objectifs · Yalla Quest';

if (!defined('YQ_ROOT')) {
    header('Location: ../../../index.php?page=objectifs');
    exit;
}
require_once YQ_FRONT . '/Controllers/ObjectifC.php';

$objectifC = new ObjectifC();

// 1. Suppression d'un objectif
if (isset($_GET['supprimer'])) {
    $idSuppr = intval($_GET['supprimer']);
    $objectifC->supprimerObjectif($idSuppr);
    header('Location: index.php?page=objectifs');
    exit;
}

// 2. Basculement manuel de statut (Accomplir / Reprendre)
if (isset($_GET['toggle'])) {
    $idToggle = intval($_GET['toggle']);
    $objectifC->basculerStatut($idToggle);
    header('Location: index.php?page=objectifs');
    exit;
}

// 3. Validation / Invalidation d'une étape
if (isset($_GET['toggle_etape'])) {
    $idEO = intval($_GET['toggle_etape']);
    $val = isset($_GET['val']) ? intval($_GET['val']) : 1;
    $objectifC->validerEtape($idEO, $val === 1);
    header('Location: index.php?page=objectifs');
    exit;
}

// 4. Ajout d'un nouvel objectif via formulaire POST
$messageErreur = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ajouter_objectif'])) {
    $titre = trim($_POST['titre'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $dateDebut = $_POST['date_debut'] ?? date('Y-m-d\TH:i');
    $dateFin = $_POST['date_fin'] ?? date('Y-m-d\TH:i', strtotime('+7 days'));
    $categorie = $_POST['categorie'] ?? 'Activité physique';
    $etapesTitres = $_POST['etapes'] ?? [];

    if (empty($titre)) {
        $messageErreur = "Le titre de l'objectif est obligatoire.";
    } elseif (mb_strlen($titre) > 150) {
        $messageErreur = 'Le titre ne doit pas dépasser 150 caractères.';
    } elseif (strtotime($dateDebut) === false || strtotime($dateFin) === false) {
        $messageErreur = 'Les dates de début et de fin sont invalides.';
    } elseif (strtotime($dateFin) <= strtotime($dateDebut)) {
        $messageErreur = "L'heure de fin doit venir après l'heure de début.";
    } else {
        // Création de l'objet Objectif (conforme au cours)
        $objectif = new Objectif($titre, $note, $dateDebut, $dateFin, 'en_cours', null, $categorie, 50);
        $idNouvelObj = $objectifC->ajouterObjectif($objectif);

        // Ajout des étapes associées
        if (!empty($etapesTitres) && is_array($etapesTitres)) {
            foreach ($etapesTitres as $titreEtape) {
                $titreEtape = trim($titreEtape);
                if (!empty($titreEtape) && mb_strlen($titreEtape) <= 255) {
                    $etape = new EtapeObjectif($titreEtape, $idNouvelObj);
                    $objectifC->ajouterEtape($etape);
                }
            }
        }

        header('Location: index.php?page=objectifs&succes=1');
        exit;
    }
}

// Récupération de la liste des objectifs
$listeObjectifs = $objectifC->afficherObjectifs();

// Calcul des statistiques pour le profil
$totalObjectifs = count($listeObjectifs);
$nbAtteints = 0;
$nbAVenir = 0;
$nbEnCours = 0;
$now = time();

foreach ($listeObjectifs as $obj) {
    if ($obj['statut'] === 'atteint') {
        $nbAtteints++;
    } else {
        $tDebut = strtotime($obj['date_debut']);
        if ($tDebut > $now) {
            $nbAVenir++;
        } else {
            $nbEnCours++;
        }
    }
}

// XP et Niveau
$xpTotal = $nbAtteints * 50;
$niveau = 1 + floor($xpTotal / 100);
$xpDansNiveau = $xpTotal % 100;

// Association d'image par catégorie
$imagesPiliers = [
    'Activité physique' => 'endurance',
    'Nutrition & Santé' => 'nutrition',
    'Santé mentale' => 'focus',
    'Sommeil & Récupération' => 'mobilite',
    'Habitudes de vie' => 'force'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="front-office/css/yq.css">
    <link rel="stylesheet" href="front-office/css/objectifs.css">
</head>
<body>
<?php require YQ_FRONT . '/includes/modules-nav.php'; ?>
<div class="app">
<main class="canvas" id="canvas">
    <!-- En-tête de la page -->
    <div class="title-row">
        <div>
            <p class="kicker">Objectifs</p>
            <h1>Mes objectifs</h1>
            <p class="about">« Chaque habitude est une quête. »</p>
            <p class="sub">Planifie un but comme une note pour le futur, avec une heure de début et une heure de fin.</p>
        </div>
        <div class="actions">
            <a class="btn sm" href="index.php?admin=objectifs" style="margin-right:8px;">Administration</a>
            <button class="btn primary" type="button" onclick="ouvrirModal()">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M8 3v10M3 8h10"/></svg>
                Nouveau but
            </button>
        </div>
    </div>

    <?php if ($messageErreur !== '') { ?>
        <p class="alert" role="alert"><?= htmlspecialchars($messageErreur) ?></p>
    <?php } ?>

    <!-- Carte Profil Aventurier & Progression XP -->
    <section class="profile" aria-label="Profil aventurier">
        <div class="panel profile-card">
            <img src="front-office/img/logo-objectifs.jpeg" alt="Logo" style="width:56px;height:56px;border-radius:14px;object-fit:cover;">
            <div class="who">
                <h2>Aventurier</h2>
                <div class="pills">
                    <span class="pill lilac">Niveau <?= $niveau ?></span>
                    <span class="pill">Félin Géométrique</span>
                </div>
                <div class="xp">
                    <div class="xp-line">
                        <div class="capbar" role="progressbar" aria-valuenow="<?= $xpDansNiveau ?>" aria-valuemin="0" aria-valuemax="100">
                            <i style="width: <?= $xpDansNiveau ?>%;"></i>
                        </div>
                        <strong><?= $xpTotal ?> XP</strong>
                    </div>
                    <small>Plus que <?= (100 - $xpDansNiveau) ?> XP pour atteindre le niveau <?= ($niveau + 1) ?></small>
                </div>
            </div>
        </div>
        <!-- Compteurs KPI -->
        <div class="kpis" aria-label="Compteurs">
            <div class="kpi"><div class="kpi-label">Total</div><div class="kpi-value"><?= $totalObjectifs ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot lilac"></span>À venir</div><div class="kpi-value"><?= $nbAVenir ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot gold"></span>En cours</div><div class="kpi-value"><?= $nbEnCours ?></div></div>
            <div class="kpi"><div class="kpi-label"><span class="dot sage"></span>Atteints</div><div class="kpi-value"><?= $nbAtteints ?></div></div>
        </div>
    </section>

    <!-- Onglets de filtrage -->
    <div class="tabs" role="tablist">
        <button type="button" role="tab" aria-selected="true" onclick="filtrerObjectifs('all', this)">Tous <span class="n"><?= $totalObjectifs ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('a_venir', this)">À venir <span class="n"><?= $nbAVenir ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('en_cours', this)">En cours <span class="n"><?= $nbEnCours ?></span></button>
        <button type="button" role="tab" aria-selected="false" onclick="filtrerObjectifs('atteint', this)">Atteints <span class="n"><?= $nbAtteints ?></span></button>
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
                $id = $obj['id'];
                $cat = $obj['categorie'] ?? 'Activité physique';
                $img = $imagesPiliers[$cat] ?? 'focus';
                $isAtteint = ($obj['statut'] === 'atteint');
                $etapes = $objectifC->recupererEtapes($id);
                $totalE = count($etapes);
                $valideesE = 0;
                foreach ($etapes as $e) { if ($e['est_validee']) $valideesE++; }
                $pourcentage = ($totalE > 0) ? round(($valideesE / $totalE) * 100) : ($isAtteint ? 100 : 0);
                
                $dateDebStr = !empty($obj['date_debut']) ? date('d M', strtotime($obj['date_debut'])) : '';
                $dateFinStr = !empty($obj['date_fin']) ? date('d M', strtotime($obj['date_fin'])) : '';
            ?>
            <article class="obj-card ivory on-ivory" data-statut="<?= htmlspecialchars($obj['statut']) ?>">
                <img class="art" src="front-office/img/piliers/<?= $img ?>.jpg" alt="" loading="lazy">
                <div class="body">
                    <p class="qline">
                        <span class="pilier"><?= htmlspecialchars($cat) ?></span> · 
                        du <strong><?= $dateDebStr ?></strong> au <strong><?= $dateFinStr ?></strong>
                        <span class="flag">+<?= $obj['xp_recompense'] ?? 50 ?> XP</span>
                    </p>
                    <h3 class="<?= $isAtteint ? 'done' : '' ?>"><?= htmlspecialchars($obj['titre']) ?></h3>
                    
                    <div class="note">
                        <span class="note-label">Note pour le futur</span>
                        <p><?= htmlspecialchars($obj['note'] ?? $obj['description'] ?? 'Aucune note.') ?></p>
                    </div>

                    <!-- Bloc des étapes de progression -->
                    <?php if (!empty($etapes)): ?>
                    <div class="etapes-box">
                        <div class="etapes-header">
                            <span>Étapes de progression</span>
                            <span class="etapes-ratio"><?= $valideesE ?> / <?= $totalE ?> complétée<?= $totalE > 1 ? 's' : '' ?> (<?= $pourcentage ?>%)</span>
                        </div>
                        <div class="etapes-progress-bar" role="progressbar" aria-valuenow="<?= $pourcentage ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="etapes-progress-fill" style="width: <?= $pourcentage ?>%;"></div>
                        </div>
                        <ul class="etapes-list">
                            <?php foreach ($etapes as $etape): 
                                $checkId = "chk-" . $id . "-" . $etape['IdEO'];
                                $isEtapeValidee = (bool)$etape['est_validee'];
                            ?>
                            <li class="etape-row <?= $isEtapeValidee ? 'validee' : '' ?>">
                                <input class="etape-check" type="checkbox" id="<?= $checkId ?>" 
                                       <?= $isEtapeValidee ? 'checked' : '' ?> 
                                       onchange="toggleEtape(<?= $etape['IdEO'] ?>, this)">
                                <label for="<?= $checkId ?>" class="etape-title"><?= htmlspecialchars($etape['titre']) ?></label>
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
                        <span class="pill st-<?= $obj['statut'] ?>">
                            <?= $isAtteint ? 'Accompli' : ($obj['statut'] === 'a_venir' ? 'À venir' : 'En cours') ?>
                        </span>
                        <div class="actions">
                            <?php if ($isAtteint): ?>
                                <a class="btn sm" href="index.php?page=objectifs&amp;toggle=<?= $id ?>">Reprendre</a>
                            <?php else: ?>
                                <a class="btn primary sm" href="index.php?page=objectifs&amp;toggle=<?= $id ?>">Accomplir</a>
                            <?php endif; ?>
                            <a class="btn danger sm icon" href="index.php?page=objectifs&amp;supprimer=<?= $id ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet objectif ?');" title="Supprimer">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:14px;height:14px;"><path d="M3 4.5h10M6.5 4.5V3h3v1.5M4.5 4.5l.6 8.5h5.8l.6-8.5"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- Boîte de dialogue : Formulaire d'ajout d'objectif (HTML5 + PHP) -->
<dialog class="dlg" id="dlgBut" aria-labelledby="dlgButTitle">
    <form method="POST" action="index.php?page=objectifs" onsubmit="return validerFormulaireObjectif()">
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

            <!-- Catégorie -->
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

            <!-- Note pour le futur -->
            <div class="field">
                <label for="butNote">Note pour le futur <small style="color:#888;">(pense-bête, astuce, motivation)</small></label>
                <textarea class="input" id="butNote" name="note" rows="2" placeholder="Ex. Préparer le tapis la veille au soir près de la fenêtre..."></textarea>
            </div>

            <!-- Dates Début & Fin -->
            <div class="field-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label for="butDateDebut">Date & Heure de début <span style="color:#d9534f">*</span></label>
                    <input class="input" type="datetime-local" id="butDateDebut" name="date_debut" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="field">
                    <label for="butDateFin">Date & Heure de fin <span style="color:#d9534f">*</span></label>
                    <input class="input" type="datetime-local" id="butDateFin" name="date_fin" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>" required>
                </div>
            </div>
            <p class="field-error" id="butDateErr" style="color:#d9534f;font-size:13px;"></p>

            <!-- Section des Étapes obligatoires (UML EtapeObjectif) -->
            <div class="field" style="margin-top:16px;border-top:1px solid rgba(255,255,255,0.1);padding-top:16px;">
                <label>Étapes à accomplir <span style="color:#d9534f">*</span></label>
                <p style="font-size:12px;color:#888;margin:0 0 8px;">Ajoute les étapes clés. L'objectif sera automatiquement validé quand toutes les étapes seront cochées !</p>
                
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <input class="input" type="text" id="nouvelleEtapeInput" placeholder="Ex. Préparer mon matériel...">
                    <button class="btn primary sm" type="button" onclick="ajouterChampEtape()" style="white-space:nowrap;">+ Ajouter</button>
                </div>
                <p class="field-error" id="etapeErr" style="color:#d9534f;font-size:13px;"></p>

                <!-- Liste des étapes ajoutées -->
                <div id="listeChampsEtapes" style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;">
                    <!-- Les étapes apparaîtront ici -->
                </div>
            </div>
        </div>

        <div class="foot">
            <button class="btn" type="button" onclick="fermerModal()">Annuler</button>
            <button class="btn primary" type="submit">Enregistrer l'objectif</button>
        </div>
    </form>
</dialog>

<!-- Script JavaScript simple -->
<script src="front-office/js/objectifs.js"></script>
</body>
</html>
