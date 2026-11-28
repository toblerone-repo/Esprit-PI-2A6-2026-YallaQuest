<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/categorie.php';


class CategorieC
{

    function listCategories()
    {
        $sql = "SELECT * FROM categorie";

        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }


    function ajouterCategorie(Categorie $categorie)
    {
        $sql = "INSERT INTO categorie (nom, description)
                VALUES (:nom, :description)";

        $db = config::getConnexion();

        try {
            $requete = $db->prepare($sql);
            return $requete->execute(
                [
                    'nom' => $categorie->getNom(),
                    'description' => $categorie->getDescription()
                ]
            );
        } catch (PDOException $e) {
            return false;
        }
    }

    function modifierCategorie(Categorie $categorie, int $id_categorie)
    {
        try {
            $db = config::getConnexion();
            $requete = $db->prepare('UPDATE categorie
                SET nom = :nom, description = :description
                WHERE id_categorie = :id');
            return $requete->execute([
                'nom' => $categorie->getNom(),
                'description' => $categorie->getDescription(),
                'id' => $id_categorie
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function supprimerCategorie(int $id_categorie)
    {
        $sql = 'DELETE FROM categorie WHERE id_categorie = :id';
        $db = config::getConnexion();
        try {

            $requete = $db->prepare($sql);
            $requete->execute(['id' => $id_categorie]);
            return $requete->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    function chargerCategorie(int $id_categorie)
    {

        $sql = 'SELECT * FROM categorie WHERE id_categorie = :id';
        $db = config::getConnexion();
        try {

            $requete = $db->prepare($sql);
            $requete->execute(['id' => $id_categorie]);
            return $requete->fetch();
        } catch (PDOException $e) {
            return null;
        };
    }
}
