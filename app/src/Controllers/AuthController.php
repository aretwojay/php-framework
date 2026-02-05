<?php

namespace App\Controllers;

use App\Core\Session;
use App\Entities\User;
use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Repositories\UserRepository;

use function App\Lib\Security\sanitize_text_field;

class AuthController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function process(Request $request): Response
    {
        $path = $request->getPath();
        $method = $request->getMethod();

        if ($path === '/register') {
            if ($method === 'POST') {
                return $this->registerProcess();
            }
            return $this->render('auth/register', ['title' => 'Inscription']);
        }

        if ($path === '/login') {
            if ($method === 'POST') {
                return $this->loginProcess();
            }
            return $this->render('auth/login', ['title' => 'Connexion']);
        }

        if ($path === '/logout') {
            return $this->logout();
        }

        return new Response("Page not found", 404);
    }

    private function registerProcess(): Response
    {
        $email = sanitize_text_field($_POST['email'] ?? '');
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        $password = sanitize_text_field($_POST['password'] ?? '');

        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($this->userRepository->findByEmail($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        if (empty($errors)) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_ARGON2ID));
            $user->setRole('user');
            $user->setCreatedAt(date('Y-m-d H:i:s'));

            $this->userRepository->save($user);

            return new Response('', 302, ['Location' => '/login']);
        }

        return $this->render('auth/register', ['errors' => $errors, 'title' => 'Inscription']);
    }

    private function loginProcess(): Response
    {
        $email = sanitize_text_field($_POST['email'] ?? '');
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        $password = sanitize_text_field($_POST['password'] ?? '');

        $user = $this->userRepository->findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            Session::set('user', [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ]);
            return new Response('', 302, ['Location' => '/']);
        }

        return $this->render('auth/login', ['error' => 'Identifiants incorrects.', 'title' => 'Connexion']);
    }

    private function logout(): Response
    {
        Session::destroy();
        return new Response('', 302, ['Location' => '/login']);
    }
}
