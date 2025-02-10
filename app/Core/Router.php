<?php

namespace App\Core;

class Router {
    private $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller): void{
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes[$method] as $route => $controller) {
            $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);

            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                array_shift($matches);
                [$controllerClass, $action] = $controller;
                $controllerInstance = new $controllerClass();
                call_user_func_array([$controllerInstance, $action], $matches);
                return;
            }
        }
        http_response_code(404);
        echo "404 Not Found";
    }
}