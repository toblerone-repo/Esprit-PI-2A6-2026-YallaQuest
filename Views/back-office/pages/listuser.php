<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'utilisateurs';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

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
     Recherche : $recherche (nom/e-mail), $roleFiltre, $idRecherche, $error.
   ===================================================================== */

if (session_status() !== PHP_SESSION_ACTIVE) {
    require_once __DIR__ . '/../../../session.php';
}
require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
$utilisateurC = new UtilisateurC();
// Eya : message laissé par le changement de statut.
$messageStatut = $_SESSION['message_statut'] ?? null;
unset($_SESSION['message_statut']);
$totalUsers = $utilisateurC->countAllUtilisateurs();
$totalAdmins = $utilisateurC->countByRole('admin');
$totalRegular = $utilisateurC->countByRole('user');

$error = '';
$recherche = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$roleFiltre = is_string($_GET['role'] ?? null) ? $_GET['role'] : '';
$idSaisi = is_string($_GET['id'] ?? null) ? trim($_GET['id']) : '';
$idRecherche = null;

if (!in_array($roleFiltre, ['', 'admin', 'user'], true)) {
    $roleFiltre = '';
    $error = 'Choisissez un rôle valide.';
}
if ($idSaisi !== '') {
    $idRecherche = filter_var($idSaisi, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($idRecherche === false) {
        $idRecherche = null;
        $error = 'Saisissez un ID valide (nombre supérieur à 0).';
    }
}

$liste = $utilisateurC->rechercherUtilisateurs($recherche, $roleFiltre, $idRecherche);
$filtresActifs = $recherche !== '' || $roleFiltre !== '' || $idRecherche !== null;

$pageTitle = 'Aventuriers · Administration · Yalla Quest';
$title = $pageTitle;
$currentPage = 'utilisateurs';
require_once __DIR__ . '/../partials/head.php';
?>
<link rel="stylesheet" href="../../css/utilisateurs-admin.css">

<main class="canvas">

    <!-- En-tête de la page -->
    <header class="title-row">
        <div>
            <p class="kicker">Administration · aventuriers</p>
            <h1>Aventuriers</h1>
            <p class="about">Consulter les comptes, les rôles (user / admin) et les statuts.</p>
            <p class="sub">
                <strong><?= htmlspecialchars((string) ($totalUsers), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> aventurier<?= $totalUsers > 1 ? 's' : '' ?></strong>
                · <?= htmlspecialchars((string) ($totalAdmins), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> administrateur<?= $totalAdmins > 1 ? 's' : '' ?>
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
            <p class="kpi-value"><?= htmlspecialchars((string) ($totalUsers), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        </div>
        <div class="kpi">
            <p class="kpi-label"><span class="dot dot-lilas" aria-hidden="true"></span>Utilisateurs</p>
            <p class="kpi-value"><?= htmlspecialchars((string) ($totalRegular), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        </div>
        <div class="kpi">
            <p class="kpi-label"><span class="dot dot-or" aria-hidden="true"></span>Administrateurs</p>
            <p class="kpi-value"><?= htmlspecialchars((string) ($totalAdmins), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        </div>
    </section>

    <!-- Recherche par nom/e-mail, filtre par rôle et recherche facultative par ID. -->
    <form class="search-id user-filters" action="listuser.php" method="GET">
        <div class="filter-field search-field">
            <label for="q">Nom ou e-mail</label>
            <input class="input" type="search" id="q" name="q" placeholder="Nom, prénom ou e-mail"
                   value="<?= htmlspecialchars($recherche, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        </div>
        <div class="filter-field">
            <label for="role">Rôle</label>
            <select class="input" id="role" name="role">
                <option value=""<?= $roleFiltre === '' ? ' selected' : '' ?>>Tous les rôles</option>
                <option value="user"<?= $roleFiltre === 'user' ? ' selected' : '' ?>>Utilisateur</option>
                <option value="admin"<?= $roleFiltre === 'admin' ? ' selected' : '' ?>>Administrateur</option>
            </select>
        </div>
        <div class="filter-field id-field">
            <label for="id">ID (facultatif)</label>
            <input class="input" type="number" id="id" name="id" min="1" placeholder="Ex. : 3"
                   value="<?= $idRecherche !== null ? $idRecherche : '' ?>">
        </div>
        <div class="filter-actions">
            <button class="btn primary" type="submit" name="search" value="1">Rechercher</button>
            <?php if ($filtresActifs || $error !== ''): ?>
                <a class="btn" href="listuser.php">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
    <?php if ($error !== ''): ?>
        <p class="search-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
    <p class="search-results" role="status">
        <?= htmlspecialchars((string) (count($liste)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> résultat<?= count($liste) > 1 ? 's' : '' ?> sur <?= htmlspecialchars((string) ($totalUsers), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> aventuriers
    </p>

    <?php if ($messageStatut): ?>
    <!-- Eya : résultat du blocage/déblocage. -->
    <p class="search-error" role="status"><?= htmlspecialchars($messageStatut['texte'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
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
                        <?= $filtresActifs ? "Aucun aventurier ne correspond aux filtres." : "Aucun aventurier enregistré." ?>
                    </td></tr>
                <?php endif; ?>

                <?php foreach ($liste as $u): /** @var Utilisateur $u */ ?>
                    <?php $soiMeme = UtilisateurC::estSoiMeme($u->getId()); ?>
                    <tr<?= $soiMeme ? ' class="is-me"' : '' ?>>
                        <td>
                            <span class="gcell">
                                <span class="avatar" aria-hidden="true"><?= htmlspecialchars(mb_substr($u->getPrenom(), 0, 1) . mb_substr($u->getNom(), 0, 1), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                <span>
                                    <strong><?= htmlspecialchars($u->getPrenom(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> <?= htmlspecialchars($u->getNom(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                                    <?php if ($soiMeme): ?><span class="you">(vous)</span><?php endif; ?>
                                    <span class="desc"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </span>
                            </span>
                        </td>
                        <td class="hide-md"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td>
                            Niveau <?= htmlspecialchars((string) ($u->getNiveau()), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            <span class="desc"><?= htmlspecialchars((string) ($u->getXp()), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> XP</span>
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
                                <!-- Eya : bloquer/débloquer, avec token de Houssem. -->
                                <form method="post" action="changerStatut.php" class="form-supprimer">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                                    <button type="submit" class="btn sm" name="action" value="<?= $u->getStatut() === 'bloque' ? 'debloquer' : 'bloquer' ?>"><?= $u->getStatut() === 'bloque' ? 'Débloquer' : 'Bloquer' ?></button>
                                </form>

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
