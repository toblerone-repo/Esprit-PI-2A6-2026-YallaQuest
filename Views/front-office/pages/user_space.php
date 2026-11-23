<?php
/* =====================================================================
   [WORKSHOP 5.3] Espace utilisateur protégé (Dashboard)
   ---------------------------------------------------------------------
   Accessible uniquement aux utilisateurs authentifiés.
   ===================================================================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
UtilisateurC::requireAuth('login.php');

$user = $user ?? null;
if (!$user) {
    $utilisateurC = new UtilisateurC();
    $user = $utilisateurC->findById((int) ($_SESSION['user_id'] ?? 0));
}
if (!$user) {
    header('Location: login.php');
    exit;
}

// Redirection fluide vers la page de profil YallaQuest
header('Location: page-profil.php');
exit;
