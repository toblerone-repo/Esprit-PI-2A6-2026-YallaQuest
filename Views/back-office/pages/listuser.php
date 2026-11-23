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
     $totalRegular, et dans la boucle : $u, $soiMeme, $estAdmin, $estBloque.
   ===================================================================== */

// [DARINE · RÈGLE ADMIN] Session nécessaire pour savoir quel admin est connecté.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

$utilisateurC = new UtilisateurC();

// [DAY 1 · CHANGEMENT DE RÔLE] Traitement du changement de rôle (user <-> admin)
$notificationSuccess = null;
$notificationError = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changer_role') {
    $idCible = (int) ($_POST['id'] ?? 0);
    $nouveauRole = (string) ($_POST['role'] ?? '');
    $roleErrors = $utilisateurC->verifierChangementRole($idCible, $nouveauRole);
    if (empty($roleErrors)) {
        if ($utilisateurC->changerRole($idCible, $nouveauRole)) {
            header('Location: listuser.php?success=role');
            exit;
        } else {
            $notificationError = "Impossible de modifier le rôle de cet aventurier.";
        }
    } else {
        $notificationError = implode(' ', $roleErrors);
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'changer_role' && isset($_GET['id']) && isset($_GET['role'])) {
    $idCible = (int) $_GET['id'];
    $nouveauRole = (string) $_GET['role'];
    $roleErrors = $utilisateurC->verifierChangementRole($idCible, $nouveauRole);
    if (empty($roleErrors)) {
        if ($utilisateurC->changerRole($idCible, $nouveauRole)) {
            header('Location: listuser.php?success=role');
            exit;
        } else {
            $notificationError = "Impossible de modifier le rôle de cet aventurier.";
        }
    } else {
        $notificationError = implode(' ', $roleErrors);
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'user_added') {
        $notificationSuccess = "L'aventurier a été inscrit avec succès !";
    } elseif ($_GET['success'] === 'role') {
        $notificationSuccess = "Le rôle de l'aventurier a été mis à jour avec succès !";
    }
}

// [DARINE · LISTE] Données préparées par le contrôleur
$liste        = $utilisateurC->listUtilisateurs();
$totalUsers   = $utilisateurC->countAllUtilisateurs();
$totalAdmins  = $utilisateurC->countByRole('admin');
$totalRegular = $utilisateurC->countByRole('user');

$currentPage = 'utilisateurs';
$pageTitle = 'Aventuriers · Administration · Yalla Quest';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Liste des aventuriers de Yalla Quest (administration).">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display&display=swap">
    <link rel="stylesheet" href="../../css/utilisateurs-admin.css">
</head>
<body>
<?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php include_once __DIR__ . '/../partials/header.php'; ?>

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
            <!-- [DAY 1 · CHANGEMENT DE RÔLE & AJOUT UTILISATEUR] -->
            <a href="changeRole.php" class="btn sm">Changer un rôle</a>
            <a href="addUser.php" class="btn primary">+ Inscrire un aventurier</a>
        </div>
    </header>

    <?php if (!empty($notificationSuccess)): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars($notificationSuccess) ?></div>
    <?php endif; ?>

    <?php if (!empty($notificationError)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($notificationError) ?></div>
    <?php endif; ?>

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
                    <tr><td colspan="6" class="empty">Aucun aventurier enregistré.</td></tr>
                <?php endif; ?>

                <?php foreach ($liste as $u): /** @var Utilisateur $u */ ?>
                    <?php
                    $soiMeme    = UtilisateurC::estSoiMeme($u->getId());
                    $nomComplet = trim($u->getPrenom() . ' ' . $u->getNom());
                    $initiales  = mb_strtoupper(mb_substr($u->getPrenom(), 0, 1) . mb_substr($u->getNom(), 0, 1));
                    $estAdmin   = $u->getRole() === 'admin';
                    $estBloque  = $u->getStatut() === 'bloque';
                    ?>
                    <tr<?= $soiMeme ? ' class="is-me"' : '' ?>>
                        <td>
                            <span class="gcell">
                                <span class="avatar" aria-hidden="true"><?= $initiales ?></span>
                                <span>
                                    <strong><?= $nomComplet ?></strong>
                                    <?php if ($soiMeme): ?><span class="you">(vous)</span><?php endif; ?>
                                    <span class="desc"><?= $u->getEmail() ?></span>
                                </span>
                            </span>
                        </td>
                        <td class="hide-md"><?= $u->getEmail() ?></td>
                        <td>
                            Niveau <?= $u->getNiveau() ?>
                            <span class="desc"><?= $u->getXp() ?> XP</span>
                        </td>
                        <td>
                            <span class="pill <?= $estAdmin ? 'role-admin' : 'role-user' ?>"><?= $estAdmin ? 'Admin' : 'User' ?></span>
                        </td>
                        <td>
                            <span class="pill <?= $estBloque ? 'st-bloque' : 'st-actif' ?>"><?= $estBloque ? 'Bloqué' : 'Actif' ?></span>
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
                                <!-- [DAY 1 · CHANGEMENT DE RÔLE] changeRole.php par e-mail -->
                                <?php if ($u->getRole() === 'user'): ?>
                                    <a href="changeRole.php?email=<?= urlencode($u->getEmail()) ?>" class="btn sm btn-role-admin" title="Promouvoir cet aventurier en administrateur">
                                        Promouvoir Admin
                                    </a>
                                <?php else: ?>
                                    <form method="post" action="listuser.php" style="display:inline-block; margin:0;" onsubmit="return confirm('Rétrograder cet administrateur en utilisateur standard ?');">
                                        <input type="hidden" name="action" value="changer_role">
                                        <input type="hidden" name="id" value="<?= $u->getId() ?>">
                                        <input type="hidden" name="role" value="user">
                                        <button type="submit" class="btn sm btn-role-user" title="Rétrograder en utilisateur standard">
                                            Rétrograder User
                                        </button>
                                    </form>
                                <?php endif; ?>
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
