
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// [WORKSHOP 5.3] Protection de la page : authentification obligatoire
UtilisateurC::requireAuth('login.php');

$idConnecte = UtilisateurC::getIdConnecte();
$utilisateurC = new UtilisateurC();
$u = $idConnecte ? $utilisateurC->getUtilisateur($idConnecte) : null;

if ($u) {
    $utilisateur = [
        'id_utilisateur' => $u->getId(),
        'nom' => $u->getNom(),
        'prenom' => $u->getPrenom(),
        'mail' => $u->getEmail(),
        'mot_de_passe_hash' => $u->getMp(),
        'total_xp' => $u->getXp(),
        'niveau' => $u->getNiveau(),
        'derniere_prolongation_at' => $u->getDerniereProlongationAt()?->format('Y-m-d H:i:s') ?? '2026-11-19 14:30:00',
        'id_totem' => $u->getIdTotem(),
        'date_obtention_totem' => $u->getDateObtentionTotem()?->format('Y-m-d') ?? '2026-11-14',
        'img_profil' => $u->getImgProfil() ?? 'renard'
    ];
} else {
    // Si le compte a été supprimé en base
    $utilisateurC->logout();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil | YallaQuest</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/profil.css">
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
                    <?= htmlspecialchars($utilisateur['prenom'][0] . $utilisateur['nom'][0]) ?>
                </div>

                <div class="infos-identite">
                    <h2 class="nom-profil">
                        <?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>
                    </h2>

                    <p class="email-profil">
                        <?= htmlspecialchars($utilisateur['mail']) ?>
                    </p>

                    <p class="niveau-profil">
                        <i class="bi bi-stars"></i>
                        AVENTURIER · NIVEAU <?= $utilisateur['niveau'] ?>
                    </p>
                </div>

            </div>

            <div class="actions-profil">
                <form action="modifier-profil.php" method="get" class="form-action-profil">
                    <button class="bouton-modifier-profil" type="submit">
                        <i class="bi bi-pencil-square"></i>
                        Modifier mes informations
                    </button>
                </form>
                <a href="logout.php" class="bouton-deconnexion-profil" title="Fermer la session">
                    <i class="bi bi-box-arrow-right"></i>
                    Se déconnecter
                </a>
            </div>

        </section>

        <section class="grille-statistiques">

            <div class="carte-profil carte-statistique">
                <p class="etiquette-carte">EXPÉRIENCE TOTALE</p>

                <p class="valeur-statistique">
                    <?= number_format($utilisateur['total_xp'], 0, ',', ' ') ?>
                </p>

                <p class="texte-accent">XP cumulés</p>
            </div>

            <div class="carte-profil carte-statistique">
                <p class="etiquette-carte">NIVEAU ACTUEL</p>

                <p class="valeur-statistique">
                    <?= $utilisateur['niveau'] ?>
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
                    <h3 class="nom-totem">
                        Totem n°<?= $utilisateur['id_totem'] ?>
                    </h3>

                    <p class="texte-secondaire">
                        Votre totem actuel
                    </p>

                    <p class="date-totem">
                        <i class="bi bi-calendar3"></i>
                        Obtenu le <?= htmlspecialchars($utilisateur['date_obtention_totem']) ?>
                    </p>
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
                <?= htmlspecialchars($utilisateur['derniere_prolongation_at']) ?>
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
                        <?= htmlspecialchars($utilisateur['prenom']) ?>
                    </p>
                </div>

                <div class="champ-information">
                    <p class="etiquette-carte">NOM</p>

                    <p class="valeur-information">
                        <?= htmlspecialchars($utilisateur['nom']) ?>
                    </p>
                </div>

            </div>

            <div class="separateur-informations">
                <p class="etiquette-carte">ADRESSE E-MAIL</p>

                <p class="valeur-information">
                    <?= htmlspecialchars($utilisateur['mail']) ?>
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
