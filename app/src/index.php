<?php

use App\Lib\Http\Request;
use App\Lib\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    
    $request = new Request();
    $response = Router::route($request);

    header($response->getHeadersAsString());
    http_response_code($response->getStatus());
    echo $response->getContent();
    exit();
} catch(\Exception $e) {
    if ($e->getCode() === 404) {
        http_response_code(404);
        $title = 'Page introuvable';
        ob_start();
        require_once __DIR__ . '/../views/errors/404.html';
        $content = ob_get_clean();
        ob_start();
        require_once __DIR__ . '/../views/layouts/home.html';
        echo ob_get_clean();
    } else {
        echo $e->getMessage();
    }
}
