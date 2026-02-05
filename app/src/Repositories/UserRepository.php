<?php

namespace App\Repositories;

use App\Lib\Repositories\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function findByEmail(string $email)
    {
        $table = $this->findBy(["email" => $email]);
        if ($table) {
            return $table[0];
        }

        return null;
    }
}
