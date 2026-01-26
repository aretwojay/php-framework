<?php

namespace App\Repositories;

use App\Lib\Repositories\AbstractRepository;
use App\Entities\Post;

class PostRepository extends AbstractRepository
{
    public const POST_NOT_FOUND = 'Post not found';

    public function findById(int $id): ?Post
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function create(array $data): string
    {
        $post = new Post();

        $post->setTitle($data['title']);
        $post->setSlug($data['slug']);
        $post->setContent($data['content']);
        $post->setPublished($data['published'] ?? false);
        $post->setCreatedAt($data['createdAt']);
        if (isset($data['user'])) {
            $post->setUser($data['user']);
        }

        return $this->save($post);
    }

    public function updatePost(int $id, array $data): void
    {
        $post = $this->findById($id);

        if (!$post) {
            throw new \Exception(self::POST_NOT_FOUND, 404);
        }

        $post->setTitle($data['title']);
        $post->setSlug($data['slug']);
        $post->setContent($data['content']);
        $post->setPublished($data['published'] ?? false);

        $this->update($post);
    }

    public function deleteById(int $id): void
    {
        $post = $this->findById($id);

        if (!$post) {
            throw new \Exception(self::POST_NOT_FOUND, 404);
     }

        $this->remove($post);
    }
    public function findPublished(): array
    {
        return $this->findBy(['published' => 1]);
    }
}