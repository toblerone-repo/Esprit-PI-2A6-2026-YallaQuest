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

    // Le modèle fournit le nouveau mot de passe en clair.
    function updateUtilisateur(Utilisateur $utilisateur, int $id, string $ancienMotDePasse): bool
    {
        if ($id < 1 || strlen($utilisateur->getMp()) < 8) {
            return false;
        }

        $db = config::getConnexion();

        try {
            $read = $db->prepare(
                'SELECT mot_de_passe_hash FROM utilisateur WHERE id_utilisateur = :id LIMIT 1'
            );
            $read->bindValue(':id', $id, PDO::PARAM_INT);
            $read->execute();
            $storedHash = $read->fetchColumn();

            if (!is_string($storedHash) || !password_verify($ancienMotDePasse, $storedHash)) {
                return false;
            }

            // Le hachage est calculé ici, avant toute écriture en base.
            $passwordHash = password_hash($utilisateur->getMp(), PASSWORD_DEFAULT);

            $query = $db->prepare(
                'UPDATE utilisateur SET
                    nom = :nom,
                    prenom = :prenom,
                    mail = :mail,
                    mot_de_passe_hash = :mp
                 WHERE id_utilisateur = :id AND mot_de_passe_hash = :previous_hash'
            );
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':nom', $utilisateur->getNom(), PDO::PARAM_STR);
            $query->bindValue(':prenom', $utilisateur->getPrenom(), PDO::PARAM_STR);
            $query->bindValue(':mail', $utilisateur->getEmail(), PDO::PARAM_STR);
            $query->bindValue(':mp', $passwordHash, PDO::PARAM_STR);
            $query->bindValue(':previous_hash', $storedHash, PDO::PARAM_STR);
            $query->execute();

            return $query->rowCount() === 1;
        } catch (PDOException $e) {
            error_log('Requête SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }
    }

    // Renvoie l'utilisateur (sans le hash) si l'e-mail et le mot de passe sont corrects, sinon null.
    function login(string $mail, string $mp): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur AS id, nom, prenom, mail, mot_de_passe_hash FROM utilisateur WHERE mail = :mail LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':mail', $mail, PDO::PARAM_STR);
            $query->execute();
            $utilisateur = $query->fetch();
        } catch (PDOException $e) {
            error_log('Requête SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }

        if (!$utilisateur || !password_verify($mp, $utilisateur['mot_de_passe_hash'])) {
            return null;
        }

        unset($utilisateur['mot_de_passe_hash']);
        return $utilisateur;
    }
    public static function getIdConnecte(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        return null;
    }
    function deleteUtilisateur($id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('DELETE FROM utilisateur WHERE id_utilisateur = :id');
            $query->execute(['id' => $id]);
            return $query->rowCount();
        } catch (PDOException $e) {
            // Code 23000 : clé étrangère. Le compte est encore utilisé par une autre table
            // (quêtes programmées, publications, guilde créée...) : MySQL refuse le DELETE.
            if ($e->getCode() == 23000) {
                return -1;
            }
            die('Erreur: ' . $e->getMessage());
        }
    }
    function listerImagesProfil()
    {
        $sql = "SELECT id_image AS id, chemin FROM images";
        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        };
    }
    // Dorra garde ses tableaux pour l'affichage du profil ; la liste garde ses objets.
    public function getProfilUtilisateur(int $id): ?array
    {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT u.*, i.chemin FROM utilisateur u
            LEFT JOIN images i ON i.id_image = u.img_profil
            WHERE u.id_utilisateur = :id LIMIT 1');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $profil = $query->fetch(PDO::FETCH_ASSOC);
        return $profil ?: null;
    }

    // Le formulaire de Dorra transmet un hash : il ne doit pas être haché une seconde fois.
    public function updateProfilUtilisateur(Utilisateur $utilisateur, int $id, string $ancienMotDePasse = ''): bool
    {
        if ($id < 1 || $id !== self::getIdConnecte()) {
            return false;
        }
        $profil = $this->getProfilUtilisateur($id);
        if (!$profil || password_get_info($utilisateur->getMp())['algo'] === null) {
            return false;
        }
        if ($utilisateur->getMp() !== $profil['mot_de_passe_hash']
            && !password_verify($ancienMotDePasse, $profil['mot_de_passe_hash'])) {
            return false;
        }
        $db = config::getConnexion();
        $sql = 'UPDATE utilisateur SET nom = :nom, prenom = :prenom, mail = :mail,
            mot_de_passe_hash = :mp, img_profil = :img_profil
            WHERE id_utilisateur = :id AND mot_de_passe_hash = :ancien_hash';
        $query = $db->prepare($sql);
        return $query->execute([
            'id' => $id, 'nom' => $utilisateur->getNom(), 'prenom' => $utilisateur->getPrenom(),
            'mail' => $utilisateur->getEmail(), 'mp' => $utilisateur->getMp(),
            'img_profil' => $utilisateur->getImgProfil(), 'ancien_hash' => $profil['mot_de_passe_hash'],
        ]);
    }


    /**
     * Recherche par nom, prénom ou e-mail, avec rôle et ID facultatifs.
     * Les filtres se cumulent et le résultat conserve la forme Utilisateur[].
     */
    public function rechercherUtilisateurs(string $recherche = '', string $role = '', ?int $id = null): array
    {
        if (!in_array($role, ['', 'admin', 'user'], true) || ($id !== null && $id < 1)) {
            return [];
        }

        $sql = 'SELECT * FROM utilisateur WHERE 1 = 1';
        $parametres = [];
        $recherche = trim($recherche);
        if ($recherche !== '') {
            $sql .= " AND (nom LIKE :nom ESCAPE '!' OR prenom LIKE :prenom ESCAPE '!'
                OR mail LIKE :mail ESCAPE '!'
                OR CONCAT(prenom, ' ', nom) LIKE :nom_complet ESCAPE '!'
                OR CONCAT(nom, ' ', prenom) LIKE :nom_inverse ESCAPE '!')";
            $motif = '%' . strtr($recherche, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            foreach ([':nom', ':prenom', ':mail', ':nom_complet', ':nom_inverse'] as $parametre) {
                $parametres[$parametre] = $motif;
            }
        }
        if ($role !== '') {
            $sql .= ' AND role = :role';
            $parametres[':role'] = $role;
        }
        if ($id !== null) {
            $sql .= ' AND id_utilisateur = :id';
            $parametres[':id'] = $id;
        }
        $sql .= ' ORDER BY id_utilisateur ASC';

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($parametres as $nom => $valeur) {
                $stmt->bindValue($nom, $valeur, is_int($valeur) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Recherche utilisateurs : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }

        return array_map(fn(array $row) => Utilisateur::fromArray($row), $rows);
    }

}
