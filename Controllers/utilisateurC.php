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

    function listerImagesProfil()
    {
        $sql = "SELECT * FROM images";
        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list;
        } catch (PDOException $e) {
            die("Erreur: " . $e->getMessage());
        };
    }
};
