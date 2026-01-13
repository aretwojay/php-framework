<?php

namespace App\Lib\Http;

use App\Lib\Controllers\AbstractController;


class Router {
    private const ERROR_NO_ROUTE = 'No matching route found';
    
    const string CONTROLLER_NAMESPACE_PREFIX = "App\\Controllers\\";
    private array $routes = [];
    
    
    public function add(string $method, string $path, array $controller): void{
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller
        ];
    }

    public function run(Request $request): Response
    {
        foreach ($this->routes as $route) {

            if (!self::checkMethod($request, $route)) {
                continue;
            }

            if (!self::checkUri($request, $route)) {
                continue;
            }

            return self::dispatch($route['controller'], $request);
        }

        throw new \Exception(self::ERROR_NO_ROUTE, 404);
    }

    private static function checkMethod(Request $request, array $route): bool {
        return $request->getMethod() === $route['method'];
    }

    private static function checkUri(Request $request, array $route): bool {
        $requestUriParts = self::getUrlParts($request->getPath());
        $routePathParts = self::getUrlParts($route['path']);

        if(self::checkUrlPartsNumberMatches($requestUriParts, $routePathParts) === false) {
            return false;
        }

        foreach($routePathParts as $key => $part) {
            if(self::isUrlPartSlug($part) === false) {
                if($part !== $requestUriParts[$key]) {
                    return false;
                }
            }else{
                $request->addSlug(substr($part, 1),  trim(urldecode($requestUriParts[$key])));
            }
        }

        return true;
    }
    
    private static function getControllerInstance(string $controllerClass,Request $request): AbstractController {
        
        if (!class_exists($controllerClass)) {
            throw new \Exception('Controller not found', 404);
        }

        $controller = new $controllerClass($request);

        if (!is_subclass_of($controller, AbstractController::class)) {
            throw new \Exception('Invalid controller', 500);
        }

        return $controller;
    }


    private static function getUrlParts(string $url): array {
        return explode('/', trim($url, '/'));
    }

    private static function checkUrlPartsNumberMatches(array $requestUriParts, array $routePathParts): bool {
        return count($requestUriParts) === count($routePathParts);
    }

    private static function isUrlPartSlug(string $part): bool {
        return strpos($part, ':') === 0;
    }

    private static function dispatch(array $controllerDefinition, Request $request): Response
    {
        [$controllerClass, $action] = $controllerDefinition;

        if (!class_exists($controllerClass)) {
            throw new \Exception(
                sprintf('Controller "%s" not found', $controllerClass),
                404
            );
        }

        $controller = new $controllerClass($request);

        if (!is_subclass_of($controller, AbstractController::class)) {
            throw new \Exception(
                sprintf('Controller "%s" is not a valid controller', $controllerClass),
                500
            );
        }

        if (!method_exists($controller, $action)) {
            throw new \Exception(
                sprintf(
                    'Action "%s" not found on controller "%s"',
                    $action,
                    $controllerClass
                ),
                404
            );
        }

        return $controller->$action();
    }
}