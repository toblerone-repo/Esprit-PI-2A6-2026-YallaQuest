<?php
/* =====================================================================
   [WORKSHOP 5.1, 5.2, 5.5, 7.4] Page de Connexion (Login)
   ---------------------------------------------------------------------
   Architecture MVC : la vue ne contient aucune requête SQL.
   Toute la vérification d'authentification passe par le Contrôleur
   UtilisateurC::login($_POST).
   ===================================================================== */

// [WORKSHOP 5.5] Démarrage sécurisé de la session
ini_set('session.use_strict_mode', '1');   // Refuse les ID de session non générés par le serveur
ini_set('session.cookie_httponly', '1');   // Inaccessible au JavaScript (anti-vol de session XSS)
ini_set('session.cookie_samesite', 'Lax'); // Limite le CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// [WORKSHOP 5.5] Expiration par inactivité (30 minutes)
$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// [WORKSHOP 7.5] En-têtes HTTP de sécurité
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
$utilisateurC = new UtilisateurC();

// [WORKSHOP 5.2] Si déjà connecté : redirection immédiate vers l'espace approprié
if (UtilisateurC::isAuthenticated()) {
    $role = $_SESSION['role'] ?? ($_SESSION['utilisateur']['role'] ?? 'user');
    $redirect = ($role === 'admin') ? '../../back-office/pages/listuser.php' : 'page-profil.php';
    header('Location: ' . $redirect);
    exit();
}

$pageTitle = 'Connexion · Yalla Quest';
$erreur = '';
$succes = '';
$email = $email ?? '';

if (!empty($_GET['registered'])) {
    $succes = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
}

// [WORKSHOP 5.1] Traitement du formulaire POST via le Contrôleur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $errors = $utilisateurC->login($_POST);

    if (empty($errors)) {
        // Authentification réussie -> Redirection selon le rôle (Workshop 5.2 & 6.4)
        $role = $_SESSION['role'] ?? ($_SESSION['utilisateur']['role'] ?? 'user');
        $redirect = ($role === 'admin') ? '../../back-office/pages/listuser.php' : 'page-profil.php';
        header('Location: ' . $redirect);
        exit();
    } else {
        $erreur = implode(' ', $errors);
    }
}

// [WORKSHOP 7.4] Jeton CSRF
$csrfToken = $utilisateurC->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../../css/front-objectifs-styles.css">
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

                <?php if ($succes !== ''): ?>
                    <div class="alert alert-success" style="background:#e6f4ea; color:#188038; border:1px solid #b7dfc2; margin-bottom:14px; padding:10px 14px; border-radius:6px; font-size:13px;" role="status">
                        <?= htmlspecialchars($succes) ?>
                    </div>
                <?php endif; ?>

                <div id="loginError" class="alert" role="alert"<?= $erreur === '' ? ' hidden' : '' ?>><?= htmlspecialchars($erreur) ?></div>

                <form id="loginForm" method="post" action="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'login.php') ?>" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="field">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="field">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input class="input" type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>
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
