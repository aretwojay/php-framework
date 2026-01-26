<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class DashboardController extends AbstractController
{
    public function process(Request $request): Response
    {
        return $this->index($request);
    }

    public function index(Request $request): Response
    {
        $user = ['name' => 'Admin']; // Simule l'utilisateur connecté

        // Note: 'layouts/admin' refers to layouts/admin.html (extension handled by AbstractController)
        return $this->render(
            'admin/dashboard',               
            ['title' => 'Dashboard Admin', 'user' => $user],
            'admin'                  
        );
    }
}