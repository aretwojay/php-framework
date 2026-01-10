<?php
namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {

            // Récupération des infos depuis config/database.json
            $configPath = __DIR__ . '/../../config/database.json';
            if (!file_exists($configPath)) {
                throw new PDOException("Le fichier de configuration $configPath est introuvable");
            }

            $config = json_decode(file_get_contents($configPath), true);

            $host = $config['host'] ?? 'db';
            $port = $config['port'] ?? 3306;
            $dbname = $config['database'] ?? 'mydb';
            $user = $config['username'] ?? 'root';
            $pass = $config['password'] ?? '';

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Connexion MySQL impossible : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}

