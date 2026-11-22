<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'modifier-profil';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode('Jeton de sécurité invalide. Rechargez la page.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}
AuthController::requireAuth();


if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../../session.php';
}

$id = $_SESSION['user_id'] ?? null;
if ($id === null) {
     exit('Connexion requise.');
}

//$id = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-type: application/json');
}

// $ancienUtilisateur = [
//     'prenom' => 'Camille',
//     'nom' => 'Martin',
//     'mail' => 'camille.demo@gmail.com',
//     'mot_de_passe_hash' => password_hash('MotDePasseDemo123!', PASSWORD_DEFAULT),
//     'derniere_prolongation_at' => '2026-11-21',
//     'img_profil' => 'chat',
//     'chemin' => '../../img/avatars/chat.png',
// ];

// $images = [
//     ['id' => 'renard', 'chemin' => '../../img/avatars/renard.png'],
//     ['id' => 'chat', 'chemin' => '../../img/avatars/chat.png'],
//     ['id' => 'grenouille', 'chemin' => '../../img/avatars/grenouille.png'],
//     ['id' => 'hibou', 'chemin' => '../../img/avatars/hibou.png'],
//     ['id' => 'lapin', 'chemin' => '../../img/avatars/lapin.png'],
//     ['id' => 'panda', 'chemin' => '../../img/avatars/panda.png'],
//     ['id' => 'loutre', 'chemin' => '../../img/avatars/loutre.png'],
// ];

include_once __DIR__ . '/../../../Controllers/utilisateurC.php';
$utilisateurC = new UtilisateurC();
$ancienUtilisateur = $utilisateurC->getProfilUtilisateur((int) $id);
$images = $utilisateurC->listerImagesProfil();
if (!$ancienUtilisateur) { http_response_code(404); exit('Compte introuvable.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nom', 'prenom', 'email'] as $field) {
        if (!is_string($_POST[$field] ?? null)) { http_response_code(400); echo json_encode('Données invalides.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); exit; }
    }
    $hash = $ancienUtilisateur['mot_de_passe_hash'];

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $nom = $_POST['nom'];
    $ancien_mot_de_passe = $_POST['ancien-mot-de-passe'] ?? '';
    $nouveau_mp = $_POST['nouveau-mot-de-passe'] ?? '';
    $confirmation_mp = $_POST['confirmer-mot-de-passe'] ?? '';
    $changerMotDePasse = $ancien_mot_de_passe !== '' || $nouveau_mp !== '' || $confirmation_mp !== '';

    if ($changerMotDePasse) {
        if (!is_string($ancien_mot_de_passe) || $ancien_mot_de_passe === '' ||
            !is_string($nouveau_mp) || strlen(trim($nouveau_mp)) < 8 || $nouveau_mp !== $confirmation_mp) {
            echo json_encode("Données invalides.", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit();
        }

        $resultat = password_verify($ancien_mot_de_passe, $hash);

        if (!$resultat) {
            echo json_encode(false, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit();
        }
    }

    if (empty(trim($nom)) || empty(trim($prenom)) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode("Données invalides.", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit();
    }
    $imgProfil = $_POST['img_profil'] ?? $ancienUtilisateur['img_profil'] ?? '';
    $id_images = array_map('strval', array_column($images, 'id'));

    if (!is_string($imgProfil) || ($imgProfil !== '' && !in_array($imgProfil, $id_images, true))) {
        echo json_encode('Imadge de profil invalide.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit();
    }

    $nouveau_hash = $changerMotDePasse ? password_hash($nouveau_mp, PASSWORD_DEFAULT) : $hash;

    $utilisateur = new Utilisateur($nom, $prenom, $email, $nouveau_hash, $imgProfil, $id);
    try {
        $resultat = $utilisateurC->updateProfilUtilisateur($utilisateur, (int) $id, $ancien_mot_de_passe);
    } catch (PDOException $e) {
        error_log('Modification du profil : ' . $e->getMessage());
        http_response_code($e->getCode() === '23000' ? 409 : 500);
        echo json_encode($e->getCode() === '23000' ? 'Cette adresse e-mail est déjà utilisée.' : 'La modification a échoué.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    echo json_encode($resultat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit();
};

$pageTitle = 'Modifier mon profil · Yalla Quest';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php $currentPage = 'modifier-profil'; $pageTitle = $pageTitle ?? 'Mon profil | YallaQuest'; require_once __DIR__ . '/../includes/head.php'; ?>
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
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div class="field-grid">
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <input class="input" type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($ancienUtilisateur['prenom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <span></span>
                    </div>
                    <div class="field">
                        <label for="nom">Nom</label>
                        <input class="input" type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($ancienUtilisateur['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <span></span>
                    </div>
                    <div class="field full">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" value="<?php echo htmlspecialchars($ancienUtilisateur['mail'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <span></span>
                    </div>
                    <fieldset class="choix-avatars full">
                        <legend>Image de profil</legend>
                        <div class="grille-avatars">
                            <?php foreach ($images ?? [] as $image) {
                                $idImage = (string) $image['id'];
                                $estSelectionnee = $idImage === ($ancienUtilisateur['img_profil'] ?? '');
                            ?>
                                <label class="option-avatar">
                                    <input type="radio" name="img_profil"
                                        value="<?= htmlspecialchars($idImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        aria-label="<?= htmlspecialchars('Image de profil ' . $idImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        <?= $estSelectionnee ? 'checked' : '' ?>>
                                    <img src="<?= htmlspecialchars($image['chemin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        alt="" width="64" height="64">
                                </label>
                            <?php } ?>
                        </div>
                    </fieldset>
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

    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/regex.js"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/profil.js"></script>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
