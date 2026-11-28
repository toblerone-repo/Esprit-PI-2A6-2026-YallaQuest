<?php
// Darine : connexion au projet. Houssem : configuration PDO sécurisée.
class config
{   private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password ="";
            // [DARINE · CORRECTION] "gestionproduits" (base de l'exemple du cours) -> "yalla_quest",
            // la base du projet. Ajout de charset=utf8mb4 (accents) comme dans gestion_produit/config.php.
            $dbname="yalla_quest";
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                        $username,
                        $password
                   
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
               
            } catch (Exception $e) {
                error_log('Connexion SQL : ' . $e->getMessage());
                http_response_code(500);
                die('Connexion à la base de données indisponible.');
            }
        }
        return self::$pdo;
    }
}
