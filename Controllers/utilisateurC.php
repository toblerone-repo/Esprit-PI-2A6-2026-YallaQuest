<?php
// Darine : liste/modèle. Amine : suppression. Dorra : profil. Eya : inscription/blocage. Houssem : SQL/CSRF/XSS.
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

    // Darine : connexion MVC
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
    // Darine / Amine : liste des comptes
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
    // Darine : récupération par ID
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
    // Darine : recherche par ID
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
    // Darine / Amine : comptage par rôle
    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM utilisateur WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Nombre total de comptes.
     */
    // Darine / Amine : comptage total
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
    // Darine : protection du compte connecté
    public static function estSoiMeme(int $id): bool
    {
        return (int) ($_SESSION['user_id'] ?? 0) === $id;
    }

    // Darine : interdiction de supprimer son compte
    public function verifierSuppression(int $id): array
    {
        $errors = [];

        if (self::estSoiMeme($id)) {
            $errors[] = "Vous ne pouvez pas supprimer votre propre compte.";
        }

        return $errors;
    }

    // Darine : interdiction de se rétrograder
    public function verifierChangementRole(int $id, string $nouveauRole): array
    {
        $errors = [];

        if (self::estSoiMeme($id) && $nouveauRole !== 'admin') {
            $errors[] = "Vous ne pouvez pas retirer votre propre rôle administrateur.";
        }

        return $errors;
    }

    // Le modèle fournit le nouveau mot de passe en clair.
    // Houssem : hachage avant la mise à jour SQL
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
    // Houssem / Eya : vérification des identifiants et du statut
    function login(string $mail, string $mp): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur AS id, id_utilisateur, nom, prenom, mail, role, statut, mot_de_passe_hash FROM utilisateur WHERE mail = :mail LIMIT 1";

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

        if (($utilisateur['statut'] ?? '') === 'bloque') { return null; }
        unset($utilisateur['mot_de_passe_hash']);
        return $utilisateur;
    }
    // Amine : identifiant de session
    public static function getIdConnecte(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        return null;
    }
    // Amine : suppression et clés étrangères
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
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }
    }
    // Dorra : images disponibles
    function listerImagesProfil()
    {
        $sql = "SELECT id_image AS id, chemin FROM images";
        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (PDOException $e) {
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        };
    }
    // Dorra garde ses tableaux pour l'affichage du profil ; la liste garde ses objets.
    // Intégration : tableaux de profil utilisés par Dorra
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
    // Dorra / Houssem : profil avec hash déjà calculé, sans double hachage
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
            'img_profil' => $utilisateur->getImgProfil() !== '' ? $utilisateur->getImgProfil() : null, 'ancien_hash' => $profil['mot_de_passe_hash'],
        ]);
    }


    /**
     * Recherche par nom, prénom ou e-mail, avec rôle et ID facultatifs.
     * Les filtres se cumulent et le résultat conserve la forme Utilisateur[].
     */
    // Houssem : recherche nom/e-mail/rôle/ID
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

    // Eya : inscription et blocage/déblocage.
    // Eya : état de connexion
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['utilisateur']);
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : contrôle du rôle
    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['utilisateur']['role'] ?? null) === $role;
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : unicité e-mail/pseudo
    private function existsByEmailOrUsername(string $email, string $pseudo): bool
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur FROM utilisateur WHERE mail = :mail OR pseudo = :pseudo LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute(['mail' => $email, 'pseudo' => $pseudo]);
            return (bool) $query->fetch();
        } catch (PDOException $e) {
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : insertion du compte avec rôle user
    private function insertUser(Utilisateur $utilisateur): bool
    {
        $db = config::getConnexion();
        $sql = "INSERT INTO utilisateur (nom, prenom, pseudo, mail, mot_de_passe_hash, role, statut)
                VALUES (:nom, :prenom, :pseudo, :mail, :mp, 'user', 'actif')";

        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'pseudo' => $utilisateur->getPseudo(),
                'mail' => $utilisateur->getEmail(),
                'mp' => $utilisateur->getMp(),
            ]);
        } catch (PDOException $e) {
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : inscription, Houssem : vérification CSRF
    public function register(array $post): array
    {
        $errors = [];
        if (!AuthController::verifyCsrfToken($post['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) { return ['Jeton de sécurité invalide. Rechargez la page.']; }
        foreach (['prenom', 'nom', 'pseudo', 'email', 'mot_de_passe', 'confirmer_mot_de_passe'] as $field) {
            if (!is_string($post[$field] ?? null)) { return ['Tous les champs sont obligatoires.']; }
        }

        $prenom = trim($post['prenom'] ?? '');
        $nom = trim($post['nom'] ?? '');
        $pseudo = trim($post['pseudo'] ?? '');
        $email = trim($post['email'] ?? '');
        $motDePasse = $post['mot_de_passe'] ?? '';
        $confirmation = $post['confirmer_mot_de_passe'] ?? '';

        if ($prenom === '' || $nom === '' || $pseudo === '' || $email === '' || $motDePasse === '') {
            $errors[] = "Tous les champs sont obligatoires.";
        }
        if (mb_strlen($prenom) > 100 || mb_strlen($nom) > 100) {
            $errors[] = "Le nom et le prénom ne doivent pas dépasser 100 caractères.";
        }
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $pseudo)) {
            $errors[] = "Le pseudo doit contenir de 3 à 50 caractères (lettres, chiffres, _ ou -).";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            $errors[] = "L'adresse e-mail n'est pas valide.";
        }
        if (strlen($motDePasse) < 8 || strpos($motDePasse, chr(0)) !== false) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if ($motDePasse !== $confirmation) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->existsByEmailOrUsername($email, $pseudo)) {
            $errors[] = "Cet e-mail ou ce pseudo est déjà utilisé.";
            return $errors;
        }

        // Hachage sécurisé du mot de passe (bcrypt)
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

        $utilisateur = new Utilisateur($nom, $prenom, $email, $hash, new DateTimeImmutable("now"), "");
        $utilisateur->setPseudo($pseudo);

        if (!$this->insertUser($utilisateur)) {
            $errors[] = "Une erreur est survenue lors de la création du compte.";
        }

        return $errors;
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : blocage
    public function bloquerUtilisateur(int $id): array
    {
        return $this->changerStatut($id, 'bloque');
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : déblocage
    public function debloquerUtilisateur(int $id): array
    {
        return $this->changerStatut($id, 'actif');
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : lecture du statut
    private function findStatutById(int $id): ?string
    {
        $db = config::getConnexion();
        $sql = "SELECT statut FROM utilisateur WHERE id_utilisateur = :id LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $ligne = $query->fetch();
        } catch (PDOException $e) {
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }

        return $ligne ? $ligne['statut'] : null;
    }
    // Eya : inscription et blocage/déblocage.
    // Eya : changement de statut et protection de son compte
    private function changerStatut(int $id, string $statut): array
    {
        $errors = [];

        // Seul un administrateur connecté peut bloquer / débloquer
        if (!self::hasRole('admin')) {
            $errors[] = "Accès refusé : action réservée aux administrateurs.";
            return $errors;
        }

        $statutActuel = $this->findStatutById($id);
        if ($statutActuel === null) {
            $errors[] = "Ce compte n'existe pas.";
            return $errors;
        }

        // Un admin ne doit pas pouvoir se bloquer lui-même (il ne pourrait plus se connecter)
        if ($statut === 'bloque' && (int) ($_SESSION['utilisateur']['id_utilisateur'] ?? 0) === $id) {
            $errors[] = "Vous ne pouvez pas bloquer votre propre compte.";
            return $errors;
        }

        if ($statutActuel === $statut) {
            $errors[] = $statut === 'bloque' ? "Ce compte est déjà bloqué." : "Ce compte est déjà actif.";
            return $errors;
        }

        $db = config::getConnexion();
        $sql = "UPDATE utilisateur SET statut = :statut WHERE id_utilisateur = :id";

        try {
            $query = $db->prepare($sql);
            $query->execute(['statut' => $statut, 'id' => $id]);
        } catch (PDOException $e) {
            error_log('SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }

        return $errors;
    }

    // ==============================================================
    // Mohamed : AJOUT D'UN COMPTE ET CHANGEMENT DE RÔLE (back-office)
    // ==============================================================

    // Mohamed : e-mail déjà utilisé ?
    public function emailExiste(string $email, ?int $exclureId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilisateur WHERE mail = :mail';
        $params = ['mail' => $email];
        if ($exclureId !== null) {
            $sql .= ' AND id_utilisateur != :id';
            $params['id'] = $exclureId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Mohamed : validation du formulaire addUser.php
    public function verifierAjout(array $data): array
    {
        $errors = [];
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $mail = trim((string) ($data['mail'] ?? $data['email'] ?? ''));
        $password = (string) ($data['mot_de_passe'] ?? $data['password'] ?? '');
        $confirmPassword = isset($data['confirmer_mot_de_passe']) ? (string) $data['confirmer_mot_de_passe'] : null;
        $role = (string) ($data['role'] ?? 'user');
        $statut = (string) ($data['statut'] ?? 'actif');

        if ($nom === '') {
            $errors['nom'] = "Le nom est obligatoire.";
        } elseif (mb_strlen($nom) > 100) {
            $errors['nom'] = "Le nom ne doit pas dépasser 100 caractères.";
        }
        if ($prenom === '') {
            $errors['prenom'] = "Le prénom est obligatoire.";
        } elseif (mb_strlen($prenom) > 100) {
            $errors['prenom'] = "Le prénom ne doit pas dépasser 100 caractères.";
        }
        if ($mail === '') {
            $errors['mail'] = "L'adresse e-mail est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['mail'] = "L'adresse e-mail n'est pas valide.";
        } elseif (mb_strlen($mail) > 254) {
            $errors['mail'] = "L'e-mail ne doit pas dépasser 254 caractères.";
        } elseif ($this->emailExiste($mail)) {
            $errors['mail'] = "Cette adresse e-mail est déjà utilisée par un autre aventurier.";
        }
        if ($password === '') {
            $errors['mot_de_passe'] = "Le mot de passe est obligatoire.";
        } elseif (strlen($password) < 8) {
            // Intégration : même longueur minimale que updateUtilisateur().
            $errors['mot_de_passe'] = "Le mot de passe doit contenir au moins 8 caractères.";
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

    // Mohamed : insertion d'un compte créé par un admin (mot de passe déjà haché par addUser.php)
    public function addUtilisateur(Utilisateur $utilisateur): bool
    {
        // Intégration : img_profil est une clé étrangère vers images. Si l'avatar demandé
        // n'existe pas dans cette table, le compte est créé sans avatar plutôt que d'échouer.
        $img = $utilisateur->getImgProfil();
        if ($img !== '') {
            $check = $this->db->prepare('SELECT 1 FROM images WHERE id_image = :img LIMIT 1');
            $check->execute(['img' => $img]);
            if (!$check->fetchColumn()) {
                $img = '';
            }
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO utilisateur (nom, prenom, mail, mot_de_passe_hash, role, statut, total_xp, niveau, img_profil)
                 VALUES (:nom, :prenom, :mail, :mp, :role, :statut, :total_xp, :niveau, :img_profil)'
            );
            $ok = $stmt->execute([
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'mail' => $utilisateur->getEmail(),
                'mp' => $utilisateur->getMp(),
                'role' => $utilisateur->getRole() ?: 'user',
                'statut' => $utilisateur->getStatut() ?: 'actif',
                'total_xp' => $utilisateur->getXp(),
                'niveau' => max(1, $utilisateur->getNiveau()),
                'img_profil' => $img !== '' ? $img : null,
            ]);
            if ($ok) {
                $utilisateur->setId((int) $this->db->lastInsertId());
            }
            return $ok;
        } catch (PDOException $e) {
            error_log('Ajout utilisateur : ' . $e->getMessage());
            return false;
        }
    }

    // Mohamed : recherche d'un compte par e-mail (changeRole.php)
    public function getUtilisateurParEmail(string $email): ?Utilisateur
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => trim($email)]);
        $row = $stmt->fetch();
        return $row ? Utilisateur::fromArray($row) : null;
    }

    // Mohamed : changement de rôle, avec la règle de Darine (pas de rétrogradation de soi-même)
    public function changerRole(int $id, string $nouveauRole): bool
    {
        if ($id < 1 || !in_array($nouveauRole, ['user', 'admin'], true)
            || !empty($this->verifierChangementRole($id, $nouveauRole))) {
            return false;
        }
        try {
            $stmt = $this->db->prepare('UPDATE utilisateur SET role = :role WHERE id_utilisateur = :id');
            $stmt->execute(['role' => $nouveauRole, 'id' => $id]);
            return $stmt->rowCount() === 1;
        } catch (PDOException $e) {
            error_log('Changement de rôle : ' . $e->getMessage());
            return false;
        }
    }
}
