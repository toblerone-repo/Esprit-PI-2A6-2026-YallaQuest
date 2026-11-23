<?php
/* =====================================================================
   [WORKSHOP 5.1 & 5.2] Déconnexion Back-office (Logout)
   ---------------------------------------------------------------------
   Détruit la session côté serveur, expire le cookie PHPSESSID et
   redirige vers la page de connexion.
   ===================================================================== */

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

$utilisateurC = new UtilisateurC();
$utilisateurC->logout();

header('Location: ../../front-office/pages/login.php');
exit();
