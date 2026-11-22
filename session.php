<?php
/* PARTIE 5.5 : démarrage sécurisé de la session.
   Inclus une seule fois, tout en haut de index.php : c'est le seul session_start() du routeur.
   Les ini_set() doivent passer AVANT session_start(). */

ini_set('session.use_strict_mode', '1');   // refuse les ID de session non générés par le serveur
ini_set('session.cookie_httponly', '1');   // cookie inaccessible au JavaScript (anti-vol via XSS)
ini_set('session.cookie_samesite', 'Lax'); // limite le CSRF
session_start();

// Expiration par inactivité (30 min)
$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();
