<?php

namespace App\Lib\Http;

use App\Lib\Http\Request;
use App\Lib\Http\Response;

abstract class AbstractMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @return Response|null Return a Response to stop execution (e.g. redirect), or null to continue.
	 */
	public function handle(Request $request): ?Response {
		return null;
	}
}
