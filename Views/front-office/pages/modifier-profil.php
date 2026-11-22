<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode('Jeton de sécurité invalide. Rechargez la page.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id = filter_var(
        $_SESSION['utilisateur']['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        http_response_code(401);
        echo json_encode('Veuillez vous connecter.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit();
    }

    foreach (['nom', 'prenom', 'email', 'ancien-mot-de-passe', 'nouveau-mot-de-passe', 'confirmer-mot-de-passe'] as $field) {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
            http_response_code(400);
            echo json_encode('Données invalides.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit();
        }
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $ancien_mot_de_passe = $_POST['ancien-mot-de-passe'];
    $nouveau_mp = $_POST['nouveau-mot-de-passe'];

    if ($nom === '' || $prenom === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        || strlen($nouveau_mp) < 8 || $nouveau_mp !== $_POST['confirmer-mot-de-passe']) {
        http_response_code(400);
        echo json_encode('Données invalides.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit();
    }

    include_once __DIR__ . '/../../../Controllers/utilisateurC.php';

    $utilisateur = new Utilisateur($nom, $prenom, $email, $nouveau_mp, '', $id);
    $utilisateurC = new UtilisateurC();
    $resultat = $utilisateurC->updateUtilisateur($utilisateur, $id, $ancien_mot_de_passe);

    if ($resultat) {
        $_SESSION['utilisateur']['nom'] = $nom;
        $_SESSION['utilisateur']['prenom'] = $prenom;
        $_SESSION['utilisateur']['mail'] = $email;
    } else {
        http_response_code(403);
    }

    echo json_encode($resultat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit();
}

$pageTitle = 'Modifier mon profil · Yalla Quest';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <?php
    $currentPage = 'modifier-profil';
    require_once __DIR__ . '/../includes/head.php';
  ?>
</head>
<body class="page-modifier-profil">
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth">
    <div class="conteneur-modification-profil">
        <section class="panel auth-card" aria-labelledby="titre-modification">
            <div class="auth-brand"><img src="../../img/logo.jpeg" alt="" width="40" height="40">Yalla Quest</div>
            <div>
                <p class="kicker">Mon compte</p>
                <h1 id="titre-modification">Modifier mon profil</h1>
                <p class="about">Mettez à jour vos informations personnelles.</p>
            </div>

            <?php if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
                <div class="alert" role="alert">L’enregistrement des modifications n’est pas encore disponible.</div>
            <?php endif; ?>

            <form id="formulaire-modification" method="post" action="modifier-profil.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div class="field-grid">
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <input class="input" type="text" id="prenom" name="prenom">
                        <span></span>
                    </div>
                    <div class="field">
                        <label for="nom">Nom</label>
                        <input class="input" type="text" id="nom" name="nom">
                        <span></span>
                    </div>
                    <div class="field full">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email">
                        <span></span>
                    </div>
                    <div class="field full">
                        <label for="ancien-mot-de-passe">Ancien mot de passe</label>
                        <input class="input" type="password" id="ancien-mot-de-passe" name="ancien-mot-de-passe">
                        <span></span>
                    </div>
                    <div class="field">
                        <label for="nouveau-mot-de-passe">Nouveau mot de passe</label>
                        <input class="input" type="password" id="nouveau-mot-de-passe" name="nouveau-mot-de-passe">
                        <span></span>
                    </div>
                    <div class="field">
                        <label for="confirmer-mot-de-passe">Confirmer le nouveau mot de passe</label>
                        <input class="input" type="password" id="confirmer-mot-de-passe" name="confirmer-mot-de-passe" autocomplete="new-password">
                        <span></span>
                    </div>
                    <div class="full"><button type="submit" class="btn primary block modifier-btn-profil">Enregistrer les modifications</button></div>
                </div>
            </form>

            <p class="switch"><a href="page-profil.php">Retour au profil</a></p>
        </section>
    </div>

    <script src="../../js/profil.js"></script>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
