<?php

namespace App\Controllers;

use App\Core\Session;
use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Lib\Security\Csrf;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;

class PostController extends AbstractController
{
    private PostRepository $postRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->postRepository = new PostRepository();
        $this->userRepository = new UserRepository();
    }

    public function process(Request $request): Response
    {
        $path   = $request->getPath();
        $method = $request->getMethod();

        if ($path === '/api/posts' && $method === 'GET') {
            $this->index();
            exit;
        }

        if ($path === '/posts' && $method === 'GET') {
            return $this->listPosts();
        }

        if ($path === '/posts/create' && $method === 'GET') {
            return $this->showCreateForm();
        }

        if ($path === '/posts/create' && $method === 'POST') {
            return $this->handleCreate();
        }
        if (preg_match('#^/posts/(\d+)/edit$#', $path, $matches)) {
            $id = (int) $matches[1];

            if ($method === 'GET') {
                return $this->showEditForm($id);
            }

            if ($method === 'POST') {
                return $this->handleUpdate($id);
            }
        }
        if (preg_match('#^/posts/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
            return $this->handleDelete((int) $matches[1]);
        }

        return new Response('Page not found', 404);
    }

    private function index(): void
    {
        $posts = $this->postRepository->findAll();

        $data = array_map(static function ($post) {
            return [
                'id'    => $post->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug(),
            ];
        }, $posts);

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($data);
    }

    private function listPosts(): Response
    {
        $userSession = Session::get('user');
        $userId = $userSession['id'];
        $posts = $this->postRepository->findBy((["user" => $userId]), ["user" => [
            "table" => "user",
            "condition" => "p.user",
            "fields" => ["id", "email"]
        ]]);

        return $this->render('post/index', [
            'posts'     => $posts,
            'csrfToken' => Csrf::generate()
        ], "home");
    }

    private function showCreateForm(): Response
    {
        return $this->render('post/create', [
            'error'     => null,
            'csrfToken' => Csrf::generate()
        ], "home");
    }

    private function handleCreate(): Response
    {
        try {
            Csrf::verify($_POST['csrf_token'] ?? '');
        } catch (\Exception $e) {
            return new Response('Invalid CSRF token', 403);
        }

        $title   = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if ($title === '') {
            return $this->render('post/create', [
                'error'     => 'Le titre est obligatoire.',
                'csrfToken' => Csrf::generate()
            ]);
        }

        $userSession = Session::get('user');
        $user = $userSession ? $this->userRepository->find($userSession['id']) : null;

        $this->postRepository->create([
            'title'     => $title,
            'slug'      => $this->slugify($title),
            'content'   => $content,
            'published' => array_key_exists('published', $_POST),
            'createdAt' => date('Y-m-d H:i:s'),
            'user'      => $user
        ]);

        return new Response('', 302, ['Location' => '/posts']);
    }

    private function showEditForm(int $id): Response
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return new Response(PostRepository::POST_NOT_FOUND, 404);
        }

        return $this->render('post/edit', [
            'post'      => $post,
            'error'     => null,
            'csrfToken' => Csrf::generate()
        ], "home");
    }

    private function handleUpdate(int $id): Response
    {
        try {
            Csrf::verify($_POST['csrf_token'] ?? '');
        } catch (\Exception $e) {
            return new Response('Invalid CSRF token', 403);
        }

        $post = $this->postRepository->findById($id);

        if (!$post) {
            return new Response(PostRepository::POST_NOT_FOUND, 404);
        }

        $title   = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if ($title === '') {
            return $this->render('post/edit', [
                'post'      => $post,
                'error'     => 'Le titre est obligatoire.',
                'csrfToken' => Csrf::generate()
            ]);
        }

        $post->setTitle($title);
        $post->setSlug($this->slugify($title));
        $post->setContent($content);
        $post->setPublished(array_key_exists('published', $_POST));

        $this->postRepository->update($post);

        return new Response('', 302, ['Location' => '/posts']);
    }

    private function handleDelete(int $id): Response
    {
        try {
            Csrf::verify($_POST['csrf_token'] ?? '');
        } catch (\Exception $e) {
            return new Response('Invalid CSRF token', 403);
        }

        $post = $this->postRepository->findById($id);

        if (!$post) {
            return new Response(PostRepository::POST_NOT_FOUND, 404);
        }

        $this->postRepository->remove($post);

        return new Response('', 302, ['Location' => '/posts']);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        return trim($text, '-');
    }
}