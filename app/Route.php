<?php

class Route
{
    private static $routes = [];

    public static function get($uri, $action)
    {
        self::$routes['GET'][$uri] = $action;
    }

    public static function post($uri, $action)
    {
        self::$routes['POST'][$uri] = $action;
    }

    public static function resolve($uri, $method)
    {   

        if (isset(self::$routes[$method][$uri])) {
            $action = self::$routes[$method][$uri];

            if (is_callable($action)) {
                return call_user_func($action);
            }

            if (is_array($action)) {
                [$controller, $method] = $action;
                return (new $controller())->$method();
            }
        }

        http_response_code(404);
        echo "Página não encontrada.";
    }
}