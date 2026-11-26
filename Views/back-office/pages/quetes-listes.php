<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'quetes';
if (
    in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)
) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();


include_once __DIR__ . '/../../../Controllers/queteC.php';
include_once __DIR__ . '/../../../Controllers/categorieC.php';
$queteC = new QueteC();
$categorieC = new CategorieC();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $titre = $_POST['titre'];
        $difficulte = $_POST['difficulte'];
        $xp = $_POST['xp'];
        $id_categorie = $_POST['id_categorie'];
        $id_competence = $_POST['id_competence'];

        $quete = new Quete($titre, $difficulte, $xp, $id_categorie, $id_competence);

        $queteC->addQuete($quete);
        header('Location: quetes-listes.php');
    } else if (isset($_POST['modifier'])) {
        $id = $_POST['id_quete']; 
        $titre = $_POST['titre'];
        $difficulte = $_POST['difficulte'];
        $xp = $_POST['xp'];
        $id_categorie = $_POST['id_categorie'];
        $id_competence = $_POST['id_competence'];

        $quete = new Quete($titre, $difficulte, $xp, $id_categorie, $id_competence);

        $queteC->updateQuete($quete, $id); 
        header('Location: quetes-listes.php');
    };
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_quete'])) {
        $id = $_GET['id_quete'];
        $queteAModifier = $queteC->chargerQuete($id);
        header('content-type: application/json');
        echo json_encode($queteAModifier);
        exit();
    };
};


$quetes = $queteC->listquetes();
$competences = $queteC->listCompetencesTemp(); //Replace with darine's function
$categories = $categorieC->listCategories();

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
                    <input type="hidden" name="id_quete">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="form-group">
                        <label class="quest-form-label" for="title">Titre</label>
                        <input type="text" name="titre" id="title" required>
                        <span class="message-erreur"></span>
                    </div>

                    <fieldset class="form-group">
                        <legend class="quest-form-label">Difficulté</legend>
                        <div class="quete-difficulte-options">
                            <input type='radio' class='difficulte-radio' name='difficulte' value="Difficile" id="difficulte-mod-difficile"

                                checked>
                            <label class='difficulte-pill' for="difficulte-mod-difficile"><i class='fa-solid fa-check'></i>Difficile</label>

                            <input type='radio' class='difficulte-radio' name='difficulte' value="Moyenne" id="difficulte-mod-moyenne">
                            <label class='difficulte-pill' for="difficulte-mod-moyenne"><i class='fa-solid fa-check'></i>Moyenne</label>

                            <input type='radio' class='difficulte-radio' name='difficulte' value="Facile" id="difficulte-mod-facile">
                            <label class='difficulte-pill' for="difficulte-mod-facile"><i class='fa-solid fa-check'></i>Facile</label>
                        </div>
                    </fieldset>


                    <fieldset class="form-group">
                        <legend class="quest-form-label">Compétence</legend>
                        <div class="quete-competence-options">
                            <?php foreach ($competences as $index => $competence) {
                                $competence['id_competence'] =  $competence['id_competence'];
                                $checked = $index === 0 ? 'checked' : '';
                                echo "<input type='radio' class='competence-radio' name='id_competence' id='competence-modifier-{$competence['id_competence']}' value='{$competence['id_competence']}' {$checked}>";

                                echo "<label class='competence-pill' for='competence-modifier-{$competence['id_competence']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom']) . "</label>";
                            }
                            ?>
                        </div>
                    </fieldset>

                    <fieldset class="form-group">
                        <legend class="quest-form-label">Catégorie</legend>
                        <div class="quete-categorie-options">
                            <?php foreach ($categories as $index => $categorie) {
                                $categorie['id_categorie'] =  $categorie['id_categorie'];
                                $checked = $index === 0 ? 'checked' : '';
                                echo "<input type='radio' class='categorie-radio' name='id_categorie' id='categorie-modifier-{$categorie['id_categorie']}' value='{$categorie['id_categorie']}' {$checked}>";

                                echo "<label class='categorie-pill' for='categorie-modifier-{$categorie['id_categorie']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($categorie['nom']) . "</label>";
                            }
                            ?>
                        </div>
                    </fieldset>



                    <div class="form-group">
                        <label class="quest-form-label" for="xp">XP</label>
                        <input name="xp" id="xp" type="text"></input>
                        <span class="message-erreur"></span>
                    </div>

                    <div class="btn-div-form">
                        <button name="modifier" type="submit" class="submit-btn btn">Enregistrer</button>
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
                        <label class="quest-form-label" for="titre">Titre</label>
                        <input type="text" name="titre" id="titre">
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
                                $competence['id_competence'] =  $competence['id_competence'];
                                $checked = $index === 0 ? 'checked' : '';
                                echo "<input type='radio' class='competence-radio' name='id_competence' id='competence-ajout-{$competence['id_competence']}' value='{$competence['id_competence']}' {$checked}>";

                                echo "<label class='competence-pill' for='competence-ajout-{$competence['id_competence']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($competence['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                            }
                            ?>
                        </div>
                    </fieldset>

                    <fieldset class="form-group">
                        <legend class="quest-form-label">Catégorie</legend>
                        <div class="quete-categorie-options">
                            <?php foreach ($categories as $index => $categorie) {
                                $categorie['id_categorie'] =  $categorie['id_categorie'];
                                $checked = $index === 0 ? 'checked' : '';
                                echo "<input type='radio' class='categorie-radio' name='id_categorie' id='categorie-ajout-{$categorie['id_categorie']}' value='{$categorie['id_categorie']}' {$checked}>";

                                echo "<label class='categorie-pill' for='categorie-ajout-{$categorie['id_categorie']}'><i class='fa-solid fa-check'></i>" . htmlspecialchars($categorie['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</label>";
                            }
                            ?>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <label class="quest-form-label" for="xp">XP</label>
                        <input name="xp" id="xp" type="number"></input>
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
                    $titreHtml = htmlspecialchars((string) ($quete['titre'] ?? ''));
                    $competenceHtml = htmlspecialchars((string) ($quete['competence'] ?? ''));
                    $categorieHtml = htmlspecialchars((string) ($quete['categorie'] ?? ''));
                    $difficulteHtml = htmlspecialchars((string) ($quete['difficulte'] ?? ''));
                    $xp =  ($quete['xp'] ?? 0);

                    echo "<tr>"
                        . "<td> {$titreHtml} </td>"
                        . "<td> {$competenceHtml}</td>"
                        . "<td> {$categorieHtml}</td>"
                        . "<td> {$difficulteHtml}</td>"
                        . "<td> {$xp}</td>"
                        . "<td class='cellule-actions'>"
                        . "<div class='actions-boutons'>"
                        . "<button data-id='{$quete['id_quete']}' name='mod-btn' type='button' class='action-btn mod-btn'> <i class='fa-solid fa-pen'></i></button>"
                        . "<button data-id='{$quete['id_quete']}' name='supp-btn' type='button' class='action-btn supp-btn'><i class='fa-solid fa-trash'></i> </button>"
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