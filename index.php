<?php
declare(strict_types=1);

session_start();

// En-têtes HTTP de sécurité : partie 7.5 du workshop.
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");

require_once __DIR__ . '/Controllers/AuthController.php';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

$authController = new AuthController();

$action = $_GET['action'] ?? 'home';

switch ($action) {
    // Ajouter les actions et charger leurs vues au fil du workshop.

    default:
        http_response_code(404);
        echo '404 - Page not found';
        break;
}
