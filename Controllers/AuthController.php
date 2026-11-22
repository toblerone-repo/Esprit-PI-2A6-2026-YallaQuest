<?php
declare(strict_types=1);

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
        if (!self::isAuthenticated()) {
            header('Location: index.php?action=login');
            exit;
        }
    }
}
