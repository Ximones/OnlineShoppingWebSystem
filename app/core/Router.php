<?php

class Router {
    protected array $routes = [
        'shop' => 'ShopController',
        'auth' => 'AuthController',
        'profile' => 'ProfileController',
        'game' => 'GameController',
        'admin' => 'AdminDashboardController',
        'admin.members' => 'AdminMemberController',
        'admin.admins' => 'AdminAdminController',
        'admin.products' => 'AdminProductController',
        'admin.categories' => 'AdminCategoryController',
        'admin.vouchers' => 'AdminVoucherController',
        'admin.orders' => 'AdminOrderController',
        'admin.paylater' => 'AdminPayLaterController',
        'cart' => 'CartController',
        'orders' => 'OrderController',
        'vouchers' => 'VoucherController',
        'bills' => 'BillController',
        'favorites' => 'FavoriteController',
    ];

    public function dispatch(): void {
        $module = req('module', 'shop');
        $action = req('action', 'home');

        $controllerKey = $module;
        if ($module === 'admin') {
            $resource = req('resource');
            if ($resource) {
                $controllerKey .= '.' . $resource;
            }
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


