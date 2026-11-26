<?php

class CategorieC {

    function listCategories() {
        $sql = "SELECT * FROM categorie"; 

        $db = config::getConnexion(); 

        try {
            $list = $db->query($sql); 
            return $list->fetchAll(); 
        }catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}



?>