<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Repositories\PostRepository;

class HomeController extends AbstractController
{
    private PostRepository $postRepository;
	public function __construct()
	{
	    $this->postRepository = new PostRepository();
	}
	public function process(Request $request): Response
	{
	    $path = $request->getPath();
		if ($path === "/design-guide") {
			return $this->render('home/design-guide', ['title' => 'Design Guide'], 'home');
		}
		return $this->index($request);
	}

	public function index(Request $request): Response
	{
		$posts = $this->postRepository->findBy(['published' => true], ["user" => [
            "table" => "user",
            "condition" => "p.user",
            "fields" => ["id", "email"]
        ]]);
		return $this->render('home/index', ['title' => 'Accueil', 'posts' => $posts], 'home');
	}
}
