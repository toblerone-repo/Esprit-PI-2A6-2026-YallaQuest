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
};
