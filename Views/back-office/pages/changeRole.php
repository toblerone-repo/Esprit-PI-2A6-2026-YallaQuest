<?php
// Mohamed : changement de rôle par e-mail. Houssem : CSRF/XSS. Darine : accès admin.
/* =====================================================================
   [DAY 1 · CHANGEMENT DE RÔLE] Back-office : Modifier le rôle (changeRole.php)
   ---------------------------------------------------------------------
   Règles de gestion :
   - Recherche et modification du rôle PAR ADRESSE E-MAIL (non par ID).
   - Même charte graphique que addUser.php et listuser.php.
   - Si changement sur un e-mail d'un admin : affiche une erreur.
   - Si l'e-mail n'existe pas : affiche une erreur.
   - Sinon : affiche valide et le rôle de l'utilisateur devient 'admin'.
   - Architecture MVC : requêtes SQL dans Controllers/utilisateurC.php.
   ===================================================================== */

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
require_once __DIR__ . '/../../../Models/utilisateur.php';

$utilisateurC = new UtilisateurC();

$currentPage = 'utilisateurs';
$pageTitle = 'Changer le rôle par e-mail · Administration · Yalla Quest';

$messageError   = null;
$messageValide  = null;
$cible          = null;
$emailSaisie    = null;

// Récupération de l'e-mail via POST ou GET
if (!empty($_POST['email'])) {
    $emailSaisie = trim((string) $_POST['email']);
} elseif (!empty($_POST['mail'])) {
    $emailSaisie = trim((string) $_POST['mail']);
} elseif (!empty($_GET['email'])) {
    $emailSaisie = trim((string) $_GET['email']);
} elseif (!empty($_GET['mail'])) {
    $emailSaisie = trim((string) $_GET['mail']);
} elseif (!empty($_GET['id'])) {
    // Rétrocompatibilité : si un ancien lien transmet un id, on retrouve son e-mail
    $userTmp = $utilisateurC->getUtilisateur((int) $_GET['id']);
    if ($userTmp !== null) {
        $emailSaisie = $userTmp->getEmail();
    }
}

if ($emailSaisie !== null) {
    if (empty($emailSaisie) || !filter_var($emailSaisie, FILTER_VALIDATE_EMAIL)) {
        $messageError = "Erreur : Veuillez saisir une adresse e-mail valide.";
    } else {
        // 1. Vérification de l'existence de l'e-mail
        $cible = $utilisateurC->getUtilisateurParEmail($emailSaisie);

        if ($cible === null) {
            // L'adresse e-mail n'existe pas en base
            $messageError = "Erreur : Aucun utilisateur trouvé avec l'adresse e-mail « " . htmlspecialchars($emailSaisie, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " » (cette adresse n'existe pas).";
        } elseif ($cible->getRole() === 'admin') {
            // L'utilisateur est déjà admin
            $messageError = "Erreur : L'utilisateur " . htmlspecialchars($cible->getPrenom() . ' ' . $cible->getNom(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (« " . htmlspecialchars($emailSaisie, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " ») possède déjà le rôle Administrateur. Le changement de rôle est impossible.";
        } elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            // Intégration : un lien (GET) affiche seulement le compte ; la promotion passe par le formulaire POST + jeton.
        } else {
            // 2. L'utilisateur existe et n'est pas admin : promotion en admin
            $updateOk = $utilisateurC->changerRole($cible->getId(), 'admin');

            if ($updateOk) {
                // Rafraîchir l'objet pour l'affichage à jour
                $cible = $utilisateurC->getUtilisateur($cible->getId());
                $messageValide = "Valide : Le rôle de l'utilisateur " . htmlspecialchars($cible->getPrenom() . ' ' . $cible->getNom(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (« " . htmlspecialchars($emailSaisie, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " ») a été modifié avec succès. Son rôle est désormais Administrateur.";
            } else {
                $messageError = "Erreur : Une erreur est survenue lors de la mise à jour du rôle en base de données.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Changer le rôle d'un aventurier par son adresse e-mail.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
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
            <h1>Changer le rôle</h1>
            <p class="about">Promouvoir un utilisateur standard au rang d'administrateur par son adresse e-mail.</p>
        </div>
        <div class="actions">
            <a href="listuser.php" class="btn sm">← Retour à la liste</a>
        </div>
    </header>

    <!-- Affichage des messages selon les règles demandées -->
    <?php if (!empty($messageError)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $messageError ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($messageValide)): ?>
        <div class="alert alert-success" role="status">
            <?= $messageValide ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire dans le panneau style sheet (même charte que addUser.php) -->
    <section class="form-sheet">
        <form method="post" action="changeRole.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <div class="form-grid">
                
                <div class="field full">
                    <label for="email">Adresse e-mail de l'utilisateur à promouvoir en Admin <span class="req">*</span></label>
                    <input class="input" type="email" id="email" name="email" value="<?= htmlspecialchars((string) ($emailSaisie ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="aventurier@yallaquest.tn" required autofocus autocomplete="email">
                    <span class="hint">Saisissez l'adresse e-mail exacte de l'aventurier dont vous souhaitez passer le rôle à <strong>Admin</strong>.</span>
                </div>

                <!-- Boutons d'action -->
                <div class="form-actions full">
                    <button type="submit" class="btn primary">Promouvoir en Administrateur</button>
                    <a href="listuser.php" class="btn">Annuler</a>
                </div>

            </div>
        </form>

        <!-- Fiche descriptive de l'aventurier ciblé (si trouvé) -->
        <?php if ($cible !== null): ?>
            <?php
            $initiales = mb_strtoupper(mb_substr($cible->getPrenom(), 0, 1) . mb_substr($cible->getNom(), 0, 1));
            $estAdmin  = $cible->getRole() === 'admin';
            $estBloque = $cible->getStatut() === 'bloque';
            ?>
            <div style="margin-top: 28px; padding: 20px; background: #000000; border: 1px solid var(--bordure); border-radius: 12px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div class="gcell">
                    <span class="avatar"><?= htmlspecialchars($initiales, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <div>
                        <strong><?= htmlspecialchars($cible->getPrenom() . ' ' . $cible->getNom(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                        <span class="desc" style="display:block;"><?= htmlspecialchars($cible->getEmail(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="pill <?= $estAdmin ? 'role-admin' : 'role-user' ?>">
                        Rôle : <?= $estAdmin ? 'Admin' : 'User' ?>
                    </span>
                    <span class="pill <?= $estBloque ? 'st-bloque' : 'st-actif' ?>">
                        Statut : <?= $estBloque ? 'Bloqué' : 'Actif' ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

    </section>

</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
