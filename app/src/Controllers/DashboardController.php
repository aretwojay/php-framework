<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Core\Session;

class DashboardController extends AbstractController
{
    public function process(Request $request): Response
    {
        return $this->index($request);
    }

    public function index(Request $request): Response
    {
        $user = Session::get("user");

        return $this->render(
            'admin/dashboard',               
            ['title' => 'Dashboard Admin', 'user' => $user],
            'admin'                  
        );
    }
}