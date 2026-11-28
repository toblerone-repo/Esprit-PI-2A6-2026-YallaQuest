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


include_once __DIR__ . '/../../../Controllers/categorieC.php';
$categorieC = new CategorieC();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $nom = $_POST['nom'];
        $description = $_POST['description'];

        $categorie = new Categorie($nom, $description);

        $categorieC->ajouterCategorie($categorie);
        header('Location: categories-listes.php');
        exit;
    } else if (isset($_POST['modifier'])) {
        $id_categorie = $_POST['id_categorie'];
        $nom = $_POST['nom'];
        $description = $_POST['description'];

        $categorie = new Categorie($nom, $description);

        $categorieC->modifierCategorie($categorie, $id_categorie); 
        header('Location: categories-listes.php');
        exit;
    } else if (isset($_POST['supprimer'])) {
        $id_categorie = $_POST['id_categorie'];
        $categorieC->supprimerCategorie($id_categorie); 
    };
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_categorie'])) {
        $id_categorie = $_GET['id_categorie'];
        $categorieAModifier = $categorieC->chargerCategorie($id_categorie);
        header('content-type: application/json');
        echo json_encode($categorieAModifier);
        exit();
    };
};


$categories = $categorieC->listCategories();

?>



<?php
$pageTitle = 'Catégories · Administration · Yalla Quest';
require_once __DIR__ . '/../partials/head.php';
?>
<div class="backoffice-layout">

    <div class="main-content container">

        <div id="backoffice-btn-div-categorie">
            <button id="ajout-categorie-btn-bo" class="submit-btn">Ajouter une catégorie</button>
        </div>

        <div id="modifier-form-container">
            <div id="form-modifier-categorie" class="form-container">
                <h1 class="form-title">Modifier une catégorie</h1>

                <form action="categories-listes.php" method="POST" id="categorie-modifier" novalidate>
                    <input type="hidden" name="id_categorie">
                    <input type="hidden" name="modifier" value="1">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="form-group">
                        <label class="quest-form-label" for="nom-categorie-modifier">Nom</label>
                        <input type="text" name="nom" id="nom-categorie-modifier" required>
                        <span class="message-erreur"></span>
                    </div>

                    <div class="form-group">
                        <label class="quest-form-label" for="description-categorie-modifier">Description</label>
                        <textarea name="description" id="description-categorie-modifier"></textarea>
                        <span class="message-erreur"></span>
                    </div>

                    <div class="btn-div-form">
                        <button id="enregistrer-modification-categorie" type="submit" class="submit-btn btn">Enregistrer</button>
                    </div>


                </form>
            </div>
        </div>

        <div id="add-form-container">
            <div id="form-ajout-categorie" class="form-container">
                <h1 class="form-title">Ajouter une catégorie</h1>

                <form action="categories-listes.php" method="POST" id="categorie-ajout" novalidate>
                    <input type="hidden" name="ajouter" value="1">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="form-group">
                        <label class="quest-form-label" for="nom-categorie-ajout">Nom</label>
                        <input type="text" name="nom" id="nom-categorie-ajout" required>
                        <span class="message-erreur"></span>
                    </div>

                    <div class="form-group">
                        <label class="quest-form-label" for="description-categorie-ajout">Description</label>
                        <textarea name="description" id="description-categorie-ajout"></textarea>
                        <span class="message-erreur"></span>
                    </div>

                    <div class="btn-div-form">
                        <button id="enregistrer-ajout-categorie" type="submit" class="submit-btn btn">Enregistrer</button>
                    </div>


                </form>
            </div>
        </div>


        <h2 id="liste-categorie-titre">Catégories de quêtes</h2>
        <div class="container">

            <table class="table-liste">
                <tr>
                    <th>Nom</th>
                    <th>Description</th>



                </tr>

                <?php
                foreach ($categories as $categorie) {
                    $id_categorie = $categorie['id_categorie'];
                    $nom = htmlspecialchars($categorie['nom']);
                    $description = htmlspecialchars(($categorie['description'] ?? ''));
                    $jeton = htmlspecialchars($csrfToken);
                    echo "<tr>"
                        . "<td> {$nom} </td>"
                        . "<td> {$description} </td>"
                        . "<td class='cellule-actions'>"
                        . "<div class='actions-boutons'>"
                        . "<button data-id='{$id_categorie}' type='button' class='action-btn mod-categorie-btn' aria-label='Modifier la catégorie'> <i class='fa-solid fa-pen'></i></button>"
                        . "<form action='categories-listes.php' method='POST'>"
                        . "<input type='hidden' name='id_categorie' value='{$id_categorie}'>"
                        . "<input type='hidden' name='csrf_token' value='{$jeton}'>"
                        . "<button name='supprimer' value='1' data-id='{$id_categorie}' type='submit' class='action-btn supp-categorie-btn' aria-label='Supprimer la catégorie'><i class='fa-solid fa-trash'></i> </button>"
                        . "</form>"
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
<script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">const nbQuetesProgrammees = 0;</script>
<script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/quetes.js"></script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
