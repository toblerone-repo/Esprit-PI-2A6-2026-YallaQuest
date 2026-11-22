<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/utilisateur.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        require_once __DIR__ . '/../config.php';
        $this->db = config::getConnexion();
    }

    public static function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
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

    // Les méthodes d'authentification seront ajoutées dans leur lot.
}
