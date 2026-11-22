<?php
$currentPage = 'join_now';
$pageTitle = 'Créer mon compte · Yalla Quest';
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

                <div id="joinError" class="alert" role="alert" hidden></div>

                <form id="joinForm" novalidate>
                    <div class="field-grid">
                        <div class="field">
                            <label for="prenom">Prénom</label>
                            <input class="input" type="text" id="prenom" autocomplete="given-name" placeholder="Yassine" required>
                        </div>
                        <div class="field">
                            <label for="nom">Nom</label>
                            <input class="input" type="text" id="nom" autocomplete="family-name" placeholder="Trabelsi" required>
                        </div>
                        <div class="field full">
                            <label for="pseudo">Pseudo de quête</label>
                            <input class="input" type="text" id="pseudo" autocomplete="nickname" placeholder="YassineLeLynx" required aria-describedby="pseudoHint">
                            <span class="hint" id="pseudoHint">Visible par les autres aventuriers. Lettres, chiffres, tirets.</span>
                        </div>
                        <div class="field full">
                            <label for="email">E-mail</label>
                            <input class="input" type="email" id="email" autocomplete="email" placeholder="yassine@esprit.tn" required>
                        </div>
                        <div class="field">
                            <label for="mot_de_passe">Mot de passe</label>
                            <input class="input" type="password" id="mot_de_passe" autocomplete="new-password" required aria-describedby="mdpHint">
                            <span class="hint" id="mdpHint">8 caractères au moins, dont une lettre et un chiffre.</span>
                        </div>
                        <div class="field">
                            <label for="confirmer_mot_de_passe">Confirmer le mot de passe</label>
                            <input class="input" type="password" id="confirmer_mot_de_passe" autocomplete="new-password" required>
                        </div>
                        <div class="full"><button type="submit" class="btn primary block">Créer mon compte</button></div>
                    </div>
                </form>

                <p class="switch">Déjà membre de Yalla Quest ? <a href="../../../index.php?action=login">Se connecter</a></p>
            </section>
        </div>
    </main>

<script src="../../js/regex.js"></script>
<script src="../../js/ui.js"></script>
<script src="../../js/validation.js"></script>
</body>
</html>
