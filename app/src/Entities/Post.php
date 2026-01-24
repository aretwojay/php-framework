<?php

namespace App\Entities;

use App\Lib\Annotations\ORM\ORM;
use App\Lib\Annotations\ORM\Id;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\AutoIncrement;
use App\Lib\Entities\AbstractEntity;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}