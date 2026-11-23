<?php
/* =====================================================================
   [DARINE · MVC UTILISATEUR] Contrôleur Utilisateur
   ---------------------------------------------------------------------
   Règle du workshop (1.3) : le Contrôleur contient TOUTE la logique :
   connexion à la base, requêtes SQL préparées, vérifications.
   Les Vues n'écrivent jamais de SQL : elles appellent les méthodes d'ici.

   Organisation (même découpage que l'AuthController du workshop) :
     1. Constructeur : $this->db (connexion PDO partagée, Singleton config)
     2. [DARINE · LISTE]        listUtilisateurs(), getUtilisateur(), compteurs
     3. [DARINE · RÈGLE ADMIN]  un admin ne peut pas se supprimer
                                ni se retirer le rôle admin
     4. Méthodes déjà présentes (corrigées) : updateUtilisateur(), login()

   Pour les autres membres (ajout, modification, suppression, rôle, blocage) :
   ajoutez vos méthodes dans cette classe, avec $this->db et des requêtes
   préparées, comme ci-dessous.

   ---------------------------------------------------------------------
   [DARINE · GUIDE] COMMENT UTILISER MES MÉTHODES DEPUIS VOTRE PAGE
   ---------------------------------------------------------------------
   1) Inclure le contrôleur (chemin depuis Views/back-office/pages/) :
        require_once __DIR__ . '/../../../Controllers/utilisateurC.php';
        $utilisateurC = new UtilisateurC();

   2) Méthodes disponibles :
        $utilisateurC->listUtilisateurs()          -> Utilisateur[]  (tous)
        $utilisateurC->getUtilisateur($id)         -> Utilisateur ou null
        $utilisateurC->countAllUtilisateurs()      -> int
        $utilisateurC->countByRole('admin'|'user') -> int
        UtilisateurC::getIdConnecte()              -> int ou null (session)
        UtilisateurC::estSoiMeme($id)              -> bool
        $utilisateurC->verifierSuppression($id)            -> erreurs[]
        $utilisateurC->verifierChangementRole($id, $role)  -> erreurs[]

   3) Les méthodes renvoient des OBJETS Utilisateur (pas des tableaux) :
        utiliser les getters -> $u->getId(), $u->getNom(), $u->getPrenom(),
        $u->getEmail(), $u->getRole(), $u->getStatut(), $u->getXp(),
        $u->getNiveau()...  (liste complète dans Models/utilisateur.php)

   4) Session : getIdConnecte() lit $_SESSION['user_id'] (clé du workshop).
      Le membre qui code la connexion doit donc remplir $_SESSION['user_id']
      au login ; l'ancienne clé $_SESSION['utilisateur']['id_utilisateur']
      est aussi acceptée pour ne rien casser.
   ===================================================================== */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/utilisateur.php';

class UtilisateurC
{
    private PDO $db;

    // [DARINE · MVC] Connexion récupérée une seule fois (workshop 3.2)
    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // ==============================================================
    // [DARINE · LISTE] ADMIN BACKOFFICE DATA (workshop 6.1)
    // ==============================================================

