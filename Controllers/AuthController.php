<?php
declare(strict_types=1);

/*
 * GUIDE D'INTEGRATION - SECURITE (CSRF ET XSS)
 *
 * Ce fichier contient les deux fonctions CSRF communes aux modules du projet.
 * Pour les utiliser dans vos pages, adaptez le chemin du require_once vers
 * Controllers/AuthController.php. Il est inutile de creer un autre controleur
 * de securite ou de modifier la structure MVC pour reprendre cette protection.
 *
 * 1. PREPARER LA SESSION ET LE JETON AVANT TOUT AFFICHAGE
 *    $csrfToken = AuthController::getCsrfToken();
 *    Cette fonction demarre la session si necessaire et genere un jeton aleatoire
 *    avec random_bytes(32), conserve dans $_SESSION['csrf_token']. Elle reutilise
 *    ensuite le meme jeton de session : ne le regenerez pas a chaque formulaire.
 *
 * 2. VERIFIER LE JETON AVANT DE TRAITER UNE ACTION QUI MODIFIE DES DONNEES
 *    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET',
 *        ['POST', 'PUT', 'PATCH', 'DELETE'], true)
 *        && !AuthController::verifyCsrfToken(
 *            $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
 *        http_response_code(403);
 *        exit('Jeton de securite invalide. Rechargez la page.');
 *    }
 *    Placez ce controle avant vos INSERT, UPDATE, DELETE ou autres actions.
 *    verifyCsrfToken() attend une session deja ouverte : appelez getCsrfToken()
 *    avant ce controle, ou ouvrez la session dans votre point d'entree.
 *    Elle refuse un jeton absent, vide ou non textuel, puis le compare au jeton
 *    de session avec hash_equals(). Si le controle echoue, arretez le traitement.
 *    Les actions qui changent des donnees doivent passer par une methode protegee,
 *    par exemple un formulaire POST, et ne doivent pas etre declenchees par GET.
 *
 * 3. TRANSMETTRE LE JETON AVEC CHAQUE FORMULAIRE OU REQUETE JAVASCRIPT
 *    Dans chaque formulaire POST, ajoutez ce champ a l'interieur du formulaire :
 *    <input type="hidden" name="csrf_token"
 *           value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
 *    FormData(formulaire) inclut ce champ automatiquement. Si vous construisez
 *    vous-meme la requete, envoyez csrf_token avec les donnees du formulaire ou
 *    envoyez le jeton dans l'en-tete X-CSRF-Token. Le navigateur doit aussi
 *    transmettre le cookie de la meme session. Pour PUT, PATCH ou DELETE, utilisez
 *    l'en-tete : PHP ne remplit pas automatiquement $_POST pour ces methodes.
 *
 * 4. PROTEGER LES AFFICHAGES CONTRE XSS DANS VOS VUES
 *    Pour afficher une valeur dans le texte HTML ou un attribut entre guillemets :
 *    htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
 *    Appliquez cela aux noms, messages, titres et autres donnees dynamiques,
 *    meme lorsqu'elles viennent de la base. Faites-le au moment de l'affichage,
 *    sans transformer les valeurs en entites HTML avant de les enregistrer.
 *    Pour inserer une valeur PHP dans du JavaScript, utilisez json_encode()
 *    avec JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT.
 *    Pour afficher du texte avec JavaScript, utilisez textContent.
 *
 * Le CSRF ne verifie pas les droits : chaque action doit aussi verifier
 * l'utilisateur connecte et ses autorisations dans le module concerne.
 * Les en-tetes HTTP de securite sont deja declares dans index.php.
 * Les methodes d'authentification de ce controleur restent a integrer dans leur lot.
 */

