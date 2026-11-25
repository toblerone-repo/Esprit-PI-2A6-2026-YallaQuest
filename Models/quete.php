<?php  

    class Quete {
        private ?int $id; 
        private string $titre; 
        private string $difficulte; 
        private int $xp; 
        private int $id_comp; 
        private int $id_cat; 


        function __construct(string $titre, string $difficulte, int $xp, int $id_cat, int $id_comp, int $id=NULL)
        {
            $this->id = $id; 
            $this->titre =  $titre; 
            $this->difficulte = $difficulte; 
            $this->xp = $xp; 
            $this->id_cat = $id_cat; 
            $this->id_comp = $id_comp; 

        } 

        function getId(){
            return $this->id; 
        } 

        function getTitre(){
            return $this->titre; 
        }

        function setTitre(string $titre){
            $this->titre = $titre; 
        }

        function getDifficulte(){
            return $this->difficulte; 
        }

        function setDifficulte(string $difficulte){
            $this->difficulte = $difficulte; 
        }

        
        function getXp(){
            return $this->xp; 
        }

        function setXp(int $xp){
            $this->xp = $xp; 
        }

        function getIdComp(){
            return $this->id_comp; 
        }

        function getIdCat(){
            return $this->id_cat; 
        }

        



        





    }


?>