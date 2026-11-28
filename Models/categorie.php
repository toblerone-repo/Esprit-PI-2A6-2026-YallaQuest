<?php  

    class Categorie {
        private ?int $id_categorie; 
        private string $nom; 
        private string $description; 


        function __construct(string $nom, string $description, ?int $id_categorie = null)
        {
            $this->id_categorie = $id_categorie;
            $this->nom =  $nom; 
            $this->description = $description; 
         

        } 

        function getId(){
            return $this->id_categorie; 
        } 

        function getNom(){
            return $this->nom; 
        }

        function setNom(string $nom){
            $this->nom = $nom; 
        }

        function getDescription(){
            return $this->description; 
        }

        function setDescription(string $description){
            $this->description = $description; 
        }

    }


?>