/* PARTIE 5.1 : connexion, déconnexion et espace utilisateur.
   Toute la logique est ici : requêtes préparées, password_verify, session.
   Le modèle (Models/utilisateur.php) ne fait que porter les données.
   Utilisé par le routeur index.php (actions login, logout, dashboard). */

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../Models/utilisateur.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // Vrai si un utilisateur est connecté dans la session en cours.
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // ==============================================================
    // LOGIN
    // ==============================================================
    private function findByEmail(string $email): ?Utilisateur
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $email]);
        $row = $stmt->fetch();
        return $row ? Utilisateur::fromArray($row) : null;
    }

    // Renvoie la liste des erreurs ; tableau vide = connexion réussie.
    public function login(array $post): array
    {
        $errors = [];
        if (!self::verifyCsrfToken($post['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            return ['Jeton de sécurité invalide. Rechargez la page.'];
        }
        if (!is_string($post['email'] ?? null) || !is_string($post['mot_de_passe'] ?? null)) {
            return ['Données de connexion invalides.'];
        }
        $email    = trim((string) ($post['email'] ?? ''));
        $password = (string) ($post['mot_de_passe'] ?? '');

        if ($email === '' || $password === '') {
            $errors[] = 'Saisis ton e-mail et ton mot de passe.';
            return $errors;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = "L'adresse e-mail n'est pas valide.";
            return $errors;
        }

        $user = $this->findByEmail($email);

        // Message volontairement générique : ne jamais révéler si l'e-mail existe
        if (!$user || !password_verify($password, $user->getMp())) {
            $errors[] = 'E-mail ou mot de passe incorrect.';
            return $errors;
        }

        // Compte bloqué (colonne statut) : annoncé seulement après un mot de passe correct
        if ($user->getStatut() === 'bloque') {
            $errors[] = 'Ce compte est bloqué.';
            return $errors;
        }

        // Protection contre la fixation de session
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->getId();
        $_SESSION['username']      = $user->getPrenom() . ' ' . $user->getNom();
        $_SESSION['role']          = $user->getRole();
        $_SESSION['last_activity'] = time();
        $_SESSION['utilisateur'] = ['id' => $user->getId(), 'id_utilisateur' => $user->getId(), 'nom' => $user->getNom(), 'prenom' => $user->getPrenom(), 'mail' => $user->getEmail(), 'role' => $user->getRole()];
        unset($_SESSION['csrf_token']);

        return [];
    }

    // ==============================================================
    // LOGOUT
    // ==============================================================
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    // ==============================================================
    // USER SPACE (frontoffice)
    // ==============================================================
    public function findById(int $id): ?Utilisateur
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Utilisateur::fromArray($row) : null;
    }

    public static function requireAuth(): void
    {
        self::getCsrfToken();
        if (!self::isAuthenticated()) {
            header('Location: ' . self::basePath() . '/index.php?action=login');
            exit;
        }
        $auth = new self();
        $user = $auth->findById((int) $_SESSION['user_id']);
        if (!$user || $user->getStatut() === 'bloque') {
            $auth->logout();
            header('Location: ' . self::basePath() . '/index.php?action=login');
            exit;
        }
        $_SESSION['role'] = $user->getRole();
        $_SESSION['utilisateur']['id'] = $user->getId();
        $_SESSION['utilisateur']['role'] = $user->getRole();
        $_SESSION['utilisateur']['id_utilisateur'] = $user->getId();
    }
    public static function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            require_once __DIR__ . '/../session.php';
        }
        if (!is_string($_SESSION['csrf_token'] ?? null) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function verifyCsrfToken($token): bool
    {
        $expected = $_SESSION['csrf_token'] ?? null;
        return is_string($token) && $token !== '' && is_string($expected) && $expected !== ''
            && hash_equals($expected, $token);
    }
    // Chemin du routeur, pour conserver les liens quand une vue est ouverte directement.
    public static function basePath(): string
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $views = strpos($script, '/Views/');
        return rtrim($views === false ? dirname($script) : substr($script, 0, $views), '/.');
    }

    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['role'] ?? '') === $role;
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (!self::hasRole($role)) {
            http_response_code(403);
            exit('Accès refusé : permissions insuffisantes.');
        }
    }

}
