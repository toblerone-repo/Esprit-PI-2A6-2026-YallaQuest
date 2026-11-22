<?php
/* =====================================================================
   [DARINE · LISTE UTILISATEURS] Back-office : liste des aventuriers
   ---------------------------------------------------------------------
   Vue = affichage uniquement (workshop 1.3) : aucune requête SQL ici,
   les données viennent de Controllers/utilisateurC.php.
   Design repris de YallaQuest1 (page Aventuriers), style dans
   Views/css/utilisateurs-admin.css (CSS externe, aucun style inline).

   ---------------------------------------------------------------------
   [DARINE · GUIDE] POUR LES AUTRES MEMBRES QUI AJOUTENT LEUR PARTIE ICI
   ---------------------------------------------------------------------
   Repérez les commentaires [À COMPLÉTER · partie ...] plus bas :
     - Ajout       : bouton dans <div class="actions"> (classe "btn primary")
     - Recherche   : formulaire juste au-dessus du tableau ; remplacez
                     $liste par le résultat de votre méthode de recherche
                     (elle doit renvoyer des objets Utilisateur).
     - Modifier / Rôle / Bloquer / Supprimer : dans la cellule Actions,
       bloc <?php else: ?> (lignes des AUTRES comptes uniquement ;
       la ligne de l'admin connecté affiche le cadenas).
       Classes prêtes : "btn sm" (Modifier), "btn danger sm" (Supprimer).
     - Avant de supprimer / changer un rôle, appelez côté serveur
       verifierSuppression() / verifierChangementRole() (voir le guide
       en haut de Controllers/utilisateurC.php).
   Variables disponibles dans cette page :
     $utilisateurC, $liste (Utilisateur[]), $totalUsers, $totalAdmins,
     $totalRegular, et dans la boucle : $u (objet Utilisateur), $soiMeme.
     Recherche par ID : $idRecherche (null si pas de recherche), $error.
   ===================================================================== */

// [DARINE · RÈGLE ADMIN] Session nécessaire pour savoir quel admin est connecté.
// (La partie Sécurité remplacera cette ligne par sa session sécurisée.)
session_start();

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// [DARINE · LISTE] Données préparées par le contrôleur
$utilisateurC = new UtilisateurC();
$liste        = $utilisateurC->listUtilisateurs();

// [DARINE · RECHERCHE PAR ID] Traitement du formulaire (comme searchProduits.php)
$error       = "";
$idRecherche = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    if (is_numeric($_POST['id']) && $_POST['id'] > 0) {
        $idRecherche = (int) $_POST['id'];
        $liste = $utilisateurC->rechercherParId($idRecherche);
    } else {
        $error = "Saisissez un ID valide (nombre supérieur à 0).";
    }
}
$totalUsers   = $utilisateurC->countAllUtilisateurs();
$totalAdmins  = $utilisateurC->countByRole('admin');
$totalRegular = $utilisateurC->countByRole('user');

// <head> commun (Atelier Intégration Template)
$pageTitle   = 'Aventuriers · Administration · Yalla Quest';
$currentPage = 'utilisateurs';
require_once __DIR__ . '/../partials/head.php';
?>

