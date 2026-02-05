<?php

namespace App\Controllers;

use App\Core\Session;
use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Services\Uploader;
use App\Lib\Security\Csrf;

use function App\Lib\Security\sanitize_text_field;
use function App\Lib\Security\sanitize_post_data;

class AdminPostController extends AbstractController
{
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private Uploader $uploader;

    public function __construct()
    {
        $this->postRepository = new PostRepository();
        $this->userRepository = new UserRepository();
        $this->uploader = new Uploader();
    }

    public function process(Request $request): Response
    {
        $path = $request->getPath();
        $method = $request->getMethod();

        if ($path === '/admin/posts' && $method === 'GET') {
            return $this->index($request);
        }

        if ($path === '/admin/posts/create') {
            if ($method === 'GET') {
                return $this->showCreateForm();
            }
            if ($method === 'POST') {
                return $this->handleCreate();
            }
        }

        if (preg_match('#^/admin/posts/(\d+)/edit$#', $path, $matches)) {
            $id = (int)$matches[1];
            if ($method === 'GET') {
                return $this->showEditForm($id);
            }
            if ($method === 'POST') {
                return $this->handleUpdate($id);
            }
        }

        if (preg_match('#^/admin/posts/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
            return $this->handleDelete((int)$matches[1]);
        }

        return new Response("Page not found", 404);
    }

    public function index(Request $request): Response
    {
        $posts = $this->postRepository->findAll(["user" => [
            "table" => "user",
            "condition" => "p.user",
            "fields" => ["id", "email"]
        ]]);


        return $this->render('admin/posts/index', [
            'posts' => $posts,
            'csrfToken' => Csrf::generate()
        ], 'admin');
    }

    private function showCreateForm(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/posts/create', [
            'error' => null,
            'csrfToken' => Csrf::generate(),
            'users' => $users
        ], 'admin');
    }

    private function handleCreate(): Response
    {
        try {
            Csrf::verify($_POST['csrf_token'] ?? '');
        } catch (\Exception $e) {
            return new Response('Invalid CSRF token', 403);
        }

        $post_data = sanitize_post_data();
        $title = trim($post_data['title'] ?? '');
        $content = $post_data['content'] ?? '';
        $userId = $post_data['user_id'] ?? null;

        if ($title === '') {
            $users = $this->userRepository->findAll();
            return $this->render('admin/posts/create', [
                'error' => 'Le titre est obligatoire.',
                'csrfToken' => Csrf::generate(),
                'users' => $users
            ], 'admin');
        }

        $user = null;
        if ($userId) {
            $user = $this->userRepository->find($userId);
        }

        if (!$user) {
            $userSession = Session::get('user');
            $user = $this->userRepository->find($userSession['id']);
        }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $users = $this->userRepository->findAll();
                return $this->render('admin/posts/create', [
                    'error' => "Erreur d'upload.",
                    'csrfToken' => Csrf::generate(),
                    'users' => $users
                ], 'admin');
            }
            try {
                $image = $this->uploader->upload($_FILES['image']);
            } catch (\Exception $e) {
                $users = $this->userRepository->findAll();
                return $this->render('admin/posts/create', [
                   'error' => "Erreur d'upload : " . $e->getMessage(),
                   'csrfToken' => Csrf::generate(),
                   'users' => $users
                ], 'admin');
            }
        }

        $this->postRepository->create([
            'title' => $title,
            'slug' => $this->slugify($title),
            'content' => $content,
            'image' => $image,
            'published' => array_key_exists('published', $_POST),
            'createdAt' => date('Y-m-d H:i:s'),
            'user' => $user
        ]);

        return new Response('', 302, ['Location' => '/admin/posts']);
    }

    private function showEditForm(int $id): Response
    {
        $post = $this->postRepository->findById($id);
        $users = $this->userRepository->findAll();

        if (!$post) {
            return new Response(PostRepository::POST_NOT_FOUND, 404);
        }

        return $this->render('admin/posts/edit', [
            'post' => $post,
            'users' => $users,
            'error' => null,
            'csrfToken' => Csrf::generate()
        ], 'admin');
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

        $post_data = sanitize_post_data();
        $title = trim($post_data['title'] ?? '');
        $content = $post_data['content'] ?? '';
        $user_id = $post_data['user_id'] ?? null;

        if ($title === '') {
            $users = $this->userRepository->findAll();
            return $this->render('admin/posts/edit', [
                'post' => $post,
                'users' => $users,
                'error' => 'Le titre est obligatoire.',
                'csrfToken' => Csrf::generate()
            ], 'admin');
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                 $users = $this->userRepository->findAll();
                 return $this->render('admin/posts/edit', [
                       'post' => $post,
                       'users' => $users,
                       'error' => "Erreur lors du téléchargement de l'image.",
                       'csrfToken' => Csrf::generate()
                 ], 'admin');
            }
            try {
                $image = $this->uploader->upload($_FILES['image']);
                $post->setImage($image);
            } catch (\Exception $e) {
                $users = $this->userRepository->findAll();
                return $this->render('admin/posts/edit', [
                   'post' => $post,
                   'users' => $users,
                   'error' => "Erreur d'upload : " . $e->getMessage(),
                   'csrfToken' => Csrf::generate()
                ], 'admin');
            }
        }

        $post->setTitle($title);
        $post->setSlug($this->slugify($title));
        $post->setContent($content);
        $post->setPublished(array_key_exists('published', $_POST));
        $post->setUser($user_id);

        $this->postRepository->update($post);

        return new Response('', 302, ['Location' => '/admin/posts']);
    }

    private function handleDelete(int $id): Response
    {
        try {
            Csrf::verify($_POST['csrf_token'] ?? '');
        } catch (\Exception $e) {
            return new Response('Invalid CSRF token', 403);
        }

        $post = $this->postRepository->findById($id);
        if ($post) {
            $this->postRepository->remove($post);
        }

        return new Response('', 302, ['Location' => '/admin/posts']);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}
