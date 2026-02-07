<?php

namespace App\Core;

use App\Lib\Http\AbstractMiddleware;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class GuestMiddleware extends AbstractMiddleware
{
	public function handle(Request $request): ?Response
	{
		$user = Session::get('user');
		
		// If logged in, redirect to home
		if ($user) {
			return new Response('', 302, ['Location' => '/']);
		}
		
		return null;
	}
}
