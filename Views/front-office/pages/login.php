<?php
/* PARTIE 5 : vue de connexion (index.php?action=login).
   Vue seulement : la vérification est faite par AuthController::login(), appelé par le routeur,
   qui fournit $errors (liste des erreurs) et $email (valeur à remettre dans le champ).
   <base> : la page est servie par index.php ; les chemins restent relatifs à ce dossier. */

// Accès direct au fichier : on repasse par le routeur.
if (!defined('YQ_ROUTEUR')) {
    header('Location: ../../../index.php?action=login');
    exit();
}

$pageTitle = 'Connexion · Yalla Quest';
$erreur = $errors[0] ?? '';
$email = $email ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="Views/front-office/pages/">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
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

                <div id="loginError" class="alert" role="alert"<?= $erreur === '' ? ' hidden' : '' ?>><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>

                <form id="loginForm" method="post" action="../../../index.php?action=login" novalidate>
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
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

<script src="../../js/regex.js"></script>
<script src="../../js/ui.js"></script>
<script src="../../js/validation.js"></script>
</body>
</html>
