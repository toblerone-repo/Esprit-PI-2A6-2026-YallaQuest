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
    mot_de_passe_hash = :mp, 
    img_profil = :img_profil
    WHERE id_utilisateur = :id";


        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'mail' => $utilisateur->getEmail(),
                'mp' => $utilisateur->getMp(),
                'img_profil' => $utilisateur->getImgProfil(),
            ]);
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        };
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
    
    // Renvoie l'utilisateur (sans le hash) si l'e-mail et le mot de passe sont corrects, sinon null.
    function login(string $mail, string $mp): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT id, nom, prenom, mail, mot_de_passe_hash FROM utilisateur WHERE mail = :mail LIMIT 1";

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
};

?>