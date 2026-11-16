<?php
// Connexion PDO unique, partagée par tous les modules (Communauté, Objectifs, …).
// Base : back-office/src/sql/yallaquest.sql. Modifier ici l'hôte, la base ou les identifiants.
date_default_timezone_set('Africa/Tunis');

class Config
{
    private static $pdo = null;
    private static $failed = false;

    const HOST = '127.0.0.1';
    const DATABASE = 'yallaquest';
    const USER = 'root';
    const PASSWORD = '';

    // Renvoie la connexion, ou null si la base est indisponible : chaque modèle
    // affiche alors une liste vide au lieu d'arrêter la page.
    public static function getConnexion()
    {
        if (self::$pdo !== null || self::$failed) {
            return self::$pdo;
        }
        try {
            self::$pdo = new PDO(
                'mysql:host=' . self::HOST . ';dbname=' . self::DATABASE . ';charset=utf8mb4',
                self::USER,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            error_log('YallaQuest : connexion MySQL impossible : ' . $e->getMessage());
            self::$failed = true;
        }
        return self::$pdo;
    }
}
