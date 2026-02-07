<?php

namespace App\Core;

use App\Lib\Http\AbstractMiddleware;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class AdminMiddleware extends AbstractMiddleware
{
    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');

        // Not logged in -> redirect to login
        if (!$user) {
            return new Response('', 302, ['Location' => '/login']);
        }

        // Logged in but not admin -> redirect to home (or 403)
        if (($user['role'] ?? '') !== 'admin') {
            return new Response('Access Denied', 403);
        }

        return null;
    }
}
