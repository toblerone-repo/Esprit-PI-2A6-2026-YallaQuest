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
     - Modifier / Rôle / Bloquer : dans la cellule Actions,
       bloc <?php else: ?> (lignes des AUTRES comptes uniquement ;
       la ligne de l'admin connecté affiche le cadenas).
       Classe prête : "btn sm" (Modifier).
     - Supprimer : fait ([SUPPRESSION]), le bouton envoie l'id à supprimer_utilisateur.php,
       qui appelle UtilisateurC::verifierSuppression() puis deleteUtilisateur().
   Variables disponibles dans cette page :
     $utilisateurC, $liste (Utilisateur[]), $totalUsers, $totalAdmins,
     $totalRegular, et dans la boucle : $u, $soiMeme, $estAdmin, $estBloque.
   ===================================================================== */

// [DARINE · RÈGLE ADMIN] Session nécessaire pour savoir quel admin est connecté.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// [SUPPRESSION] Message laissé par supprimer_utilisateur.php, affiché une seule fois.
$flash = $_SESSION['flash_liste_utilisateurs'] ?? null;
unset($_SESSION['flash_liste_utilisateurs']);

// [DARINE · LISTE] Données préparées par le contrôleur
$utilisateurC = new UtilisateurC();
$liste      = $utilisateurC->listUtilisateurs();
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
            <!-- [À COMPLÉTER · partie Ajout] bouton "Inscrire un aventurier" -->
        </div>
    </header>

    <!-- [SUPPRESSION] Résultat de la dernière suppression -->
    <?php if ($flash): ?>
        <p class="notice notice-<?= $flash['type'] === 'succes' ? 'succes' : 'erreur' ?>" role="<?= $flash['type'] === 'succes' ? 'status' : 'alert' ?>">
            <?= htmlspecialchars($flash['texte'], ENT_QUOTES, 'UTF-8') ?>
        </p>
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
                                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8') ?></span>
                                <span>
                                    <strong><?= htmlspecialchars($nomComplet, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($soiMeme): ?><span class="you">(vous)</span><?php endif; ?>
                                    <span class="desc"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </span>
                        </td>
                        <td class="hide-md"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
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
                                     Ce verrou est visuel ; pour la suppression, le serveur le vérifie aussi
                                     (UtilisateurC::verifierSuppression). -->
                                <span class="pill is-locked" title="Vous ne pouvez pas supprimer ni rétrograder votre propre compte">
                                    <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 6V4a4 4 0 1 1 8 0v2h1a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h2zm2-2a2 2 0 1 1 4 0v2H6V4z"/></svg>
                                    Votre compte
                                </span>
                            <?php else: ?>
                                <!-- [À COMPLÉTER · parties Modifier / Rôle / Bloquer]
                                     Classe prête : <a class="btn sm">Modifier</a>. -->

                                <!-- [SUPPRESSION] Envoie l'id à supprimer_utilisateur.php, après confirmation. -->
                                <form class="form-supprimer" method="post" action="supprimer_utilisateur.php"
                                      onsubmit="return confirm('Supprimer définitivement ce compte ? Cette action est irréversible.');">
                                    <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                                    <button type="submit" class="btn danger sm" aria-label="Supprimer <?= htmlspecialchars($nomComplet, ENT_QUOTES, 'UTF-8') ?>">Supprimer</button>
                                </form>
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
