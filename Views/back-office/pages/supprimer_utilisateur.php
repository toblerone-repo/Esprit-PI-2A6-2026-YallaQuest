<?php
/* [SUPPRESSION] Delete du CRUD (chapitre 6 : Views/supprimer_personne.php).
   Cette page n'affiche rien : elle reçoit l'id envoyé par le bouton « Supprimer »
   de liste_utilisateurs.php, appelle le contrôleur, puis revient sur la liste. */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Controllers/utilisateurC.php';

// La suppression se fait seulement depuis le formulaire (POST), jamais en ouvrant l'adresse.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: liste_utilisateurs.php');
    exit();
}

$id = (int) $_POST['id'];

$utilisateurC = new UtilisateurC();
$errors = $utilisateurC->verifierSuppression($id);

if (empty($errors)) {
    $nb = $utilisateurC->deleteUtilisateur($id);

    if ($nb === 1) {
        $message = ['type' => 'succes', 'texte' => 'Le compte a été supprimé.'];
    } elseif ($nb === -1) {
        $message = ['type' => 'erreur', 'texte' => "Impossible de supprimer ce compte : il est encore lié à d'autres données (quêtes, publications, guilde…)."];
    } else {
        $message = ['type' => 'erreur', 'texte' => "Cet aventurier n'existe pas ou a déjà été supprimé."];
    }
} else {
    $message = ['type' => 'erreur', 'texte' => $errors[0]];
}

// Message affiché une seule fois par liste_utilisateurs.php.
$_SESSION['flash_liste_utilisateurs'] = $message;

header('Location: liste_utilisateurs.php');
exit();
