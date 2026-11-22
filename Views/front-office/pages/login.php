<?php
session_start();

// Déjà connecté : pas besoin de revoir le formulaire.
if (isset($_SESSION['utilisateur'])) {
    header('Location: page-profil.php');
    exit();
}

$pageTitle = 'Connexion · Yalla Quest';
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

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

                <div id="loginError" class="alert" role="alert"<?= $erreur === '' ? ' hidden' : '' ?>><?= htmlspecialchars($erreur) ?></div>

                <form id="loginForm" method="post" action="login.php" novalidate>
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" value="<?= htmlspecialchars($email) ?>">
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
