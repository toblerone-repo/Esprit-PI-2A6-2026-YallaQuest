<?php
/*
 * GUIDE D'INTEGRATION - SECURITE (CSRF ET XSS)
 *
 * Ce fichier contient les deux fonctions CSRF communes aux modules du projet.
 * Pour les utiliser dans vos pages, adaptez le chemin du require_once vers
 * Controllers/AuthController.php. Il est inutile de creer un autre controleur
 * de securite ou de modifier la structure MVC pour reprendre cette protection.
 *
 * 1. PREPARER LA SESSION ET LE JETON AVANT TOUT AFFICHAGE
 *    $csrfToken = AuthController::getCsrfToken();
 *    Cette fonction demarre la session si necessaire et genere un jeton aleatoire
 *    avec random_bytes(32), conserve dans $_SESSION['csrf_token']. Elle reutilise
 *    ensuite le meme jeton de session : ne le regenerez pas a chaque formulaire.
 *
 * 2. VERIFIER LE JETON AVANT DE TRAITER UNE ACTION QUI MODIFIE DES DONNEES
 *    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET',
 *        ['POST', 'PUT', 'PATCH', 'DELETE'], true)
 *        && !AuthController::verifyCsrfToken(
 *            $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
 *        http_response_code(403);
 *        exit('Jeton de securite invalide. Rechargez la page.');
 *    }
 *    Placez ce controle avant vos INSERT, UPDATE, DELETE ou autres actions.
 *    verifyCsrfToken() attend une session deja ouverte : appelez getCsrfToken()
 *    avant ce controle, ou ouvrez la session dans votre point d'entree.
 *    Elle refuse un jeton absent, vide ou non textuel, puis le compare au jeton
 *    de session avec hash_equals(). Si le controle echoue, arretez le traitement.
 *    Les actions qui changent des donnees doivent passer par une methode protegee,
 *    par exemple un formulaire POST, et ne doivent pas etre declenchees par GET.
 *
 * 3. TRANSMETTRE LE JETON AVEC CHAQUE FORMULAIRE OU REQUETE JAVASCRIPT
 *    Dans chaque formulaire POST, ajoutez ce champ a l'interieur du formulaire :
 *    <input type="hidden" name="csrf_token"
 *           value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
 *    FormData(formulaire) inclut ce champ automatiquement. Si vous construisez
 *    vous-meme la requete, envoyez csrf_token avec les donnees du formulaire ou
 *    envoyez le jeton dans l'en-tete X-CSRF-Token. Le navigateur doit aussi
 *    transmettre le cookie de la meme session. Pour PUT, PATCH ou DELETE, utilisez
 *    l'en-tete : PHP ne remplit pas automatiquement $_POST pour ces methodes.
 *
 * 4. PROTEGER LES AFFICHAGES CONTRE XSS DANS VOS VUES
 *    Pour afficher une valeur dans le texte HTML ou un attribut entre guillemets :
 *    htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
 *    Appliquez cela aux noms, messages, titres et autres donnees dynamiques,
 *    meme lorsqu'elles viennent de la base. Faites-le au moment de l'affichage,
 *    sans transformer les valeurs en entites HTML avant de les enregistrer.
 *    Pour inserer une valeur PHP dans du JavaScript, utilisez json_encode()
 *    avec JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT.
 *    Pour afficher du texte avec JavaScript, utilisez textContent.
 *
 * Le CSRF ne verifie pas les droits : chaque action doit aussi verifier
 * l'utilisateur connecte et ses autorisations dans le module concerne.
 * Les en-tetes HTTP de securite sont deja declares dans index.php.
 * Les methodes d'authentification de ce controleur restent a integrer dans leur lot.
 */
declare(strict_types=1);

require_once __DIR__ . '/../Models/utilisateur.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        require_once __DIR__ . '/../config.php';
        $this->db = config::getConnexion();
    }

    public static function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!is_string($_SESSION['csrf_token'] ?? null) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token): bool
    {
        $expected = $_SESSION['csrf_token'] ?? null;
        return is_string($token) && $token !== '' && is_string($expected) && $expected !== ''
            && hash_equals($expected, $token);
    }

    // Les méthodes d'authentification seront ajoutées dans leur lot.
}
