<?php
/* PARTIE 5.3 : espace utilisateur (index.php?action=dashboard).
   Vue seulement : reçoit $user (Utilisateur) du routeur et n'affiche que du HTML.
   Style propre à la page : Views/css/user-space.css. */

// Accès direct au fichier : on repasse par le routeur, qui vérifie la session.
if (!defined('YQ_ROUTEUR')) {
    header('Location: ../../../index.php?action=dashboard');
    exit();
}

$e = fn($valeur): string => htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
$roleLibelle = $user->getRole() === 'admin' ? 'Administrateur' : 'Utilisateur';

ob_start();
?>
<main class="container conteneur-profil py-5">

    <section class="entete-profil">
        <p class="surtitre-profil">ESPACE PERSONNEL / YALLAQUEST</p>
        <h1 class="titre-profil">Bienvenue, <?= $e($user->getPrenom()) ?></h1>
        <p class="user-space-badge">Rôle : <?= $e($roleLibelle) ?></p>
    </section>

    <section class="carte-profil">
        <table class="user-space-table">
            <caption class="etiquette-carte">INFORMATIONS DU COMPTE</caption>
            <tbody>
                <tr>
                    <th scope="row">ID</th>
                    <td><?= $e($user->getId()) ?></td>
                </tr>
                <tr>
                    <th scope="row">Nom d'utilisateur</th>
                    <td><?= $e($user->getPrenom() . ' ' . $user->getNom()) ?></td>
                </tr>
                <tr>
                    <th scope="row">E-mail</th>
                    <td><?= $e($user->getEmail()) ?></td>
                </tr>
                <tr>
                    <th scope="row">Membre depuis</th>
                    <td>Non renseigné</td>
                </tr>
            </tbody>
        </table>

        <a class="bouton-modifier-profil user-space-logout" href="../../../index.php?action=logout">
            <i class="bi bi-box-arrow-right"></i>
            Se déconnecter
        </a>
    </section>

</main>
<?php
$content = ob_get_clean();
$title = 'Mon espace · YallaQuest';
$styles = ['../../css/user-space.css'];
require __DIR__ . '/../includes/layout.php';
