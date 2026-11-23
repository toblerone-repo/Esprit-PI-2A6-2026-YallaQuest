<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'page-profil';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireAuth();

if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../../session.php';
}


$id = $_SESSION['user_id'] ?? null;
if ($id === null) {
    exit('Connexion requise.');
}

include_once __DIR__ . '/../../../Controllers/utilisateurC.php';
$utilisateurC = new UtilisateurC();
$utilisateur = $utilisateurC->getProfilUtilisateur((int) $id);
if (!$utilisateur) { 
    http_response_code(404); 
    exit('Compte introuvable.'); 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php $currentPage = 'page-profil'; $pageTitle = $pageTitle ?? 'Mon profil | YallaQuest'; require_once __DIR__ . '/../includes/head.php'; ?>
</head>

<body class="page-profil">
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container conteneur-profil py-5">

        <section class="entete-profil">
            <p class="surtitre-profil">
                ESPACE PERSONNEL / YALLAQUEST
            </p>

            <h1 class="titre-profil">MON PROFIL</h1>

            <p class="sous-titre-profil">
                Votre identité. Votre progression. Votre aventure.
            </p>
        </section>

        <section class="carte-profil">

            <div class="ligne-identite">

                <div class="avatar-profil">
                    <?php if (!empty($utilisateur['chemin'])): ?>
                        <img src="<?= htmlspecialchars($utilisateur['chemin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="">
                    <?php else: ?>
                        <?= htmlspecialchars($utilisateur['prenom'][0] . $utilisateur['nom'][0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    <?php endif; ?>
                </div>

                <div class="infos-identite">
                    <h2 class="nom-profil">
                        <?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </h2>

                    <p class="email-profil">
                        <?= htmlspecialchars($utilisateur['mail'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>

                    <p class="niveau-profil">
                        <i class="bi bi-stars"></i>
                        AVENTURIER · NIVEAU <?= htmlspecialchars((string) ($utilisateur['niveau']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                </div>

            </div>

            <form action="modifier-profil.php" method="get">
                <button class="bouton-modifier-profil" type="submit">
                    <i class="bi bi-pencil-square"></i>
                    Modifier mes informations
                </button>
            </form>

        </section>

        <section class="grille-statistiques">

            <div class="carte-profil carte-statistique">
                <p class="etiquette-carte">EXPÉRIENCE TOTALE</p>

                <p class="valeur-statistique">
                    <?= htmlspecialchars((string) (number_format($utilisateur['total_xp'], 0, ',', ' ')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>

                <p class="texte-accent">XP cumulés</p>
            </div>

            <div class="carte-profil carte-statistique">
                <p class="etiquette-carte">NIVEAU ACTUEL</p>

                <p class="valeur-statistique">
                    <?= htmlspecialchars((string) ($utilisateur['niveau']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>

                <p class="texte-accent">Progression</p>
            </div>

        </section>

        <section class="carte-profil">

            <div class="ligne-titre">
                <h2 class="titre-carte">Mon totem</h2>

                <span class="statut-totem">
                    <i class="bi bi-stars"></i>
                    Totem obtenu
                </span>
            </div>

            <div class="ligne-totem">

                <div class="illustration-totem">
                    <i class="bi bi-shield-shaded"></i>
                </div>

                <div class="infos-totem">
                    <?php if (isset($utilisateur['id_totem'])) {?>
                    <h3 class="nom-totem">
                        Totem n°<?= htmlspecialchars((string) ($utilisateur['id_totem']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </h3>

                    <p class="texte-secondaire">
                        Votre totem actuel
                    </p>

                    <p class="date-totem">
                        <i class="bi bi-calendar3"></i>
                        Obtenu le <?= htmlspecialchars($utilisateur['date_obtention_totem'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                    <?php } else {?>
                    <p class="texte-secondaire">Aucun totem obtenu pour le moment.</p>
                    <?php } ?>
                </div>

            </div>

        </section>

        <section class="carte-profil">

            <div class="ligne-titre">
                <h2 class="titre-carte">Prolongation de quête</h2>
                <i class="bi bi-clock-history icone-accent"></i>
            </div>

            <p class="etiquette-carte">
                DERNIÈRE PROLONGATION
            </p>

            <p class="date-prolongation">
                <?= $utilisateur['derniere_prolongation_at']? htmlspecialchars($utilisateur['derniere_prolongation_at']) : "Pas de prolongation" ?>
            </p>

            <div class="information-prolongation">
                <i class="bi bi-info-circle icone-accent"></i>

                <p class="texte-secondaire">
                    Une prolongation est autorisée toutes les 24 heures.
                    Votre prochaine disponibilité dépend de votre
                    dernière utilisation.
                </p>
            </div>

        </section>

        <section class="carte-profil">

            <h2 class="titre-carte titre-informations">
                Informations du compte
            </h2>

            <div class="grille-informations">

                <div class="champ-information">
                    <p class="etiquette-carte">PRÉNOM</p>

                    <p class="valeur-information">
                        <?= htmlspecialchars($utilisateur['prenom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                </div>

                <div class="champ-information">
                    <p class="etiquette-carte">NOM</p>

                    <p class="valeur-information">
                        <?= htmlspecialchars($utilisateur['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                </div>

            </div>

            <div class="separateur-informations">
                <p class="etiquette-carte">ADRESSE E-MAIL</p>

                <p class="valeur-information">
                    <?= htmlspecialchars($utilisateur['mail'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>
            </div>

            <div class="separateur-informations ligne-mot-de-passe">

                <div class="champ-information">
                    <p class="etiquette-carte">MOT DE PASSE</p>
                    <p class="valeur-information">••••••••••••</p>
                </div>

            </div>

        </section>

    </main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
