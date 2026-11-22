<?php
declare(strict_types=1);

/* PARTIE 5.2 : point d'entrée unique (routeur) : index.php?action=...
   Sans action, l'adresse racine ouvre toujours l'accueil du front-office.
   Chemins relatifs : fonctionne aussi dans un sous-dossier (ex. http://localhost/integration/).
   Back-office : Views/back-office/pages/liste_utilisateurs.php (barre latérale vers les autres pages). */

require __DIR__ . '/session.php';                       // Partie 5.5 : seul session_start()
require __DIR__ . '/Controllers/AuthController.php';    // Partie 5.1

// Les vues appelées par ce routeur vérifient cette constante (accès direct interdit).
define('YQ_ROUTEUR', true);

$authController = new AuthController();
$action = (string) ($_GET['action'] ?? 'accueil');

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
            $email = trim((string) ($_POST['email'] ?? ''));
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
        $authController->logout();
        header('Location: index.php?action=login');
        exit;

    // ---------------- USER SPACE (role: user - frontoffice) ----------------
    case 'dashboard':
        AuthController::requireAuth();
        $user = $authController->findById((int) $_SESSION['user_id']);
        if (!$user) {               // compte supprimé alors que la session est encore active
            $authController->logout();
            header('Location: index.php?action=login');
            exit;
        }
        require __DIR__ . '/Views/front-office/pages/user_space.php';
        break;

    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Page introuvable</title></head>'
           . '<body><h1>404 · Page introuvable</h1><p><a href="index.php">Retour à l\'accueil</a></p></body></html>';
        break;
}
