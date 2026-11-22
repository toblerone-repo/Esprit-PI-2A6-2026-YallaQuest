<?php
// [INSCRIPTION] Traitement du formulaire (même principe que login.php).
// Toute la validation et l'accès à la base sont dans UtilisateurC::register().
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = bin2hex(random_bytes(16));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) { http_response_code(403); exit('Jeton de sécurité invalide.'); }

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
        'prenom' => is_string($_POST['prenom'] ?? null) ? $_POST['prenom'] : '',
        'nom' => is_string($_POST['nom'] ?? null) ? $_POST['nom'] : '',
        'pseudo' => is_string($_POST['pseudo'] ?? null) ? $_POST['pseudo'] : '',
        'email' => is_string($_POST['email'] ?? null) ? $_POST['email'] : '',
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
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
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
                        <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <form id="joinForm" method="post" action="join_now.php" novalidate>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="field-grid">
                        <div class="field">
                            <label for="prenom">Prénom</label>
                            <input class="input" type="text" id="prenom" name="prenom" autocomplete="given-name" placeholder="Yassine" required value="<?= htmlspecialchars($old['prenom'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        </div>
                        <div class="field">
                            <label for="nom">Nom</label>
                            <input class="input" type="text" id="nom" name="nom" autocomplete="family-name" placeholder="Trabelsi" required value="<?= htmlspecialchars($old['nom'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        </div>
                        <div class="field full">
                            <label for="pseudo">Pseudo de quête</label>
                            <input class="input" type="text" id="pseudo" name="pseudo" autocomplete="nickname" placeholder="YassineLeLynx" required aria-describedby="pseudoHint" value="<?= htmlspecialchars($old['pseudo'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <span class="hint" id="pseudoHint">Visible par les autres aventuriers. 3 à 50 caractères : lettres, chiffres, _ ou -.</span>
                        </div>
                        <div class="field full">
                            <label for="email">E-mail</label>
                            <input class="input" type="email" id="email" name="email" autocomplete="email" placeholder="yassine@esprit.tn" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
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

<script nonce="<?= htmlspecialchars($cspNonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/ui.js"></script>
<script nonce="<?= htmlspecialchars($cspNonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/regex.js"></script>
<script nonce="<?= htmlspecialchars($cspNonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/validation.js"></script>
</body>
</html>
