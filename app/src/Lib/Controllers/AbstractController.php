<?php

namespace App\Lib\Controllers;

use App\Lib\Http\Request;
use App\Lib\Http\Response;

abstract class AbstractController {
    public abstract function process(Request $request): Response;

    protected function render(string $template, array $data = [], ?string $layout = null): Response
    {
        $response = new Response();
        extract($data);
        
        ob_start();
        require_once __DIR__ . "/../../../views/{$template}.html";
        $content = ob_get_clean();

        if ($layout) {
            ob_start();
            require_once __DIR__ . "/../../../views/layouts/{$layout}.html";
            $content = ob_get_clean();
        }

        $response->setContent($content);
        $response->addHeader('Content-Type', 'text/html');

        return $response;
    }

    protected function render404(): Response
    {
        return $this->render('errors/404', ['title' => 'Page introuvable'], 'home')->setStatus(404);
    }
}
