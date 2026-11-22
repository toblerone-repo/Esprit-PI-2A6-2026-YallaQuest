<?php

include_once __DIR__ . '/../Models/utilisateur.php';
include_once __DIR__ . '/../config.php';

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
    // Utilisé par Views/back-office/pages/liste_utilisateurs.php
    // ==============================================================

    /**
     * Récupère tous les utilisateurs (liste du back-office).
     * @return Utilisateur[]
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

    /**
     * Id de l'utilisateur connecté, lu dans la session (null si personne).
     * La connexion (AuthController::login) remplit $_SESSION['user_id'].
     * (session_start() doit avoir été appelé avant)
     */
    public static function getIdConnecte(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        return null;
    }

    /**
     * true si $id est le compte actuellement connecté.
     * Utilisé par liste_utilisateurs.php pour marquer la ligne « (vous) ».
     */
    public static function estSoiMeme(int $id): bool
    {
        $idConnecte = self::getIdConnecte();
        return $idConnecte !== null && $idConnecte === $id;
    }

    // ==============================================================
    // [SUPPRESSION] Delete (DELETE) du CRUD, comme deletePersonne() du chapitre 6.
    // Appelé par Views/back-office/pages/supprimer_utilisateur.php :
    //   $utilisateurC = new UtilisateurC();
    //   $errors = $utilisateurC->verifierSuppression($id);
    //   if (empty($errors)) { $nb = $utilisateurC->deleteUtilisateur($id); }
    // ==============================================================

    // [DARINE · RÈGLE ADMIN] Un admin ne peut pas supprimer son propre compte.
    // Renvoie un tableau d'erreurs (vide = suppression autorisée).
    function verifierSuppression($idCible)
    {
        $errors = [];

        if (self::estSoiMeme($idCible)) {
            $errors[] = "Vous ne pouvez pas supprimer votre propre compte.";
        }

        return $errors;
    }

    // Supprime l'utilisateur dont l'id est $id.
    // Renvoie rowCount() : le nombre de lignes supprimées (1 = supprimé, 0 = id introuvable).
    // Le cours affiche ce nombre avec echo ; ici on le renvoie, car la page qui appelle
    // cette méthode redirige ensuite vers la liste (rien ne doit être affiché avant header()).
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

    function updateUtilisateur(Utilisateur $utilisateur, int $id)
    {
        $db = config::getConnexion();
        $sql = "UPDATE utilisateur SET 
    nom = :nom, 
    prenom = :prenom, 
    mail = :mail, 
    mot_de_passe_hash = :mp
    WHERE id_utilisateur = :id";


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
};
