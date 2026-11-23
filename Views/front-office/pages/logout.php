<?php
// Mohamed : bouton de déconnexion (Front-office). Amine : fin de session. Houssem : POST + jeton CSRF.
/* =====================================================================
   [WORKSHOP 5.1 & 5.2] Déconnexion (Logout)
   ---------------------------------------------------------------------
   Détruit la session côté serveur, expire le cookie PHPSESSID et
   redirige vers la page de connexion. Comme le routeur (index.php?action=logout),
   la déconnexion n'est acceptée qu'en POST avec le jeton CSRF : un simple lien ne
   peut pas fermer la session.
   ===================================================================== */

require_once __DIR__ . '/../../../Controllers/AuthController.php';
AuthController::getCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('La déconnexion doit être envoyée par POST.');
}
if (!AuthController::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

(new AuthController())->logout();

header('Location: ../../../index.php?action=login');
exit();
