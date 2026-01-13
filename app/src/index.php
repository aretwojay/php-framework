<?php

use App\Lib\Http\Request;
use App\Lib\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

try { // exemple d'utilisation ( add et run )
    
    $request = new Request();
    $router = new Router();

    $router->add('GET', '/hello/:name', [\App\Controllers\TestController::class, 'hey']);

    $response = $router->run($request);

    header($response->getHeadersAsString());
    http_response_code($response->getStatus());
    echo $response->getContent();
    exit();
} catch (\Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo $e->getMessage();
}


$request = new Request();

