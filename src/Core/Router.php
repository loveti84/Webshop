<?php

namespace Core;




class Router
{
    private $routes = [];
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }



    public function get($path, $handler)
    {
        
        $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public function post($path, $handler)
    {
        $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    public function put($path, $handler)
    {
        $this->routes[] = ['method' => 'PUT', 'path' => $path, 'handler' => $handler];
    }

    public function delete($path, $handler)
    {
        $this->routes[] = ['method' => 'DELETE', 'path' => $path, 'handler' => $handler];
    }

    //run called controller function
    public function dispatch($requestUri, $requestMethod)
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = str_replace($_ENV['REL_BASE_URL'], '', $uri);
        $uri = '/' . trim($uri, '/');

        // Match route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) continue;
            //exact path matching
            if ($route['path'] === $uri) {
                error_log('Dispatch Route founded! ' . $route['method'] . 'path: ' . $route['path'].' Handler ' . $route['handler']);
               $this->call($route['handler']);
                return;
            }
        }

        // 404
        ErrorHandler::show404Page();
    }

    private function call($handler)
    {
        list($controllerClass, $method) = explode('@', $handler);
        
        // Create controller with dependencies 
        $controller = $this->createController($controllerClass);
       
        error_log('Calling controller: ' . $controllerClass . '->' . $method);
        $controller->$method();
    }



    private function createController($controllerClass)
    {
        // Lazy loading: create dependencies only when needed
        switch ($controllerClass) {
            case 'Controllers\\ProductController':
                $productRepo = new \Repositories\ProductRepository($this->db);
                $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
                return new $controllerClass($productRepo, $reviewRepo);
                
            case 'Controllers\\ReviewController':
                $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
                $productRepo = new \Repositories\ProductRepository($this->db);
                $userRepo = new \Repositories\UserRepository($this->db);
                return new $controllerClass($reviewRepo, $productRepo, $userRepo);
                
            case 'Controllers\\UserController':
                $userRepo = new \Repositories\UserRepository($this->db);
                $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
                return new $controllerClass($userRepo, $reviewRepo);
                
            default:
                // ViewController and others have no dependencies
                return new $controllerClass();
        }
    }
}
