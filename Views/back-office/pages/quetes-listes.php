<?php
// Équipe : template de base. Houssem : protections CSRF/XSS sur cette interface.
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'quetes';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();


$currentPage = 'quetes';

$quetes = [
    [
        'id_quete' => 1,
        'titre' => 'Préparer une salade méchouia.',
        'consignes' => '',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Nutrition',
        'categorie' => 'Recette',
        'type_activite' => [
            'libelle' => 'Cuisine',
            'unite' => 'portion'
        ],
        'contenu_details' => 'Prépare une salade méchouia maison avec des légumes grillés, de l’huile d’olive et les épices de ton choix.',
        'selected' => false,
        'date-debut' => '2026-11-14',
        'date-fin' => '2026-11-20'
    ],
    [
        'id_quete' => 2,
        'titre' => 'Marcher 30 minutes près de chez soi.',
        'consignes' => '',
        'difficulte' => 'Moyenne',
        'xp' => 25,
        'competence' => 'Endurance',
        'categorie' => 'Marche',
        'type_activite' => [
            'libelle' => 'Physique',
            'unite' => 'kilomètre'
        ],
        'contenu_details' => 'Profite d’une promenade de 30 minutes à ton rythme. Choisis un itinéraire agréable et reste en mouvement.',
        'selected' => true,
        'date-debut' => '2026-11-15',
        'date-fin' => '2026-11-21'
    ],
    [
        'id_quete' => 3,
        'titre' => "Séance d'étirements guidée.",
        'consignes' => '',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Mobilité',
        'categorie' => 'Étirements',
        'type_activite' => [
            'libelle' => 'Souplesse',
            'unite' => 'minute'
        ],
        'contenu_details' => 'Réalise une série d’étirements doux pour détendre tes muscles et améliorer ta souplesse.',
        'selected' => false,
        'date-debut' => '2026-11-16',
        'date-fin' => '2026-11-22'
    ],
    [
        'id_quete' => 4,
        'titre' => '10 minutes de respiration guidée.',
        'consignes' => '',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Focus',
        'categorie' => 'Exercice de respiration',
        'type_activite' => [
            'libelle' => 'Respiration',
            'unite' => 'minute'
        ],
        'contenu_details' => 'Installe-toi dans un endroit calme et concentre-toi sur ta respiration pendant 10 minutes.',
        'selected' => true,
        'date-debut' => '2026-11-14',
        'date-fin' => '2026-11-17'
    ],
    [
        'id_quete' => 5,
        'titre' => 'Renforcement adapté à son niveau.',
        'consignes' => '',
        'difficulte' => 'Difficile',
        'xp' => 50,
        'competence' => 'Force',
        'categorie' => 'Renforcement musculaire',
        'type_activite' => [
            'libelle' => 'Musculation',
            'unite' => 'répétition'
        ],
        'contenu_details' => 'Effectue des exercices de renforcement musculaire adaptés à tes capacités, comme des squats ou des pompes.',
        'selected' => false,
        'date-debut' => '2026-11-18',
        'date-fin' => '2026-11-30'
    ],
    [
        'id_quete' => 6,
        'titre' => "Chacun marche 20 minutes avant l'échéance.",
        'consignes' => '',
        'difficulte' => 'Moyenne',
        'xp' => 25,
        'competence' => 'Endurance',
        'categorie' => 'Marche',
        'type_activite' => [
            'libelle' => 'Physique',
            'unite' => 'kilomètre'
        ],
        'contenu_details' => 'Chaque membre de la guilde marche 20 minutes avant la date limite. Les XP sont attribués après la participation de tous.',
        'selected' => false,
        'date-debut' => '2026-11-19',
        'date-fin' => '2026-11-26'
    ],
    [
        'id_quete' => 7,
        'titre' => 'Faire 15 minutes de yoga doux.',
        'consignes' => '',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Mobilité',
        'categorie' => 'Yoga',
        'type_activite' => [
            'libelle' => 'Souplesse',
            'unite' => 'minute'
        ],
        'contenu_details' => 'Pratique une courte séance de yoga pour détendre ton corps et améliorer ta souplesse.',
        'selected' => false,
        'date-debut' => '2026-11-20',
        'date-fin' => '2026-11-27'
    ],
    [
        'id_quete' => 8,
        'titre' => 'Prendre une pause sans écran pendant 20 minutes.',
        'consignes' => '',
        'difficulte' => 'Facile',
        'xp' => 10,
        'competence' => 'Focus',
        'categorie' => 'Pause sans écran',
        'type_activite' => [
            'libelle' => 'Bien-être',
            'unite' => 'minute'
        ],
        'contenu_details' => 'Éloigne-toi des écrans pendant 20 minutes et profite d’une activité calme.',
        'selected' => true,
        'date-debut' => '2026-11-14',
        'date-fin' => '2026-11-21'
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


$categories = [
    ['id' => 1, 'nom' => 'Recette', 'id_type_activite' => 1],
    ['id' => 2, 'nom' => 'Marche', 'id_type_activite' => 2],
    ['id' => 3, 'nom' => 'Étirements', 'id_type_activite' => 3],
    ['id' => 4, 'nom' => 'Exercice de respiration', 'id_type_activite' => 4],
    ['id' => 5, 'nom' => 'Renforcement musculaire', 'id_type_activite' => 5],
    ['id' => 6, 'nom' => 'Yoga', 'id_type_activite' => 3],
    ['id' => 7, 'nom' => 'Pause sans écran', 'id_type_activite' => 6]
];


?>



<?php
$pageTitle = 'Quêtes · Administration · Yalla Quest';
require_once __DIR__ . '/../partials/head.php';
?>
    <div class="backoffice-layout">

        <div class="main-content container">

            <div id="backoffice-btn-div-quete">
                <button id="ajout-quete-btn-bo" class="submit-btn">Ajouter une quête</button>
            </div>

            <div id="modifier-form-container">
                <div id="form-modifier-quete" class="form-container">
                    <h1 class="form-title">Modifier une quête</h1>

                    <form action="quetes-listes.php" method="POST" id="quest-modifier">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <div class="form-group">
                            <label class="quest-form-label" for="title">Titre</label>
                            <input type="text" name="title" id="title" required>
                            <span class="message-erreur"></span>
                        </div>

                        <fieldset class="form-group">
                            <legend class="quest-form-label">Difficulté</legend>
                            <div class="quete-difficulte-options">
                                <input type='radio' class='difficulte-radio' name='difficulte' value="Difficile" id="difficulte-difficile" checked>
                                <label class='difficulte-pill' for="difficulte-difficile"><i class='fa-solid fa-check'></i>Difficile</label>

                                <input type='radio' class='difficulte-radio' name='difficulte' value="Moyenne" id="difficulte-moyenne">
                                <label class='difficulte-pill' for="difficulte-moyenne"><i class='fa-solid fa-check'></i>Moyenne</label>

                                <input type='radio' class='difficulte-radio' name='difficulte' value="Facile" id="difficulte-facile">
                                <label class='difficulte-pill' for="difficulte-facile"><i class='fa-solid fa-check'></i>Facile</label>
                            </div>
                        </fieldset>


                        <fieldset class="form-group">
                            <legend class="quest-form-label">Compétence</legend>
                            <div class="quete-competence-options">
                                <?php foreach ($competences as $index => $competence) {
                                $competence['id'] = (int) $competence['id'];
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='competence-radio' name='competence' id='competence-modifier-{$competence['id']}' value='{$competence['id']}' {$checked}>";

                                    echo "<label class='competence-pill' for='competence-modifier-{$competence['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>

                        <fieldset class="form-group">
                            <legend class="quest-form-label">Catégorie</legend>
                            <div class="quete-categorie-options">
                                <?php foreach ($categories as $index => $categorie) {
                                $categorie['id'] = (int) $categorie['id'];
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='categorie-radio' name='categorie' id='categorie-modifier-{$categorie['id']}' value='{$categorie['id']}' {$checked}>";

                                    echo "<label class='categorie-pill' for='categorie-modifier-{$categorie['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($categorie['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>



                        <div class="form-group">
                            <label class="quest-form-label" for="xp">XP</label>
                            <input name="xp" id="xp" inputmode="numeric" data-regex="xp" required></input>
                            <span class="message-erreur"></span>
                        </div>

                        <div class="btn-div-form">
                            <button type="submit" class="submit-btn btn">Enregistrer</button>
                        </div>


                    </form>
                </div>
            </div>

            <div id="add-form-container">
                <div id="form-ajout-quete" class="form-container">
                    <h1 class="form-title">Ajouter une quête</h1>

                    <form action="quetes-listes.php" method="POST" id="quest-ajout" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <div class="form-group">
                            <label class="quest-form-label" for="title">Titre</label>
                            <input type="text" name="title" id="title" required>
                            <span class="message-erreur"></span>

                        </div>

                        <fieldset class="form-group">
                            <legend class="quest-form-label">Difficulté</legend>
                            <div class="quete-difficulte-options">
                                <input type='radio' class='difficulte-radio' name='difficulte' value="Difficile" id="difficulte-difficile" checked>
                                <label class='difficulte-pill' for="difficulte-difficile"><i class='fa-solid fa-check'></i>Difficile</label>

                                <input type='radio' class='difficulte-radio' name='difficulte' value="Moyenne" id="difficulte-moyenne">
                                <label class='difficulte-pill' for="difficulte-moyenne"><i class='fa-solid fa-check'></i>Moyenne</label>

                                <input type='radio' class='difficulte-radio' name='difficulte' value="Facile" id="difficulte-facile">
                                <label class='difficulte-pill' for="difficulte-facile"><i class='fa-solid fa-check'></i>Facile</label>
                            </div>
                        </fieldset>


                        <fieldset class="form-group">
                            <legend class="quest-form-label">Compétence</legend>
                            <div class="quete-competence-options">
                                <?php foreach ($competences as $index => $competence) {
                                $competence['id'] = (int) $competence['id'];
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='competence-radio' name='competence' id='competence-ajout-{$competence['id']}' value='{$competence['id']}' {$checked}>";

                                    echo "<label class='competence-pill' for='competence-ajout-{$competence['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>

                        <fieldset class="form-group">
                            <legend class="quest-form-label">Catégorie</legend>
                            <div class="quete-categorie-options">
                                <?php foreach ($categories as $index => $categorie) {
                                $categorie['id'] = (int) $categorie['id'];
                                    $checked = $index === 0 ? 'checked' : '';
                                    echo "<input type='radio' class='categorie-radio' name='categorie' id='categorie-ajout-{$categorie['id']}' value='{$categorie['id']}' {$checked}>";

                                    echo "<label class='categorie-pill' for='categorie-ajout-{$categorie['id']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($categorie['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                                }
                                ?>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <label class="quest-form-label" for="xp">XP</label>
                            <input name="xp" id="xp" inputmode="numeric" data-regex="xp" required></input>
                            <span class="message-erreur"></span>

                        </div>


                        <div class="btn-div-form">
                            <button name="ajouter" id="add_quete_btn" type="submit" class="submit-btn btn">Enregistrer</button>
                        </div>


                    </form>
                </div>
            </div>


            <h2 id="liste-quete-titre">Liste de quêtes</h2>
            <div class="container">

                <table class="table-liste">
                    <tr>
                        <th>Titre</th>
                        <th>Compétence</th>
                        <th>Catégorie</th>
                        <th>Difficulté</th>
                        <th>XP</th>



                    </tr>

                    <?php
                    foreach ($quetes as $quete) {
                $titreHtml = htmlspecialchars((string) ($quete['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $competenceHtml = htmlspecialchars((string) ($quete['competence'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $categorieHtml = htmlspecialchars((string) ($quete['categorie'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $difficulteHtml = htmlspecialchars((string) ($quete['difficulte'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $xp = (int) ($quete['xp'] ?? 0);

                        echo "<tr>"
                            . "<td> {$titreHtml} </td>"
                            . "<td> {$competenceHtml}</td>"
                            . "<td> {$categorieHtml}</td>"
                            . "<td> {$difficulteHtml}</td>"
                            . "<td> {$xp}</td>"
                            . "<td class='cellule-actions'>"
                            . "<div class='actions-boutons'>"
                            . "<button type='button' class='action-btn mod-btn'> <i class='fa-solid fa-pen'></i></button>"
                            . "<button type='button' class='action-btn supp-btn'><i class='fa-solid fa-trash'></i> </button>"
                            . "</div>"
                            . "</td>"
                            . "</tr>";
                    };


                    ?>
                </table>
            </div>



        </div>
    </div>


    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/regex.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/quetes.js"></script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
