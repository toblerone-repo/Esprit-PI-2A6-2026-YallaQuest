<?php
date_default_timezone_set('Africa/Tunis');

class Config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (isset(self::$pdo)) {
            return self::$pdo;
        }

        $host = '127.0.0.1';
        $database = ''; // Nom de ta base MySQL, quand tu voudras la connecter.
        $user = 'root';
        $password = '';

        if ($database == '') {
            return null;
        }

        self::$pdo = new PDO(
            'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4',
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return self::$pdo;
    }
}
