<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/quete.php';

class QueteC
{

    function listquetes()
    {
        $sql = "SELECT q.id_quete, q.titre, q.difficulte, q.xp,
                       q.id_categorie, q.id_competence,
                       c.nom AS categorie, co.nom AS competence
                FROM quete AS q
                JOIN categorie AS c ON q.id_categorie = c.id_categorie
                JOIN competence AS co ON q.id_competence = co.id_competence";
        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    function addQuete(Quete $quete)
    {
        $sql = "INSERT INTO quete (titre, difficulte, xp, id_categorie, id_competence)
                VALUES (:titre, :difficulte, :xp, :id_categorie, :id_competence)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(
                [
                    'titre' => $quete->getTitre(),
                    'difficulte' => $quete->getDifficulte(),
                    'xp' => $quete->getXp(),
                    'id_categorie' => $quete->getIdCat(),
                    'id_competence' => $quete->getIdComp()
                ]
            );
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    function updateQuete(Quete $quete, int $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE quete
                SET titre = :titre, difficulte = :difficulte, xp = :xp,
                    id_categorie = :id_cat, id_competence = :id_comp
                WHERE id_quete = :id');
            $query->execute([
                'titre' => $quete->getTitre(),
                'difficulte' => $quete->getDifficulte(),
                'xp' => $quete->getXp(),
                'id_cat' => $quete->getIdCat(),
                'id_comp' => $quete->getIdComp(),
                'id' => $id
            ]);
            echo $query->rowCount() . " records UPDATED";
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    function deleteQuete(int $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('DELETE FROM quete WHERE id_quete = :id');
            $query->execute(['id' => $id]);
            echo $query->rowCount() . " record(s) DELETED";
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }


}
