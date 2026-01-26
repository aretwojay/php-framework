<?php

namespace App\Core;

use App\Lib\Http\AbstractMiddleware;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class AuthMiddleware extends AbstractMiddleware
{
    public function handle(Request $request): ?Response
    {
        // Check if user is in session
        $user = Session::get('user');

        if (!$user) {
            // User not logged in, redirect to login
            return new Response('', 302, ['Location' => '/login']);
        }

        // Allow request to proceed
        return null;
    }
}
