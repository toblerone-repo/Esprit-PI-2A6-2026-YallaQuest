<?php
// Amine : routeur. Houssem : en-têtes et CSRF. Intégration : raccordements des vues.
declare(strict_types=1);

/* PARTIE 5.2 : point d'entrée unique (routeur) : index.php?action=...
   Sans action, l'adresse racine ouvre toujours l'accueil du front-office.
   Chemins relatifs : fonctionne aussi dans un sous-dossier (ex. http://localhost/integration/).
   Back-office : Views/back-office/pages/liste_utilisateurs.php (barre latérale vers les autres pages). */

require_once __DIR__ . '/session.php';                       // Partie 5.5 : seul session_start()
require_once __DIR__ . '/Controllers/AuthController.php';

// En-têtes HTTP de sécurité de Houssem, adaptés aux ressources des six templates.
$cspNonce = bin2hex(random_bytes(16));
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$cspNonce' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com; script-src-attr 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
$csrfToken = AuthController::getCsrfToken();
// Intégration : le compteur d'Amine envoie la déconnexion automatique en POST. Si le serveur a déjà
// fermé la session, son ancien jeton n'est plus valide : personne n'est connecté, on affiche la connexion.
if (($_GET['action'] ?? null) === 'logout' && isset($_GET['expire']) && !AuthController::isAuthenticated()) {
    header('Location: index.php?action=login&expire=1');
    exit;
}
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}


// Les vues appelées par ce routeur vérifient cette constante (accès direct interdit).
define('YQ_ROUTEUR', true);

$authController = new AuthController();
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : 'accueil';

switch ($action) {
    // ---------------- ACCUEIL ----------------
    case 'accueil':
        header('Location: Views/front-office/pages/quetes-programmation.php');
        exit;

    // ---------------- LOGIN ----------------
    case 'login':
        if (AuthController::isAuthenticated()) {
            // Déjà connecté : l'admin retourne au back-office, l'utilisateur à son espace.
            header('Location: ' . (($_SESSION['role'] ?? '') === 'admin' ? 'Views/back-office/pages/listuser.php' : 'index.php?action=dashboard'));
            exit;
        }
        $errors = [];
        $email = '';
        // Amine : arrivée depuis le compteur d'inactivité (Views/js/session-timeout.js) : la vue affiche un message.
        $sessionExpiree = isset($_GET['expire']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
            $errors = $authController->login($_POST);
            if (empty($errors)) {
                // Admin : directement au back-office (liste des aventuriers) ; utilisateur : son espace.
                if (($_SESSION['role'] ?? '') === 'admin') {
                    header('Location: Views/back-office/pages/listuser.php');
                } else {
                    header('Location: index.php?action=dashboard');
                }
                exit;
            }
        }
        require __DIR__ . '/Views/front-office/pages/login.php';
        break;

    // ---------------- LOGOUT ----------------
    case 'logout':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405); header('Allow: POST'); exit('La déconnexion doit être envoyée par POST.');
        }
        $authController->logout();
        // Amine : &expire=1 : déconnexion automatique par le compteur d'inactivité.
        header('Location: index.php?action=login' . (isset($_GET['expire']) ? '&expire=1' : ''));
        exit;

    // ---------------- PING (compteur d'inactivité) ----------------
    // Amine : bouton « Rester connecté » du compteur. session.php vient déjà de remettre
    // last_activity à maintenant, il suffit donc de répondre si la session est encore valide.
    case 'ping':
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        if (!AuthController::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['connecte' => false]);
            exit;
        }
        echo json_encode(['connecte' => true, 'duree' => $sessionLifetime]);
        exit;

    // ---------------- USER SPACE (role: user - frontoffice) ----------------
    case 'dashboard':
        AuthController::requireRole('user');
        $user = $authController->findById((int) $_SESSION['user_id']);
        if (!$user) {               // compte supprimé alors que la session est encore active
            $authController->logout();
            header('Location: index.php?action=login');
            exit;
        }
        require __DIR__ . '/Views/front-office/pages/user_space.php';
        break;

    case 'admin':
        AuthController::requireRole('admin');
        header('Location: Views/back-office/pages/listuser.php');
        exit;

    case 'home':
        $destination = AuthController::isAuthenticated() ? (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard') : 'login';
        header('Location: index.php?action=' . $destination);
        exit;

    // Eya : inscription conservée dans join_now.php et UtilisateurC.
    case 'register':
        header('Location: Views/front-office/pages/join_now.php', true, ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 307 : 302);
        exit;

    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Page introuvable</title></head>'
           . '<body><h1>404 · Page introuvable</h1><p><a href="index.php">Retour à l\'accueil</a></p></body></html>';
        break;
}
