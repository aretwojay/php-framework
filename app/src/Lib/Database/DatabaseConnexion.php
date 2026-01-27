<?php

namespace App\Lib\Database;

class DatabaseConnexion {
    private \PDO | null $connexion;

    public function setConnexion(Dsn $dsn): void {
        $this->connexion = new \PDO($dsn->getDsn(), $dsn->getUser(), $dsn->getPassword());

        $driver = $this->connexion->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $version = $this->connexion->getAttribute(\PDO::ATTR_SERVER_VERSION);

        if ($driver !== 'pgsl' || !str_starts_with($version , '18')){
            throw new \Exception('This ORM requires PostgreSQL 18',500);
        }
    }

    public function getConnexion(): \PDO {
        return $this->connexion;
    }

    public function deleteConnexion(): void {
        $this->connexion = null;
    }
}
