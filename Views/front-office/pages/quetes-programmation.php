<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'quetes-programmation';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

AuthController::requireAuth();

$id = $_SESSION['user_id'] ?? null;

$currentPage = 'quetes-programmation';


$programmation = [
    [
        'id_programmation' => 1,
        'id_utilisateur' => 2,
        'id_quete' => 1,
        'date_debut' => '2026-11-24',
        'date_fin' => '2026-11-27',
        'statut' => 'programmee',
        'titre' => 'Préparer une salade méchouia.',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Nutrition'
    ],
    [
        'id_programmation' => 2,
        'id_utilisateur' => 2,
        'id_quete' => 20,
        'date_debut' => '2026-11-25',
        'date_fin' => '2026-11-28',
        'statut' => 'selectionnee',
        'titre' => 'Marcher 20 minutes dans son quartier.',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Endurance'
    ],
    [
        'id_programmation' => 3,
        'id_utilisateur' => 2,
        'id_quete' => 34,
        'date_debut' => '2026-11-24',
        'date_fin' => '2026-11-27',
        'statut' => 'programmee',
        'titre' => 'Pratiquer 10 minutes de yoga doux au réveil.',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Mobilité'
    ],
    [
        'id_programmation' => 4,
        'id_utilisateur' => 2,
        'id_quete' => 43,
        'date_debut' => '2026-11-25',
        'date_fin' => '2026-11-28',
        'statut' => 'selectionnee',
        'titre' => 'Suivre 15 minutes d\'exercices de respiration guidée.',
        'difficulte' => 'Moyenne',
        'xp' => 25,
        'competence' => 'Focus'
    ],
    [
        'id_programmation' => 5,
        'id_utilisateur' => 2,
        'id_quete' => 50,
        'date_debut' => '2026-11-27',
        'date_fin' => '2026-11-30',
        'statut' => 'programmee',
        'titre' => 'Faire 2 séries de 8 pompes contre un mur.',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Force'
    ],
    [
        'id_programmation' => 6,
        'id_utilisateur' => 2,
        'id_quete' => 15,
        'date_debut' => '2026-11-27',
        'date_fin' => '2026-11-30',
        'statut' => 'programmee',
        'titre' => 'Préparer une salade de lentilles et de légumes.',
        'difficulte' => 'Moyenne',
        'xp' => 25,
        'competence' => 'Nutrition'
    ]
];


$competences = [
    [
        'id' => 1,
        'nom' => 'Nutrition',
        'description' => 'Quêtes liées à une alimentation saine et équilibrée.'
    ],
    [
        'id' => 2,
        'nom' => 'Endurance',
        'description' => 'Quêtes visant à améliorer la résistance physique et cardiovasculaire.'
    ],
    [
        'id' => 3,
        'nom' => 'Mobilité',
        'description' => 'Quêtes favorisant la souplesse, les étirements et la mobilité du corps.'
    ],
    [
        'id' => 4,
        'nom' => 'Focus',
        'description' => 'Quêtes visant à améliorer la concentration, la relaxation et le bien-être mental.'
    ],
    [
        'id' => 5,
        'nom' => 'Force',
        'description' => 'Quêtes destinées à développer la force et le renforcement musculaire.'
    ]
];

$difficultes = ["Difficile", "Moyenne", "Facile"];

$pas_de_prog = count($programmation)===0? true : false;

?>



<!DOCTYPE html>
<html lang="fr">

<head>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
  <?php
    $pageTitle = 'Mes quêtes · Yalla Quest';
    require_once __DIR__ . '/../includes/head.php';
  ?>
</head>

