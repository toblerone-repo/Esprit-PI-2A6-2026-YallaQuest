<?php
// Mohamed : ajout d'un compte par l'admin. Houssem : CSRF/XSS. Darine : accès admin.
/* =====================================================================
   [DAY 1 · AJOUT UTILISATEUR] Back-office : Inscrire un aventurier
   ---------------------------------------------------------------------
   Règle du workshop (1.3) :
   - Vue = affichage uniquement, aucune requête SQL ici !
   - Toute requête SQL et logique métier sont dans Controllers/utilisateurC.php.
   - Les données utilisateur sont encapsulées dans Models/utilisateur.php.
   - Même charte graphique que listuser.php (yalla_quest back-office).
   - Image par défaut : ?string $imgProfil = 'renard'.
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
$pageTitle = 'Inscrire un aventurier · Administration · Yalla Quest';

$errors = [];
$nom = '';
$prenom = '';
$pseudo = '';
$mail = '';
$role = 'user';
$statut = 'actif';
$imgProfil = 'renard';

// Traitement du formulaire d'ajout
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $nom       = trim((string) ($_POST['nom'] ?? ''));
    $prenom    = trim((string) ($_POST['prenom'] ?? ''));
    $pseudo    = trim((string) ($_POST['pseudo'] ?? ''));
    $mail     = trim((string) ($_POST['mail'] ?? $_POST['email'] ?? ''));
    $mdp       = (string) ($_POST['mot_de_passe'] ?? $_POST['password'] ?? '');
    $confMdp   = (string) ($_POST['confirmer_mot_de_passe'] ?? $_POST['confirm_password'] ?? '');
    $role      = (string) ($_POST['role'] ?? 'user');
    $statut    = (string) ($_POST['statut'] ?? 'actif');
    $imgProfil = !empty($_POST['img_profil']) ? trim((string) $_POST['img_profil']) : 'renard';

    // Validation via le contrôleur (règle MVC : logique dans le contrôleur)
    $errors = $utilisateurC->verifierAjout([
        'nom'                    => $nom,
        'prenom'                 => $prenom,
        'pseudo'                 => $pseudo,
        'mail'                 => $mail,
        'mot_de_passe'           => $mdp,
        'confirmer_mot_de_passe' => $confMdp,
        'role'                   => $role,
        'statut'                 => $statut,
        'img_profil'             => $imgProfil,
    ]);

    if (empty($errors)) {
        // Hachage sécurisé du mot de passe
        $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);

        // Création de l'objet Métier (Modèle) avec l'image par défaut "renard"
        $nouveau = new Utilisateur($nom, $prenom, $mail, $mdpHash, $imgProfil);
        $nouveau->setPseudo($pseudo);
        $nouveau->setRole($role);
        $nouveau->setStatut($statut);
        $nouveau->setXp(0);
        $nouveau->setNiveau(1);

        // Appel de la méthode du contrôleur (SQL exclusivement dans UtilisateurC)
        if ($utilisateurC->addUtilisateur($nouveau)) {
            header('Location: listuser.php?success=user_added');
            exit;
        } else {
            $errors['general'] = "Une erreur est survenue lors de l'enregistrement de l'aventurier.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ajouter un nouvel aventurier dans le système Yalla Quest.">
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
            <h1>Inscrire un aventurier</h1>
            <p class="about">Créer un nouveau compte utilisateur ou administrateur.</p>
        </div>
        <div class="actions">
            <a href="listuser.php" class="btn sm">← Retour à la liste</a>
        </div>
    </header>

    <!-- Affichage des erreurs globales ou serveur -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul>
                <?php foreach ($errors as $champ => $msg): ?>
                    <li><?= htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout dans le panneau style sheet -->
    <section class="form-sheet">
        <form id="addUserForm" method="post" action="addUser.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <div class="form-grid">
                <!-- Prénom -->
                <div class="field">
                    <label for="prenom">Prénom <span class="req">*</span></label>
                    <input class="input" type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Ex : Yassine" required autocomplete="given-name">
                    <?php if (isset($errors['prenom'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['prenom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Nom -->
                <div class="field">
                    <label for="nom">Nom <span class="req">*</span></label>
                    <input class="input" type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Ex : Ben Salem" required autocomplete="family-name">
                    <?php if (isset($errors['nom'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Pseudo -->
                <div class="field full">
                    <label for="pseudo">Pseudo <span class="req">*</span></label>
                    <input class="input" type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($pseudo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Ex : YassineLeLynx" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_\-]{3,50}" autocomplete="nickname">
                    <span class="hint">Unique et visible par les autres aventuriers. 3 à 50 caractères : lettres, chiffres, _ ou -.</span>
                    <?php if (isset($errors['pseudo'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['pseudo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- E-mail -->
                <div class="field full">
                    <label for="mail">Adresse e-mail <span class="req">*</span></label>
                    <input class="input" type="email" id="mail" name="mail" value="<?= htmlspecialchars($mail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="aventurier@yallaquest.tn" required autocomplete="email">
                    <span class="hint">Doit être unique dans tout le système Yalla Quest.</span>
                    <?php if (isset($errors['mail'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['mail'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Mot de passe -->
                <div class="field">
                    <label for="mot_de_passe">Mot de passe <span class="req">*</span></label>
                    <input class="input" type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required autocomplete="new-password">
                    <span class="hint">8 caractères minimum.</span>
                    <?php if (isset($errors['mot_de_passe'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['mot_de_passe'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Confirmer mot de passe -->
                <div class="field">
                    <label for="confirmer_mot_de_passe">Confirmer le mot de passe <span class="req">*</span></label>
                    <input class="input" type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" placeholder="••••••••" required autocomplete="new-password">
                    <?php if (isset($errors['confirmer_mot_de_passe'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['confirmer_mot_de_passe'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Rôle -->
                <div class="field">
                    <label for="role">Rôle attribué <span class="req">*</span></label>
                    <select class="input" id="role" name="role">
                        <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User (Aventurier standard)</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin (Administrateur)</option>
                    </select>
                    <span class="hint">Un administrateur a accès au panneau de gestion back-office.</span>
                    <?php if (isset($errors['role'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Statut -->
                <div class="field">
                    <label for="statut">Statut initial <span class="req">*</span></label>
                    <select class="input" id="statut" name="statut">
                        <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif (Peut se connecter)</option>
                        <option value="bloque" <?= $statut === 'bloque' ? 'selected' : '' ?>>Bloqué (Accès interdit)</option>
                    </select>
                    <span class="hint">Actif par défaut.</span>
                    <?php if (isset($errors['statut'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['statut'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Image de profil par défaut : renard -->
                <div class="field full">
                    <label for="img_profil">Image de profil par défaut</label>
                    <div class="avatar-preview-box">
                        <span class="avatar" aria-hidden="true">🦊</span>
                        <div class="avatar-preview-info">
                            <strong>Avatar Renard (par défaut)</strong>
                            <span>Attribué automatiquement aux nouveaux aventuriers.</span>
                        </div>
                        <input type="hidden" id="img_profil" name="img_profil" value="renard">
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="form-actions full">
                    <button type="submit" class="btn primary" id="btnSubmit">Inscrire l'aventurier</button>
                    <a href="listuser.php" class="btn">Annuler</a>
                </div>
            </div>

        </form>
    </section>

</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

<script>
// Contrôle de validation côté client pour améliorer l'expérience utilisateur
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addUserForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const prenom = document.getElementById('prenom').value.trim();
        const nom = document.getElementById('nom').value.trim();
        const pseudo = document.getElementById('pseudo').value.trim();
        const mail = document.getElementById('mail').value.trim();
        const mdp = document.getElementById('mot_de_passe').value;
        const confMdp = document.getElementById('confirmer_mot_de_passe').value;

        let erreurs = [];

        if (prenom === '') erreurs.push('Le prénom est obligatoire.');
        if (nom === '') erreurs.push('Le nom est obligatoire.');
        if (!/^[a-zA-Z0-9_\-]{3,50}$/.test(pseudo)) {
            erreurs.push('Le pseudo doit contenir de 3 à 50 caractères (lettres, chiffres, _ ou -).');
        }
        if (mail === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) {
            erreurs.push('Veuillez renseigner une adresse e-mail valide.');
        }
        if (mdp.length < 8) {
            erreurs.push('Le mot de passe doit contenir au moins 8 caractères.');
        }
        if (mdp !== confMdp) {
            erreurs.push('Les mots de passe ne correspondent pas.');
        }

        if (erreurs.length > 0) {
            e.preventDefault();
            let alerte = document.querySelector('.alert.alert-danger');
            if (!alerte) {
                alerte = document.createElement('div');
                alerte.className = 'alert alert-danger';
                alerte.setAttribute('role', 'alert');
                form.parentElement.insertAdjacentElement('beforebegin', alerte);
            }
            alerte.innerHTML = '<strong>Veuillez corriger les erreurs suivantes :</strong><ul>' +
                erreurs.map(function(msg) { return '<li>' + msg + '</li>'; }).join('') +
                '</ul>';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
});
</script>

</body>
</html>