    /**
     * Récupère tous les utilisateurs (liste du back-office).
     * @return Utilisateur[]
     *
     * Utilisation :
     *   foreach ($utilisateurC->listUtilisateurs() as $u) {
     *       echo $u->getPrenom() . ' ' . $u->getNom();
     *   }
     * [Recherche / filtre] : pour la recherche par nom/e-mail et le filtre
     * par rôle, créez une méthode à part (ex. rechercherUtilisateurs($motCle, $role))
     * qui renvoie aussi Utilisateur::fromArray($row) : la page listuser.php
     * pourra alors afficher le résultat avec la même boucle.
     */
    public function listUtilisateurs(): array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM utilisateur ORDER BY id_utilisateur ASC');
            $rows = $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }

        return array_map(fn(array $row) => Utilisateur::fromArray($row), $rows);
    }

    // Alias workshop (6.1)
    public function getAllUsers(): array
    {
        return $this->listUtilisateurs();
    }

    /**
     * Récupère un utilisateur par son id (null s'il n'existe pas).
     *
     * Utilisation (ex. page de modification, ou vérifier qu'un compte existe
     * avant de le supprimer / changer son rôle) :
     *   $u = $utilisateurC->getUtilisateur((int) $_GET['id']);
     *   if (!$u) { header('Location: listuser.php'); exit; }
     *   echo $u->getEmail();
     */
    public function getUtilisateur(int $id): ?Utilisateur
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }

        return $row ? Utilisateur::fromArray($row) : null;
    }

    // Alias workshop (5.3)
    public function findById(int $id): ?Utilisateur
    {
        return $this->getUtilisateur($id);
    }

    /**
     * Compte les utilisateurs d'un rôle ('user' ou 'admin').
     * Utilisation : $nbAdmins = $utilisateurC->countByRole('admin');
     */
    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM utilisateur WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Nombre total de comptes.
     * Utilisation : $total = $utilisateurC->countAllUtilisateurs();
     */
    public function countAllUtilisateurs(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
    }

    // Alias workshop (6.1)
    public function countAllUsers(): int
    {
        return $this->countAllUtilisateurs();
    }

    // ==============================================================
    // [DARINE · RÈGLE ADMIN] Un admin ne peut pas se supprimer
    // ni se rétrograder (admin -> user) lui-même.
    // --------------------------------------------------------------
    // Utilisation par les membres qui codent la suppression / le rôle,
    // AVANT d'exécuter leur requête DELETE ou UPDATE :
    //
    //   $errors = $utilisateurC->verifierSuppression((int) $_POST['id']);
    //   if (empty($errors)) {
    //       $utilisateurC->deleteUtilisateur((int) $_POST['id']);   // votre méthode
    //   }
    //
    //   $errors = $utilisateurC->verifierChangementRole((int) $_POST['id'], $_POST['role']);
    //   if (empty($errors)) {
    //       $utilisateurC->changerRole((int) $_POST['id'], $_POST['role']); // votre méthode
    //   }
    //
    //   Affichage des erreurs dans la vue :
    //   foreach ($errors as $error) { echo '<p class="alert">' . $error . '</p>'; }
    //
    // Retour : tableau d'erreurs, vide = autorisé (même principe que
    // register() / login() dans le workshop).
    // IMPORTANT : l'appel doit être fait côté serveur (dans le PHP qui
    // supprime / modifie), le cadenas affiché dans listuser.php n'est
    // qu'un repère visuel.
    // ==============================================================

    /**
     * Id de l'utilisateur connecté, lu dans la session (null si personne).
     * Accepte la clé du workshop ($_SESSION['user_id']) et celle utilisée
     * actuellement par login.php ($_SESSION['utilisateur']['id_utilisateur']).
     * Utilisation : $monId = UtilisateurC::getIdConnecte();
     * (session_start() doit avoir été appelé avant)
     */
    public static function getIdConnecte(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        if (!empty($_SESSION['utilisateur']['id_utilisateur'])) {
            return (int) $_SESSION['utilisateur']['id_utilisateur'];
        }
        return null;
    }

    /**
     * [WORKSHOP 4.1 & 5.1] Vérifie si un utilisateur est actuellement authentifié.
     */
    public static function isAuthenticated(): bool
    {
        return self::getIdConnecte() !== null;
    }

    /**
     * [WORKSHOP 6.1] Vérifie si l'utilisateur connecté possède le rôle demandé.
     */
    public static function hasRole(string $role): bool
    {
        if (!self::isAuthenticated()) {
            return false;
        }
        $currentRole = $_SESSION['role'] ?? ($_SESSION['utilisateur']['role'] ?? null);
        return $currentRole === $role;
    }

    /**
     * [WORKSHOP 5.3] Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
     */
    public static function requireAuth(string $loginUrl = 'login.php'): void
    {
        if (!self::isAuthenticated()) {
            header('Location: ' . $loginUrl);
            exit();
        }
    }

    /**
     * [WORKSHOP 6.1] Vérifie les droits d'accès : bloque avec une erreur 403 si le rôle requis n'est pas présent.
     */
    public static function requireRole(string $role, string $loginUrl = 'login.php'): void
    {
        self::requireAuth($loginUrl);
        if (!self::hasRole($role)) {
            http_response_code(403);
            die('Access denied: you do not have the required permissions.');
        }
    }

    /**
     * true si $id est le compte actuellement connecté.
     * Utilisation dans une vue (ex. cacher le bouton Supprimer sur sa propre ligne) :
     *   <?php if (!UtilisateurC::estSoiMeme($u->getId())): ?> ...bouton... <?php endif; ?>
     */
    public static function estSoiMeme(int $id): bool
    {
        $idConnecte = self::getIdConnecte();
        return $idConnecte !== null && $idConnecte === $id;
    }

    /**
     * Vérifie qu'on a le droit de supprimer ce compte.
     */
    public function verifierSuppression(int $idCible): array
    {
        $errors = [];

        if (self::estSoiMeme($idCible)) {
            $errors[] = "Vous ne pouvez pas supprimer votre propre compte.";
        }

        return $errors;
    }

    /**
     * Vérifie qu'on a le droit de donner le rôle $nouveauRole à ce compte.
     * Un admin peut changer le rôle des AUTRES, mais pas se retirer
     * son propre rôle admin.
     */
    public function verifierChangementRole(int $idCible, string $nouveauRole): array
    {
        $errors = [];

        if (!in_array($nouveauRole, ['user', 'admin'], true)) {
            $errors[] = "Rôle invalide.";
            return $errors;
        }

        if (self::estSoiMeme($idCible) && $nouveauRole !== 'admin') {
            $errors[] = "Vous ne pouvez pas retirer votre propre rôle administrateur.";
        }

        return $errors;
    }

    /**
     * Modifie le rôle d'un utilisateur en base de données.
     * Règle de gestion : vérifie avec verifierChangementRole() qu'un admin
     * ne retire pas son propre statut admin.
     *
     * @param int $id Identifiant de l'utilisateur cible
     * @param string $nouveauRole 'user' ou 'admin'
     * @return bool true si la mise à jour a réussi, false sinon
     */
    public function changerRole(int $id, string $nouveauRole): bool
    {
        $errors = $this->verifierChangementRole($id, $nouveauRole);
        if (!empty($errors)) {
            return false;
        }

        $sql = 'UPDATE utilisateur SET role = :role WHERE id_utilisateur = :id';
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'role' => $nouveauRole,
                'id'   => $id,
            ]);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Alias en anglais / workshop pour changerRole
    public function changeRole(int $id, string $nouveauRole): bool
    {
        return $this->changerRole($id, $nouveauRole);
    }

    public function updateRole(int $id, string $nouveauRole): bool
    {
        return $this->changerRole($id, $nouveauRole);
    }

    /**
     * Récupère un utilisateur par son adresse e-mail.
     * @param string $email Adresse e-mail recherchée
     * @return Utilisateur|null L'objet Utilisateur ou null si introuvable
     */
    public function getUtilisateurParEmail(string $email): ?Utilisateur
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE mail = :mail LIMIT 1');
            $stmt->execute(['mail' => trim($email)]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }

        return $row ? Utilisateur::fromArray($row) : null;
    }

    public function getUtilisateurByEmail(string $email): ?Utilisateur
    {
        return $this->getUtilisateurParEmail($email);
    }

    // Alias workshop (5.1)
    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->getUtilisateurParEmail($email);
    }

    /**
     * Modifie le rôle d'un utilisateur identifié par son adresse e-mail.
     * @param string $email Adresse e-mail du compte
     * @param string $nouveauRole 'user' ou 'admin'
     * @return bool true si le rôle a été modifié
     */
    public function changerRoleParEmail(string $email, string $nouveauRole): bool
    {
        $u = $this->getUtilisateurParEmail($email);
        if (!$u || $u->getId() === null) {
            return false;
        }

        return $this->changerRole($u->getId(), $nouveauRole);
    }

    public function changeRoleByEmail(string $email, string $nouveauRole): bool
    {
        return $this->changerRoleParEmail($email, $nouveauRole);
    }

    // ==============================================================
    // [AJOUT UTILISATEUR] CRUD Admin · Ajout d'un aventurier
    // ==============================================================

    /**
     * Vérifie si une adresse e-mail existe déjà en base de données.
     */
    public function emailExiste(string $email, ?int $exclureId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilisateur WHERE mail = :mail';
        $params = ['mail' => $email];
        if ($exclureId !== null) {
            $sql .= ' AND id_utilisateur != :id';
            $params['id'] = $exclureId;
        }
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie les données saisies pour l'ajout d'un utilisateur.
     * @param array $data Données du formulaire POST
     * @return array Tableau des erreurs (clé => message), vide si valide
     */
    public function verifierAjout(array $data): array
    {
        $errors = [];
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $mail = trim((string) ($data['mail'] ?? $data['email'] ?? ''));
        $password = (string) ($data['mot_de_passe'] ?? $data['password'] ?? '');
        $confirmPassword = isset($data['confirmer_mot_de_passe']) ? (string) $data['confirmer_mot_de_passe'] : (isset($data['confirm_password']) ? (string) $data['confirm_password'] : null);
        $role = (string) ($data['role'] ?? 'user');
        $statut = (string) ($data['statut'] ?? 'actif');

        if (empty($nom)) {
            $errors['nom'] = "Le nom est obligatoire.";
        } elseif (mb_strlen($nom) > 100) {
            $errors['nom'] = "Le nom ne doit pas dépasser 100 caractères.";
        }

        if (empty($prenom)) {
            $errors['prenom'] = "Le prénom est obligatoire.";
        } elseif (mb_strlen($prenom) > 100) {
            $errors['prenom'] = "Le prénom ne doit pas dépasser 100 caractères.";
        }

        if (empty($mail)) {
            $errors['mail'] = "L'adresse e-mail est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['mail'] = "L'adresse e-mail n'est pas valide.";
        } elseif (mb_strlen($mail) > 254) {
            $errors['mail'] = "L'e-mail ne doit pas dépasser 254 caractères.";
        } elseif ($this->emailExiste($mail)) {
            $errors['mail'] = "Cette adresse e-mail est déjà utilisée par un autre aventurier.";
        }

        if (empty($password)) {
            $errors['mot_de_passe'] = "Le mot de passe est obligatoire.";
        } elseif (strlen($password) < 6) {
            $errors['mot_de_passe'] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        if ($confirmPassword !== null && $password !== $confirmPassword) {
            $errors['confirmer_mot_de_passe'] = "Les mots de passe ne correspondent pas.";
        }

        if (!in_array($role, ['user', 'admin'], true)) {
            $errors['role'] = "Le rôle sélectionné est invalide.";
        }

        if (!in_array($statut, ['actif', 'bloque'], true)) {
            $errors['statut'] = "Le statut sélectionné est invalide.";
        }

        return $errors;
    }

    /**
     * Enregistre un nouvel utilisateur en base de données.
     * Respecte l'architecture MVC du workshop : toute requête SQL
     * est encapsulée dans le contrôleur.
     *
     * @param Utilisateur $utilisateur Objet représentant l'utilisateur
     * @return bool true si inséré avec succès
     */
    public function addUtilisateur(Utilisateur $utilisateur): bool
    {
        $img = $utilisateur->getImgProfil() ?: 'renard';

        // Assurer que la clé étrangère img_profil existe dans la table images
        try {
            $checkImg = $this->db->prepare('SELECT id_image FROM images WHERE id_image = :img LIMIT 1');
            $checkImg->execute(['img' => $img]);
            if (!$checkImg->fetch()) {
                $insertImg = $this->db->prepare('INSERT INTO images (id_image) VALUES (:img)');
                $insertImg->execute(['img' => $img]);
            }
        } catch (PDOException) {
            // Continuer si images est géré différemment
        }

        $sql = 'INSERT INTO utilisateur (
                    nom, prenom, mail, mot_de_passe_hash, role, statut,
                    total_xp, niveau, img_profil, derniere_prolongation_at, id_totem, id_guilde
                ) VALUES (
                    :nom, :prenom, :mail, :mp, :role, :statut,
                    :total_xp, :niveau, :img_profil, :derniere_prolongation_at, :id_totem, :id_guilde
                )';

        try {
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                'nom'                      => $utilisateur->getNom(),
                'prenom'                   => $utilisateur->getPrenom(),
                'mail'                     => $utilisateur->getEmail(),
                'mp'                       => $utilisateur->getMp(),
                'role'                     => $utilisateur->getRole() ?: 'user',
                'statut'                   => $utilisateur->getStatut() ?: 'actif',
                'total_xp'                 => $utilisateur->getXp(),
                'niveau'                   => $utilisateur->getNiveau(),
                'img_profil'               => $img,
                'derniere_prolongation_at' => $utilisateur->getDerniereProlongationAt()?->format('Y-m-d H:i:s'),
                'id_totem'                 => $utilisateur->getIdTotem(),
                'id_guilde'                => $utilisateur->getIdGuilde(),
            ]);

            if ($res) {
                $utilisateur->setId((int) $this->db->lastInsertId());
                return true;
            }
            return false;
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Alias pour addUtilisateur (nommage standard workshop)
    public function ajouterUtilisateur(Utilisateur $utilisateur): bool
    {
        return $this->addUtilisateur($utilisateur);
    }

    public function createUtilisateur(Utilisateur $utilisateur): bool
    {
        return $this->addUtilisateur($utilisateur);
    }

    /**
     * Supprime un utilisateur après contrôle verifierSuppression().
     */
    public function deleteUtilisateur(int $id): bool
    {
        $errors = $this->verifierSuppression($id);
        if (!empty($errors)) {
            return false;
        }

        $sql = 'DELETE FROM utilisateur WHERE id_utilisateur = :id';
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ==============================================================
    // MÉTHODES DÉJÀ PRÉSENTES (utilisées par modifier-profil.php
    // et login.php) — [DARINE · CORRECTION] voir commentaires.
    // ==============================================================

    // [DARINE · CORRECTION] La colonne s'appelle id_utilisateur (et non id) :
    // l'ancienne requête "WHERE id = :id" échouait toujours.
    // La signature (Utilisateur, int $id) est gardée pour ne pas casser
    // modifier-profil.php ; c'est bien $id qui est utilisé maintenant
    // (avant il était ignoré).
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
                'mp' => $utilisateur->getMp(),
            ]);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ==============================================================
    // [WORKSHOP 7.4] PROTECTION CSRF
    // ==============================================================

    /**
     * Génère ou récupère le token CSRF unique de la session courante.
     */
    public function getCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie la validité du token CSRF fourni.
     */
    public function verifyCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ==============================================================
    // [WORKSHOP 5.1 & 5.2] AUTHENTIFICATION · CONNEXION (LOGIN)
    // --------------------------------------------------------------
    // Règle du workshop :
    // 1. Vérification des champs obligatoires
    // 2. Recherche par e-mail préparée (pas d'injection SQL)
    // 3. Comparaison sécurisée avec password_verify()
    // 4. Message d'erreur générique : "E-mail ou mot de passe incorrect."
    //    (ne jamais révéler si l'e-mail existe ou non)
    // 5. Régénération de session (session_regenerate_id(true)) contre
    //    la fixation de session
    // 6. Remplissage des variables de session (user_id, username, role...)
    // ==============================================================

    /**
     * Authentifie un utilisateur (vérification email + mot de passe hashé).
     *
     * Signature polyvalente :
     * - $utilisateurC->login($_POST)          -> renvoie un tableau d'erreurs (vide [] si succès) [Workshop 5.1]
     * - $utilisateurC->login($email, $mdp)     -> renvoie les infos utilisateur (array) ou null   [Compatibilité YallaQuest]
     *
     * @param array|string $input Tableau POST ou adresse e-mail
     * @param string|null $passwordParam Mot de passe si appelé avec 2 chaînes
     * @return array|null Tableau d'erreurs (Workshop), tableau utilisateur ou null (legacy)
     */
    public function login(array|string $input, ?string $passwordParam = null): mixed
    {
        // Cas 1 : Style Workshop (appel avec tableau $_POST)
        if (is_array($input)) {
            $errors = [];

            // CSRF si présent dans le formulaire
            if (isset($input['csrf_token']) && !$this->verifyCsrfToken($input['csrf_token'])) {
                $errors[] = "Token de sécurité invalide. Veuillez rafraîchir la page et réessayer.";
                return $errors;
            }

            $email = trim((string) ($input['email'] ?? $input['mail'] ?? ''));
            $password = (string) ($input['mot_de_passe'] ?? $input['password'] ?? '');

            if ($email === '' || $password === '') {
                $errors[] = "Veuillez saisir votre e-mail et votre mot de passe.";
                return $errors;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse e-mail n'est pas valide.";
                return $errors;
            }

            // Vérifier QUI est l'utilisateur dans la table `utilisateur`
            $user = $this->findByEmail($email);

            // Message volontairement générique : ne révèle jamais si l'e-mail existe (Workshop 5.1)
            if (!$user || !password_verify($password, $user->getMp())) {
                $errors[] = "E-mail ou mot de passe incorrect.";
                return $errors;
            }

            // Vérification du statut (actif ou bloqué)
            if ($user->getStatut() === 'bloque') {
                $errors[] = "Votre compte est bloqué. Veuillez contacter un administrateur.";
                return $errors;
            }

            // Démarrage de session si nécessaire
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }

            // Protection contre la fixation de session (Workshop 5.1)
            if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            // Clés de session du Workshop (Partie 5.1 & 5.5)
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            $_SESSION['email'] = $user->getEmail();
            $_SESSION['role'] = $user->getRole();
            $_SESSION['statut'] = $user->getStatut();
            $_SESSION['last_activity'] = time();

            // Rétrocompatibilité totale avec les autres modules YallaQuest
            $_SESSION['utilisateur'] = [
                'id_utilisateur' => $user->getId(),
                'nom'            => $user->getNom(),
                'prenom'         => $user->getPrenom(),
                'mail'           => $user->getEmail(),
                'role'           => $user->getRole(),
                'statut'         => $user->getStatut(),
                'total_xp'       => $user->getXp(),
                'niveau'         => $user->getNiveau(),
                'img_profil'     => $user->getImgProfil(),
            ];

            return []; // Succès (aucune erreur)
        }

        // Cas 2 : Appel direct login($email, $password)
        $email = trim((string) $input);
        $password = (string) ($passwordParam ?? '');

        if ($email === '' || $password === '') {
            return null;
        }

        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user->getMp()) || $user->getStatut() === 'bloque') {
            return null;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['statut'] = $user->getStatut();
        $_SESSION['last_activity'] = time();

        $_SESSION['utilisateur'] = [
            'id_utilisateur' => $user->getId(),
            'nom'            => $user->getNom(),
            'prenom'         => $user->getPrenom(),
            'mail'           => $user->getEmail(),
            'role'           => $user->getRole(),
            'statut'         => $user->getStatut(),
            'total_xp'       => $user->getXp(),
            'niveau'         => $user->getNiveau(),
            'img_profil'     => $user->getImgProfil(),
        ];

        return $_SESSION['utilisateur'];
    }

    // ==============================================================
    // [WORKSHOP 5.1] DÉCONNEXION (LOGOUT)
    // --------------------------------------------------------------
    // Vide la session, supprime le cookie PHPSESSID et détruit la session.
    // ==============================================================
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                $params['secure'] ?? false,
                $params['httponly'] ?? true
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
