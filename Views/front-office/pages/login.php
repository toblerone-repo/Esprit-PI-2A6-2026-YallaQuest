<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

// Déjà connecté : pas besoin de revoir le formulaire.
if (isset($_SESSION['utilisateur'])) {
    header('Location: page-profil.php');
    exit();
}

$pageTitle = 'Connexion · Yalla Quest';
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $mot_de_passe = is_string($_POST['mot_de_passe'] ?? null) ? $_POST['mot_de_passe'] : '';

    if ($email === '' || $mot_de_passe === '') {
        $erreur = 'Saisis ton e-mail et ton mot de passe.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $erreur = "L'adresse e-mail n'est pas valide.";
    } else {
        include_once __DIR__ . '/../../../Controllers/utilisateurC.php';
        $utilisateurC = new UtilisateurC();
        $utilisateur = $utilisateurC->login($email, $mot_de_passe);

        if ($utilisateur === null) {
            $erreur = 'E-mail ou mot de passe incorrect.';
        } else {
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            $_SESSION['utilisateur'] = $utilisateur;
            header('Location: page-profil.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <?php
    $currentPage = 'login';
    require_once __DIR__ . '/../includes/head.php';
  ?>
</head>
<body>
    <main class="auth">
        <div>
            <section class="panel auth-card" aria-labelledby="loginTitle">
                <div class="auth-brand"><img src="../../img/logo.jpeg" alt="" width="40" height="40">Yalla Quest</div>
                <div>
                    <p class="kicker">Bon retour</p>
                    <h1 id="loginTitle">Connexion</h1>
                    <p class="about">« Chaque habitude est une quête. »</p>
                </div>

                <div id="loginError" class="alert" role="alert"<?= htmlspecialchars((string) ($erreur === '' ? ' hidden' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars($erreur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>

                <form id="loginForm" method="post" action="login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    </div>
                    <div class="field">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input class="input" type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn primary block">Se connecter</button>
                </form>

                <p class="switch">Pas encore membre ? <a href="join_now.php">Créer mon compte</a></p>
            </section>
        </div>
    </main>

<script src="../../js/ui.js"></script>
<script src="../../js/validation.js"></script>
</body>
</html>
