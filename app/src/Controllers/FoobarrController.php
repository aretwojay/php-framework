<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class FoobarrController extends AbstractController {
    public function process(Request $request): Response {
        var_dump($request->getSlugs());die;
        return new Response('hello world', 200, []);
    }
}
