<?php
/* =====================================================================
   [DARINE · MVC UTILISATEUR] Contrôleur Utilisateur
   Même forme que AuthController du workshop (parties 3.2 et 6.1) :
   toute la logique et toutes les requêtes SQL sont ici, les Vues
   ne font qu'afficher.

   ---------------------------------------------------------------------
   [DARINE · GUIDE] COMMENT UTILISER MES MÉTHODES DEPUIS VOTRE PAGE
   ---------------------------------------------------------------------
   require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
   $utilisateurC = new UtilisateurC();

   $utilisateurC->listUtilisateurs()            -> Utilisateur[] (tous les comptes)
   $utilisateurC->getUtilisateur($id)           -> Utilisateur ou null
   $utilisateurC->rechercherParId($id)          -> Utilisateur[] (0 ou 1 compte)
   $utilisateurC->countAllUtilisateurs()        -> int
   $utilisateurC->countByRole('admin')          -> int
   UtilisateurC::estSoiMeme($id)                -> true si $id = compte connecté
   $utilisateurC->verifierSuppression($id)           -> tableau d'erreurs
   $utilisateurC->verifierChangementRole($id, $role) -> tableau d'erreurs

   Les méthodes renvoient des OBJETS Utilisateur : on lit les valeurs
   avec les getters ($u->getNom(), $u->getEmail(), $u->getRole()...).
   Le compte connecté est lu dans $_SESSION['user_id'] (clé du workshop,
   remplie au login par la partie Sécurité).

   Autres membres (ajout, modification, suppression, rôle, blocage,
   recherche) : ajoutez vos méthodes dans cette classe avec $this->db.
   ===================================================================== */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/utilisateur.php';

class UtilisateurC
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // ==============================================================
    // [DARINE · LISTE] ADMIN BACKOFFICE DATA (workshop 6.1)
    // ==============================================================

    /**
     * Récupère tous les utilisateurs (liste du back-office).
     * Utilisation : foreach ($utilisateurC->listUtilisateurs() as $u) { echo $u->getNom(); }
     * [Recherche] : votre méthode de recherche doit aussi renvoyer
     * des objets Utilisateur (Utilisateur::fromArray), pour que
     * listuser.php puisse l'afficher avec la même boucle.
     * @return Utilisateur[]
     */
    public function listUtilisateurs(): array
    {
        $stmt = $this->db->query('SELECT * FROM utilisateur ORDER BY id_utilisateur ASC');
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => Utilisateur::fromArray($row), $rows);
    }

    /**
     * Récupère un utilisateur par son id (null s'il n'existe pas).
     * Utilisation : $u = $utilisateurC->getUtilisateur((int) $_GET['id']);
     */
    public function getUtilisateur(int $id): ?Utilisateur
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Utilisateur::fromArray($row) : null;
    }

    /**
     * [DARINE · RECHERCHE PAR ID] Bouton "Rechercher" de listuser.php.
     * Renvoie un tableau avec l'utilisateur trouvé, ou un tableau vide :
     * même forme que listUtilisateurs(), donc la page l'affiche avec la même boucle.
     * Utilisation : $liste = $utilisateurC->rechercherParId(3);
     * @return Utilisateur[]
     */
    public function rechercherParId(int $id): array
    {
        $utilisateur = $this->getUtilisateur($id);

        if ($utilisateur === null) {
            return [];
        }
        return [$utilisateur];
    }

    /**
     * Compte les utilisateurs d'un rôle ('user' ou 'admin').
     */
    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM utilisateur WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Nombre total de comptes.
     */
    public function countAllUtilisateurs(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
    }

    // ==============================================================
    // [DARINE · RÈGLE ADMIN] Un admin ne peut pas se supprimer
    // ni se retirer le rôle admin.
    // --------------------------------------------------------------
    // À appeler par les membres Suppression / Rôle AVANT leur requête :
    //
    //   $errors = $utilisateurC->verifierSuppression((int) $_POST['id']);
    //   if (empty($errors)) { ...votre DELETE... }
    //
    //   $errors = $utilisateurC->verifierChangementRole((int) $_POST['id'], $_POST['role']);
    //   if (empty($errors)) { ...votre UPDATE du rôle... }
    //
    // Tableau vide = autorisé (même principe que register() du workshop).
    // Le cadenas affiché dans listuser.php n'est qu'un repère visuel :
    // c'est cet appel, côté serveur, qui bloque vraiment l'action.
    // ==============================================================

    /**
     * true si $id est le compte actuellement connecté.
     * (int) : l'id peut avoir été rangé en texte ('1') dans la session ;
     * sans conversion, '1' === 1 serait faux et la règle ne bloquerait rien.
     * Personne de connecté -> 0, qui ne correspond à aucun compte.
     */
    public static function estSoiMeme(int $id): bool
    {
        return (int) ($_SESSION['user_id'] ?? 0) === $id;
    }

    public function verifierSuppression(int $id): array
    {
        $errors = [];

        if (self::estSoiMeme($id)) {
            $errors[] = "Vous ne pouvez pas supprimer votre propre compte.";
        }

        return $errors;
    }

    public function verifierChangementRole(int $id, string $nouveauRole): array
    {
        $errors = [];

        if (self::estSoiMeme($id) && $nouveauRole !== 'admin') {
            $errors[] = "Vous ne pouvez pas retirer votre propre rôle administrateur.";
        }

        return $errors;
    }

    // ==============================================================
    // MÉTHODES DÉJÀ PRÉSENTES (modifier-profil.php et login.php)
    // ==============================================================

    // [DARINE · CORRECTION] La colonne s'appelle id_utilisateur (et non id).
    // getMp() -> getPassword() (nom du workshop).
    // [NOTE POUR LE MEMBRE DE modifier-profil.php] voir la note en haut
    // de Models/utilisateur.php : le constructeur est maintenant vide.
    public function updateUtilisateur(Utilisateur $utilisateur, int $id): void
    {
        $sql = 'UPDATE utilisateur SET
                    nom = :nom,
                    prenom = :prenom,
                    mail = :mail,
                    mot_de_passe_hash = :mp
                WHERE id_utilisateur = :id';

        try {
            $query = $this->db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'mail' => $utilisateur->getEmail(),
                'mp' => $utilisateur->getPassword(),
            ]);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Renvoie l'utilisateur (sans le hash) si l'e-mail et le mot de passe sont corrects, sinon null.
    // [DARINE · CORRECTION] "SELECT id" -> "SELECT id_utilisateur" (la colonne id n'existe pas).
    public function login(string $mail, string $mp): ?array
    {
        $sql = 'SELECT id_utilisateur, nom, prenom, mail, mot_de_passe_hash
                FROM utilisateur WHERE mail = :mail LIMIT 1';

        try {
            $query = $this->db->prepare($sql);
            $query->execute(['mail' => $mail]);
            $utilisateur = $query->fetch();
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }

        if (!$utilisateur || !password_verify($mp, $utilisateur['mot_de_passe_hash'])) {
            return null;
        }

        unset($utilisateur['mot_de_passe_hash']);
        return $utilisateur;
    }
}
