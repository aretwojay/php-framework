<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Repositories\PostRepository;

class AdminPostController extends AbstractController
{
    public function process(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->index($request);
        }

        throw new \Exception("Method not allowed", 405);
    }

    public function index(Request $request): Response
    {
        $repo = new PostRepository();
        $posts = $repo->findAll(); 

        return $this->render('admin/posts/index', [
            'posts' => $posts
        ], 'layouts/admin');
    }
}
