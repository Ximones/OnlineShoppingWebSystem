<?php

class Router {
    protected array $routes = [
        'shop' => 'ShopController',
        'auth' => 'AuthController',
        'profile' => 'ProfileController',
        'admin.members' => 'AdminMemberController',
        'admin.products' => 'AdminProductController',
        'cart' => 'CartController',
        'orders' => 'OrderController',
    ];

    public function dispatch(): void {
        $module = req('module', 'shop');
        $action = req('action', 'home');

        $controllerKey = $module;
        if ($module === 'admin' && req('resource')) {
            $controllerKey .= '.' . req('resource');
        }

        $controllerName = $this->routes[$controllerKey] ?? $this->routes['shop'];
        $controllerClass = "App\\Controllers\\$controllerName";

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller $controllerClass not found.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Action $action not found on $controllerClass.");
        }

        $controller->$action();
    }
}


