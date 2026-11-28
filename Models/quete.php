<?php  

    class Quete {
        private ?int $id; 
        private string $titre; 
        private string $difficulte; 
        private int $xp; 
        private int $id_comp = 0;
        private int $id_cat = 0;


        function __construct(string $titre, string $difficulte, int $xp, int $id=NULL)
        {
            $this->id = $id; 
            $this->titre =  $titre; 
            $this->difficulte = $difficulte; 
            $this->xp = $xp; 
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

        // Guildes : compétence et catégorie d'une quête proposée
        function setIdComp(int $id){
            $this->id_comp = $id;
        }

        function setIdCat(int $id){
            $this->id_cat = $id;
        }

        function setId(?int $id){
            $this->id = $id;
        }

        



        





    }


?>