<?php

use App\Lib\Http\Request;
use App\Lib\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

try { // exemple d'utilisation ( add et run )
    
    $request = new Request();
    $router = new Router();

    
    $router->add('GET', '/artist', ['GetArtistsController', 'process']);
    $router->add('GET', '/artist/:id', ['GetArtistController', 'process']);
    $router->add('POST', '/artist', ['PostArtistController', 'process']);

    $response = $router->run($request);

    header($response->getHeadersAsString());
    http_response_code($response->getStatus());
    echo $response->getContent();
    exit();
} catch(\Exception $e) {
    echo $e->getMessage();
}

$request = new Request();

