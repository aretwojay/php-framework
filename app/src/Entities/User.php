<?php

namespace App\Entities;

use App\Lib\Annotations\ORM\Id;
use App\Lib\Entities\AbstractEntity;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\AutoIncrement;
use App\Lib\Annotations\ORM\ORM;

#[ORM]
class User extends AbstractEntity
{
    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'varchar', size: 255)]
    private string $email;

    #[Column(type: 'varchar', size: 255)]
    private string $password; 

    #[Column(type: 'varchar', size: 50)]
    private string $role = 'user';

    #[Column(type: 'datetime')]
    private string $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
