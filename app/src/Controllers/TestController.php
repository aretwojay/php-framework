<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Response;

class TestController extends AbstractController
{
    public function hello(): Response
    {
        $name = $this->request->getSlug('name');

        $response = new Response();
        $response->setContent("Hello, $name ");
        $response->addHeader('Content-Type', 'text/plain');

        return $response;
    }
}
