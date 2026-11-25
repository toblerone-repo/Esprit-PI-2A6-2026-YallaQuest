<?php
// Amine : session sécurisée et expiration après 30 minutes.
/* PARTIE 5.5 (Amine) : configuration avant le démarrage et expiration après 30 minutes.
   Le routeur et les vues directes utilisent le même fichier, une seule fois par requête. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
    session_start();
}
$sessionLifetime = 1800;
if (!defined('YQ_SESSION_DUREE')) {
    define('YQ_SESSION_DUREE', $sessionLifetime);   // lu par le compteur d'inactivité du front-office
}
// Intégration : seule une session CONNECTÉE expire. Un visiteur (page de connexion, inscription)
// garde sa session et donc son jeton CSRF, même s'il met plus longtemps que la durée à remplir le formulaire.
if (isset($_SESSION['last_activity'], $_SESSION['user_id']) && time() - $_SESSION['last_activity'] > $sessionLifetime) {
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
    // Un formulaire envoyé après l'expiration porte l'ancien jeton : au lieu de « Jeton de sécurité
    // invalide », on renvoie vers la connexion avec le message d'inactivité.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' && !headers_sent()) {
        $yqScript = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $yqViews = strpos($yqScript, '/Views/');
        $yqBase = rtrim($yqViews === false ? dirname($yqScript) : substr($yqScript, 0, $yqViews), '/.');
        header('Location: ' . $yqBase . '/index.php?action=login&expire=1', true, 303);
        exit;
    }
}
$_SESSION['last_activity'] = time();
