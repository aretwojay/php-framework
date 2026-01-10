<?php
require __DIR__ . '/vendor/autoload.php';

use Core\Database;

try {
    $pdo = Database::getInstance();
    echo "Connexion MySQL OK\n";
} catch (\PDOException $e) {
    echo "Erreur MySQL : " . $e->getMessage() . "\n";
}