<main class="canvas">

    <!-- En-tête de la page -->
    <header class="title-row">
        <div>
            <p class="kicker">Administration · aventuriers</p>
            <h1>Aventuriers</h1>
            <p class="about">Consulter les comptes, les rôles (user / admin) et les statuts.</p>
            <p class="sub">
                <strong><?= $totalUsers ?> aventurier<?= $totalUsers > 1 ? 's' : '' ?></strong>
                · <?= $totalAdmins ?> administrateur<?= $totalAdmins > 1 ? 's' : '' ?>
            </p>
        </div>
        <div class="actions">
            <!-- [À COMPLÉTER · partie Ajout] bouton "Inscrire un aventurier" -->
        </div>
    </header>

    <!-- [DARINE · LISTE] Chiffres clés (workshop 6.2) -->
    <section class="kpis" aria-label="Chiffres clés">
        <div class="kpi">
            <p class="kpi-label"><span class="dot" aria-hidden="true"></span>Comptes</p>
            <p class="kpi-value"><?= $totalUsers ?></p>
        </div>
        <div class="kpi">
            <p class="kpi-label"><span class="dot dot-lilas" aria-hidden="true"></span>Utilisateurs</p>
            <p class="kpi-value"><?= $totalRegular ?></p>
        </div>
        <div class="kpi">
            <p class="kpi-label"><span class="dot dot-or" aria-hidden="true"></span>Administrateurs</p>
            <p class="kpi-value"><?= $totalAdmins ?></p>
        </div>
    </section>

    <!-- [DARINE · RECHERCHE PAR ID] Afficher un seul aventurier à partir de son ID -->
    <form class="search-id" action="" method="POST">
        <label for="id">Rechercher par ID</label>
        <input class="input" type="number" id="id" name="id" min="1" placeholder="Ex. : 3"
               value="<?= $idRecherche !== null ? $idRecherche : '' ?>">
        <input class="btn primary" type="submit" name="search" value="Rechercher">
        <?php if ($idRecherche !== null || $error != ""): ?>
            <a class="btn" href="listuser.php">Afficher tous</a>
        <?php endif; ?>
    </form>
    <?php if ($error != ""): ?>
        <p class="search-error"><?= $error ?></p>
    <?php endif; ?>

    <!-- [À COMPLÉTER · partie Recherche / filtre] formulaire GET : recherche (nom, e-mail) + filtre rôle -->

    <!-- [DARINE · LISTE] Tableau des utilisateurs -->
    <section class="sheet">
        <div class="table-wrap">
            <table class="admin-table">
                <caption class="sr">Liste des aventuriers inscrits</caption>
                <thead>
                    <tr>
                        <th scope="col">Aventurier</th>
                        <th scope="col" class="hide-md">E-mail</th>
                        <th scope="col">Progression</th>
                        <th scope="col">Rôle</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="n">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($liste)): ?>
                    <tr><td colspan="6" class="empty">
                        <?= $idRecherche !== null ? "Aucun aventurier avec l'ID " . $idRecherche . "." : "Aucun aventurier enregistré." ?>
                    </td></tr>
                <?php endif; ?>

                <?php foreach ($liste as $u): /** @var Utilisateur $u */ ?>
                    <?php $soiMeme = UtilisateurC::estSoiMeme($u->getId()); ?>
                    <tr<?= $soiMeme ? ' class="is-me"' : '' ?>>
                        <td>
                            <span class="gcell">
                                <span class="avatar" aria-hidden="true"><?= mb_substr($u->getPrenom(), 0, 1) . mb_substr($u->getNom(), 0, 1) ?></span>
                                <span>
                                    <strong><?= $u->getPrenom() ?> <?= $u->getNom() ?></strong>
                                    <?php if ($soiMeme): ?><span class="you">(vous)</span><?php endif; ?>
                                    <span class="desc"><?= $u->getEmail() ?></span>
                                </span>
                            </span>
                        </td>
                        <td class="hide-md"><?= $u->getEmail() ?></td>
                        <td>
                            Niveau <?= $u->getNiveau() ?>
                            <span class="desc"><?= $u->getTotalXp() ?> XP</span>
                        </td>
                        <td>
                            <span class="pill <?= $u->getRole() === 'admin' ? 'role-admin' : 'role-user' ?>"><?= $u->getRole() === 'admin' ? 'Admin' : 'User' ?></span>
                        </td>
                        <td>
                            <span class="pill <?= $u->getStatut() === 'bloque' ? 'st-bloque' : 'st-actif' ?>"><?= $u->getStatut() === 'bloque' ? 'Bloqué' : 'Actif' ?></span>
                        </td>
                        <td class="n">
                            <span class="row-actions">
                            <?php if ($soiMeme): ?>
                                <!-- [DARINE · RÈGLE ADMIN] Son propre compte : ni suppression ni rétrogradation.
                                     Ce verrou est visuel ; le vrai contrôle est côté serveur dans
                                     UtilisateurC::verifierSuppression() et verifierChangementRole(). -->
                                <span class="pill is-locked" title="Vous ne pouvez pas supprimer ni rétrograder votre propre compte">
                                    <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 6V4a4 4 0 1 1 8 0v2h1a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h2zm2-2a2 2 0 1 1 4 0v2H6V4z"/></svg>
                                    Votre compte
                                </span>
                            <?php else: ?>
                                <!-- [À COMPLÉTER · parties Modifier / Rôle / Bloquer / Supprimer]
                                     Classes prêtes : <a class="btn sm">Modifier</a>, <button class="btn danger sm">Supprimer</button>. -->
                            <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
