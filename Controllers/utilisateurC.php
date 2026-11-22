<?php

include_once __DIR__ . '/../Models/utilisateur.php';
include_once __DIR__ . '/../config.php';

class UtilisateurC
{

    function updateUtilisateur(Utilisateur $utilisateur, int $id)
    {
        $db = config::getConnexion();
        $sql = "UPDATE utilisateur SET 
    nom = :nom, 
    prenom = :prenom, 
    mail = :mail, 
    mot_de_passe_hash = :mp
    WHERE id= :id";


        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'mail' => $utilisateur->getEmail(),
                'mp' => $utilisateur->getMp(),
            ]);
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        };
    }

    // Renvoie l'utilisateur (sans le hash) si l'e-mail et le mot de passe sont corrects, sinon null.
    // [BLOCAGE] La colonne s'appelle id_utilisateur (et non id) ; on lit aussi role et statut
    // pour que login.php puisse refuser un compte bloqué et savoir qui est admin.
    function login(string $mail, string $mp): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur, nom, prenom, mail, role, statut, mot_de_passe_hash FROM utilisateur WHERE mail = :mail LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute(['mail' => $mail]);
            $utilisateur = $query->fetch();
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        }

        if (!$utilisateur || !password_verify($mp, $utilisateur['mot_de_passe_hash'])) {
            return null;
        }

        unset($utilisateur['mot_de_passe_hash']);
        return $utilisateur;
    }

    // ==============================================================
    // [INSCRIPTION] SESSION (workshop 4.1)
    // ==============================================================

    // true si un utilisateur est connecté (login.php remplit $_SESSION['utilisateur']).
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['utilisateur']);
    }

    // true si l'utilisateur connecté a le rôle demandé ('admin' ou 'user').
    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['utilisateur']['role'] ?? null) === $role;
    }

    // ==============================================================
    // [INSCRIPTION] ACCÈS AUX DONNÉES (requêtes préparées)
    // ==============================================================

    // true si l'e-mail OU le pseudo est déjà utilisé par un compte.
    private function existsByEmailOrUsername(string $email, string $pseudo): bool
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur FROM utilisateur WHERE mail = :mail OR pseudo = :pseudo LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute(['mail' => $email, 'pseudo' => $pseudo]);
            return (bool) $query->fetch();
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    // Insère le nouvel utilisateur. Le rôle est forcé à 'user' et le statut à 'actif'.
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
            die("Erreur: " . $e->getMessage());
        }
    }

    // ==============================================================
    // [INSCRIPTION] REGISTER (workshop 4.1)
    // Renvoie un tableau d'erreurs : vide = compte créé.
    // ==============================================================
    public function register(array $post): array
    {
        $errors = [];

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
        if (strlen($motDePasse) < 8) {
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

    // ==============================================================
    // [BLOCAGE] BLOQUER / DÉBLOQUER UN COMPTE
    // Renvoient un tableau d'erreurs : vide = statut modifié.
    // ==============================================================

    public function bloquerUtilisateur(int $id): array
    {
        return $this->changerStatut($id, 'bloque');
    }

    public function debloquerUtilisateur(int $id): array
    {
        return $this->changerStatut($id, 'actif');
    }

    // Statut actuel du compte ('actif' ou 'bloque'), ou null si le compte n'existe pas.
    private function findStatutById(int $id): ?string
    {
        $db = config::getConnexion();
        $sql = "SELECT statut FROM utilisateur WHERE id_utilisateur = :id LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $ligne = $query->fetch();
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        }

        return $ligne ? $ligne['statut'] : null;
    }

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
            die("Erreur: " . $e->getMessage());
        }

        return $errors;
    }
};
