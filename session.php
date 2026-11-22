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
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $sessionLifetime) {
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();
