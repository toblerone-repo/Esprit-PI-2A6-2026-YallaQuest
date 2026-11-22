<?php
// [INSCRIPTION] Traitement du formulaire (même principe que login.php).
// Toute la validation et l'accès à la base sont dans UtilisateurC::register().
session_start();

include_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// Déjà connecté : pas besoin de créer un compte.
if (UtilisateurC::isAuthenticated()) {
    header('Location: page-profil.php');
    exit();
}

$currentPage = 'join_now';
$pageTitle = 'Créer mon compte · Yalla Quest';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilisateurC = new UtilisateurC();
    $errors = $utilisateurC->register($_POST);

    // Valeurs réaffichées en cas d'erreur (jamais les mots de passe)
    $old = [
        'prenom' => $_POST['prenom'] ?? '',
        'nom' => $_POST['nom'] ?? '',
        'pseudo' => $_POST['pseudo'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];

    if (empty($errors)) {
        header('Location: login.php?registered=1');
        exit();
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
            <section class="panel auth-card" aria-labelledby="joinTitle">
                <div class="auth-brand"><img src="../../img/logo.jpeg" alt="" width="40" height="40">Yalla Quest</div>
                <div>
                    <p class="kicker">Nouveau départ</p>
                    <h1 id="joinTitle">Rejoindre la guilde</h1>
                    <p class="about">« Chaque habitude est une quête. »</p>
                </div>

                <div id="joinError" class="alert" role="alert"<?= empty($errors) ? ' hidden' : '' ?>>
                    <?php if (!empty($errors)): ?>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <form id="joinForm" method="post" action="join_now.php" novalidate>
                    <div class="field-grid">
                        <div class="field">
                            <label for="prenom">Prénom</label>
                            <input class="input" type="text" id="prenom" name="prenom" autocomplete="given-name" placeholder="Yassine" required value="<?= htmlspecialchars($old['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="field">
                            <label for="nom">Nom</label>
                            <input class="input" type="text" id="nom" name="nom" autocomplete="family-name" placeholder="Trabelsi" required value="<?= htmlspecialchars($old['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="field full">
                            <label for="pseudo">Pseudo de quête</label>
                            <input class="input" type="text" id="pseudo" name="pseudo" autocomplete="nickname" placeholder="YassineLeLynx" required aria-describedby="pseudoHint" value="<?= htmlspecialchars($old['pseudo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <span class="hint" id="pseudoHint">Visible par les autres aventuriers. 3 à 50 caractères : lettres, chiffres, _ ou -.</span>
                        </div>
                        <div class="field full">
                            <label for="email">E-mail</label>
                            <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="field">
                            <label for="mot_de_passe">Mot de passe</label>
                            <input class="input" type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="new-password" required aria-describedby="mdpHint">
                            <span class="hint" id="mdpHint">8 caractères au moins.</span>
                        </div>
                        <div class="field">
                            <label for="confirmer_mot_de_passe">Confirmer le mot de passe</label>
                            <input class="input" type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" autocomplete="new-password" required>
                        </div>
                        <div class="full"><button type="submit" class="btn primary block">Créer mon compte</button></div>
                    </div>
                </form>

                <p class="switch">Déjà membre de Yalla Quest ? <a href="login.php">Se connecter</a></p>
            </section>
        </div>
    </main>

<script src="../../js/ui.js"></script>
<script src="../../js/validation.js"></script>
</body>
</html>
