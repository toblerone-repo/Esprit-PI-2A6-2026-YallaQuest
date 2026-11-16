<?php
$currentPage = 'objectifs';
$pageTitle = 'Objectifs · Administration · Yalla Quest';

if (!defined('YQ_ROOT')) {
    header('Location: ../../../../index.php?admin=objectifs');
    exit;
}
require_once YQ_BACK . '/app/Controllers/AdminController.php';

$objectifC = new AdminController();

// 1. Suppression d'un objectif par l'administrateur
if (isset($_GET['supprimer'])) {
    $idSuppr = intval($_GET['supprimer']);
    $objectifC->supprimerObjectif($idSuppr);
    header('Location: index.php?admin=objectifs');
    exit;
}

// 2. Ajout d'un objectif par l'administrateur
$messageErreur = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ajouter_objectif_admin'])) {
    $titre = trim($_POST['titre'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $dateDebut = $_POST['date_debut'] ?? date('Y-m-d\TH:i');
    $dateFin = $_POST['date_fin'] ?? date('Y-m-d\TH:i', strtotime('+7 days'));
    $categorie = $_POST['categorie'] ?? 'Activité physique';
    $xp = intval($_POST['xp'] ?? 50);
    $etapesTitres = $_POST['etapes'] ?? [];

    if ($titre === '' || mb_strlen($titre) > 150) {
        $messageErreur = 'Le titre est obligatoire (150 caractères au plus).';
    } elseif (strtotime($dateDebut) === false || strtotime($dateFin) === false || strtotime($dateFin) <= strtotime($dateDebut)) {
        $messageErreur = 'La date de fin doit venir après la date de début.';
    } elseif ($xp < 0 || $xp > 1000) {
        $messageErreur = 'La récompense XP doit être comprise entre 0 et 1000.';
    } else {
        $obj = new Objectif($titre, $note, $dateDebut, $dateFin, 'en_cours', null, $categorie, $xp);
        $idNouvelObj = $objectifC->ajouterObjectif($obj);

        if (!empty($etapesTitres) && is_array($etapesTitres)) {
            foreach ($etapesTitres as $titreEtape) {
                $titreEtape = trim($titreEtape);
                if (!empty($titreEtape) && mb_strlen($titreEtape) <= 255) {
                    $etape = new EtapeObjectif($titreEtape, $idNouvelObj);
                    $objectifC->ajouterEtape($etape);
                }
            }
        }

        header('Location: index.php?admin=objectifs&succes=1');
        exit;
    }
}

// Récupération de tous les objectifs
$listeObjectifs = $objectifC->afficherObjectifs();
$totalQuests = count($listeObjectifs);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="front-office/css/yq.css">
    <link rel="stylesheet" href="back-office/src/css/admin-objectifs.css">
</head>
<body>
<?php require YQ_BACK . '/app/Views/partials/admin-nav.php'; ?>
<div class="app">
<main class="canvas" id="canvas">
    <!-- En-tête Back-Office -->
    <div class="title-row">
        <div>
            <p class="kicker">Administration · objectifs</p>
            <h1>Objectifs</h1>
            <p class="about">Les buts planifiés pour le futur, leurs notes et leurs périodes, pour chaque aventurier.</p>
            <p class="sub"><?= $totalQuests ?> objectif<?= $totalQuests > 1 ? 's' : '' ?> enregistré<?= $totalQuests > 1 ? 's' : '' ?></p>
        </div>
        <div class="actions">
            <a class="btn sm" href="index.php?page=objectifs" style="margin-right:8px;">Espace Aventurier</a>
            <button class="btn primary" type="button" onclick="document.getElementById('dlgQuest').showModal();">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M8 3v10M3 8h10"/></svg>
                Assigner un but
            </button>
        </div>
    </div>

    <?php if ($messageErreur !== '') { ?>
        <p class="alert" role="alert"><?= htmlspecialchars($messageErreur) ?></p>
    <?php } ?>

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
                            $etapes = $objectifC->recupererEtapes($id);
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
                                <a class="btn danger sm" href="index.php?admin=objectifs&amp;supprimer=<?= $id ?>" onclick="return confirm('Confirmer la suppression de cet objectif ?');">
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
    <form method="POST" action="index.php?admin=objectifs" onsubmit="return validerAdminObjectif()">
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
                <label for="adminNote">Note pour le futur</label>
                <textarea class="input" id="adminNote" name="note" rows="2" placeholder="Instructions ou note de suivi..."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label for="adminDebut">Date début *</label>
                    <input class="input" type="datetime-local" id="adminDebut" name="date_debut" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="field">
                    <label for="adminFin">Date fin *</label>
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

<script>
function filtrerTableau() {
    var search = document.getElementById('questSearch').value.toLowerCase();
    var filter = document.getElementById('questStatutFilter').value;
    var rows = document.querySelectorAll('#adminTable tbody tr');

    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var statut = row.getAttribute('data-statut');
        var matchSearch = (search === '' || text.indexOf(search) !== -1);
        var matchFilter = (filter === 'all' || statut === filter);
        row.style.display = (matchSearch && matchFilter) ? '' : 'none';
    });
}

function ajouterEtapeAdmin() {
    var txt = document.getElementById('adminEtapeTxt');
    var liste = document.getElementById('adminListeEtapes');
    if (!txt || !txt.value.trim() || !liste) return;
    
    var div = document.createElement('div');
    div.style = "display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.05);padding:6px 12px;border-radius:6px;";
    div.innerHTML = '<input type="hidden" name="etapes[]" value="' + txt.value.trim() + '">' +
                    '<span>🔹 ' + txt.value.trim() + '</span>' +
                    '<button type="button" class="btn danger sm" onclick="this.parentElement.remove()">✕</button>';
    liste.appendChild(div);
    txt.value = '';
    txt.focus();
}

function validerAdminObjectif() {
    var titre = document.getElementById('adminTitre').value.trim();
    var debut = document.getElementById('adminDebut').value;
    var fin = document.getElementById('adminFin').value;
    var etapes = document.querySelectorAll('#adminListeEtapes input[name="etapes[]"]');

    if (!titre) { alert("Le titre est requis."); return false; }
    if (new Date(fin) <= new Date(debut)) { alert("La date de fin doit être après le début."); return false; }
    if (etapes.length === 0) { alert("Ajoutez au moins une étape pour cet objectif."); return false; }
    return true;
}
</script>
</body>
</html>
