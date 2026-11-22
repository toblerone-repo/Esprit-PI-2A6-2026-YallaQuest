<?php
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
            header('Location: index.php?action=dashboard');
            exit;
        }
        $errors = [];
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
            $errors = $authController->login($_POST);
            if (empty($errors)) {
                // L'action admin arrive en Partie 6 : d'ici là, un admin tombe sur la page 404.
                $redirect = ($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard';
                header('Location: index.php?action=' . $redirect);
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
        header('Location: index.php?action=login');
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

    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Page introuvable</title></head>'
           . '<body><h1>404 · Page introuvable</h1><p><a href="index.php">Retour à l\'accueil</a></p></body></html>';
        break;
}
