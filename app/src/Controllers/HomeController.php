<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class HomeController extends AbstractController
{
	public function process(Request $request): Response
	{
		return $this->index($request);
	}

	public function index(Request $request): Response
	{
		return $this->render('home/index', ['title' => 'Accueil'], 'home');
	}
}
