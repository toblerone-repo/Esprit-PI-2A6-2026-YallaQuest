<?php
/* =====================================================================
   [BLOCAGE] Action : bloquer / débloquer un compte (back-office)
   ---------------------------------------------------------------------
   Reçoit en POST : id (id_utilisateur) et action ('bloquer' ou 'debloquer'),
   envoyés par les boutons de la liste des aventuriers (listuser.php).
   - Réservé à un administrateur connecté (sinon erreur 403).
   - Le contrôleur vérifie que le compte existe et met à jour le statut
     avec une requête préparée.
   Le résultat est rangé dans $_SESSION['message_statut'] puis on revient
   à la liste, qui l'affiche.
   ===================================================================== */
session_start();

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// Seul un administrateur connecté peut accéder à cette action
if (!UtilisateurC::hasRole('admin')) {
    http_response_code(403);
    die('Accès refusé : action réservée aux administrateurs.');
}

// Modification de données : uniquement en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listuser.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

$utilisateurC = new UtilisateurC();

if ($action === 'bloquer') {
    $errors = $utilisateurC->bloquerUtilisateur($id);
    $succes = "Le compte a été bloqué.";
} elseif ($action === 'debloquer') {
    $errors = $utilisateurC->debloquerUtilisateur($id);
    $succes = "Le compte a été débloqué.";
} else {
    $errors = ["Action inconnue."];
    $succes = '';
}

if (empty($errors)) {
    $_SESSION['message_statut'] = ['type' => 'success', 'texte' => $succes];
} else {
    $_SESSION['message_statut'] = ['type' => 'error', 'texte' => $errors[0]];
}

header('Location: listuser.php');
exit();
