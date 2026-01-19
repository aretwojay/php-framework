<?php

namespace App\Lib\Controllers;

use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Core\ViewRenderer;

abstract class AbstractController {
    public abstract function process(Request $request): Response;

    protected function render(string $template, array $data = []): Response
    {
        $response = new Response();
        extract($data);
        ob_start();

        $viewRenderer = new ViewRenderer();
        $content = $viewRenderer->render($template, $data);
        $response->setContent($content);

        return $response;
    }
}
