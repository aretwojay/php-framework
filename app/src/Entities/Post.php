<?php

namespace App\Entities;

use App\Lib\Annotations\ORM\ORM;
use App\Lib\Annotations\ORM\Id;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\AutoIncrement;
use App\Lib\Annotations\ORM\References;
use App\Lib\Entities\AbstractEntity;
use App\Entities\User;

#[ORM]
class Post extends AbstractEntity
{
    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    protected int $id;

    #[Column(type: 'varchar', size: 255)]
    protected string $title;

    #[Column(type: 'varchar', size: 255, unique: true)]
    protected string $slug;

    #[Column(type: 'text')]
    protected string $content;

    #[Column(type: 'datetime')]
    protected string $createdAt;

    #[Column(type: 'boolean')]
    protected bool $published = false;

    #[Column(type: 'int')]
    #[References(class: User::class, property: 'id')]
    protected User|int $user;

    public function getId(): string|int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
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

    public function getUser(): User|int
    {
        return $this->user;
    }

    public function setUser(User|int $user): self
    {
        $this->user = $user;
        return $this;
    }
}
