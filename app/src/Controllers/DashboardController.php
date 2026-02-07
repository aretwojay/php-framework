<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Core\Session;
use App\Lib\Cache\TransientManager;

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

    public function clearCache(Request $request): Response
    {
        TransientManager::init();
        TransientManager::clear();

        $response = new Response();
        $response->setStatus(302);
        $response->addHeader('Location', '/admin');
        
        return $response;
    }
}