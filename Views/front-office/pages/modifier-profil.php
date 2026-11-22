<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-type: application/json');
    $id = 1; 
    $hash = password_hash("YallaQuest123!", PASSWORD_DEFAULT);

    $nom = $_POST['nom']; 
    $prenom = $_POST['prenom']; 
    $email = $_POST['email']; 
    $nom = $_POST['nom']; 
    $ancien_mot_de_passe = $_POST['ancien-mot-de-passe'];
        $nouveau_mp = $_POST['nouveau-mot-de-passe'];

    $resultat = password_verify($ancien_mot_de_passe, $hash);

    if(!$resultat) {
        echo json_encode(false); 
        exit(); 
    }

    if (empty(trim($nom)) || empty(trim($prenom)) || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($nouveau_mp)<8 || $nouveau_mp !== $_POST['confirmer-mot-de-passe']) {
        http_response_code(400); 
        echo json_encode("Données invalides."); 
        exit(); 
    }

    $nouvau_hash = password_hash($nouveau_mp, PASSWORD_DEFAULT); 

    include_once __DIR__ . '/../../../Controllers/utilisateurC.php';

    $utilisateur = new Utilisateur($nom, $prenom, $email, $nouvau_hash, new DateTimeImmutable("today"), "", $id);
    $utilisateurC = new UtilisateurC(); 
    $utilisateurC->updateUtilisateur($utilisateur, $id); 
    

    echo json_encode($resultat);
    exit();
};

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