<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container <?= htmlspecialchars((string) ($pas_de_prog ? 'sans-programmation' : '')) ?>" id="liste-quetes-cartes">
        <p id="message-acceuil">Des vraies quêtes chaque jour. <br> Des compétences qui progressent à chaque fois que tu te lèves et bouges.</p>
        <h1 id="titre-acceuil">CONQUIERS TA JOURNÉE</h1>

        <div id="etat-sans-programmation" class="btn-div-form">
            <button type="button" id="ouvrir-formulaire-vide" class="submit-btn">
                Choisir mes quêtes
            </button>
        </div>

        <div class="container form-container" id="quete-prog-formulaire">
            <h1 class="form-title">Choisissez vos quêtes du jour</h1>
            <form method="POST" action="" id="programmation-quetes-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div id="quete-section">
                    <fieldset class="quete-champ">
                        <legend class="quete-numero">Quête 01</legend>
                        <hr>
                        <fieldset class="form-group">
                            <legend class="quest-radio-label">Compétence</legend>
                            <div class="quete-competence-options">
                                <?php foreach ($competences as $index => $competence) {
                                $competence['id'] = (int) $competence['id'];
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='competence-radio' name='competence-1' id='competence-1-{$competence['id']}' value='{$competence['id']}' {$checked}>";

                                    echo "<label class='competence-pill' for='competence-1-{$competence['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>

                        <fieldset class="form-group">
                            <legend class="quest-radio-label">Difficulté</legend>
                            <div class="quete-difficulte-options">
                                <?php foreach ($difficultes as $index => $difficulte) {
                                $difficulteHtml = htmlspecialchars((string) $difficulte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='difficulte-radio' name='difficulte-1' id='difficulte-1-{$difficulteHtml}' value='{$difficulteHtml}' {$checked}>";

                                    echo "<label class='difficulte-pill' for='difficulte-1-{$difficulteHtml}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($difficulte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>
                    </fieldset>
                </div>



                <div id="ajout-quete-btn-div">
                    <button id="ajouter-quete" type="button" class="btn"><i class="fa-regular fa-square-plus"></i> Ajouter une autre quête</button>
                </div>

                <div class="btn-div-form">
                    <button type="submit" class="submit-btn btn">Enregistrer</button>
                </div>


            </form>
        </div>

        <div id="filter-btn-div">
            <button class="submit-btn" id="charger-1quete-btn">Ajouter une quête</button>
            <button class="surprise-btn submit-btn">Quête surprise!</button>
            <div class="dropdown">
                <button class="btn submit-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Trier selon
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="#">Difficulté - Ordre Croissant</a>
                    <a class="dropdown-item" href="#">Difficulté - Ordre Décroissant</a>
                    <a class="dropdown-item" href="#">XP - Ordre Croissant</a>
                    <a class="dropdown-item" href="#">XP - Ordre Décroissant</a>
                </div>
            </div>
        </div>
        <div id="ajouter-queteprog-div">
            <div class="container form-container" id="ajout-1quete-form">
                <h1 class="form-title">Ajouter une quête</h1>
                <div id="quete-section">
                    <fieldset class="quete-champ">
                        <fieldset class="form-group">
                            <legend class="quest-radio-label">Compétence</legend>
                            <div class="quete-competence-options">
                                <?php foreach ($competences as $index => $competence) {
                                $competence['id'] = (int) $competence['id'];
                                    $checked1 = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='competence-radio' name='competence' id='competence-{$competence['id']}' value='{$competence['id']}' {$checked1}>";

                                    echo "<label class='competence-pill' for='competence-{$competence['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>

                        <fieldset class="form-group">
                            <legend class="quest-radio-label">Difficulté</legend>
                            <div class="quete-difficulte-options">
                                <?php foreach ($difficultes as $index => $difficulte) {
                                $difficulteHtml = htmlspecialchars((string) $difficulte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='difficulte-radio' name='difficulte' id='difficulte-{$difficulteHtml}' value='{$difficulteHtml}' {$checked}>";

                                    echo "<label class='difficulte-pill' for='difficulte-{$difficulteHtml}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($difficulte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>
                    </fieldset>
                </div>


                <div class="btn-div-form">
                    <button type="submit" class="submit-btn btn">Enregistrer</button>
                </div>


                </form>
            </div>
        </div>

        <div id="modifier-queteprog-div">
            <div class="container form-container" id="modifier-queteprog-form">

                <h1 class="form-title">Un jour bonus pour ta quête?</h1>

                <p class="extension-message">
                    Besoin d'un peu plus de temps pour relever ce défi ?
                    Offre-toi <strong>24 heures supplémentaires</strong>
                    pour aller jusqu'au bout !
                </p>

                <p class="extension-warning">
                    <strong>✨ Un bonus à utiliser avec soin !</strong>
                    Tu ne peux prolonger qu'une seule quête toutes les
                    24 heures. Une fois ce bonus utilisé, il faudra attendre
                    avant de prolonger une autre quête, même nouvellement débloquée.
                </p>

                <form method="post" action="" id="extension-quete-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <input type="hidden" name="id-programmation" id="id-programmation-extension">

                    <div class="btn-div-form">

                        <button type="submit" id="confirmer-extension" class="submit-btn">
                            Confirmer 1+ jour
                        </button>

                        <button type="button" class="bouton-modifier annuler-extension">
                            Pas maintenant
                        </button>

                    </div>

                </form>


            </div>
        </div>


        <div id="quetes-grid" class="row mb-3">
            <?php
            foreach ($programmation as $quete) {
                $dateFin = new DateTimeImmutable($quete['date_fin']); 
                $aujourdhui = new DateTimeImmutable("today"); 
                $messageEcheance = $dateFin == $aujourdhui ? "<p class='quete-carte-echeance'>⏳ Échéance aujourd'hui</p>" : "";
                $boutons = $quete['statut'] == "selectionnee" ? "" : "<button class='btn bouton-modifier p-1'><i class='fa-solid fa-clock'></i></button><button class='btn bouton-supprimer p-1'><i class='fa-solid fa-trash'></i></button>";
                $iconeStatut = $quete['statut'] == "selectionnee" ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle';
                $texteStatut = $quete['statut'] == "selectionnee" ? 'Quête sélectionnée' : 'Sélectionner cette quête';
                $statutDesactive = $quete['statut'] == "selectionnee" ? 'disabled' : '';
                echo "<div class='col-2 px-1 quete-carte-div'>"
                    . "<div class='quete-carte p-3'>"
                    . "<div class='quete-carte-header'>"
                    . "<p class='quete-carte-competence'>".htmlspecialchars($quete['competence']) . "&#183; </p>"
                    . "<p class='quete-carte-difficulte'>".htmlspecialchars($quete['difficulte']) ."</p>"
                    . "<button type='button' class='quete-carte-statut' title='{$texteStatut}' {$statutDesactive}><i class='{$iconeStatut}' aria-hidden='true'></i></button>"
                    . "</div>"
                    . "<p class='quete-carte-titre'>".htmlspecialchars($quete['titre']) ."</p>"
                    . "<div class='carte-footer-div'>"
                    . "<p class='quete-carte-xp'>".htmlspecialchars($quete['xp']) ."XP</p>"
                    . "<div class='cartes-btn-div'>"
                    . $boutons
                    . "</div>"
                    . "</div>"
                    . $messageEcheance
                    . "</div>"
                    . "</div>";
            }
            ?>


        </div>


    </div>

    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        const competences = <?php echo json_encode($competences, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const difficultes = <?php echo json_encode($difficultes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/quetes.js"></script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>
