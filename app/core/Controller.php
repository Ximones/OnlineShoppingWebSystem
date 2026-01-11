<?php

namespace App\Core;

use function flash;
use function require_auth;
use function require_role;

abstract class Controller {
    protected string $layout = 'layout/master';

    public function is_active(string $moduleName): string {
        return (isset($_GET['module']) && $_GET['module'] === $moduleName) ? 'active' : '';
    }
    
    protected function render(string $view, array $data = []): void {
        extract($data);
        $flashSuccess = flash('success');
        $flashDanger = flash('danger');

        ob_start();
        require __DIR__ . "/../views/{$view}.php";
        $content = ob_get_clean();

        require __DIR__ . "/../views/{$this->layout}.php";
    }

    protected function requireAuth(): void {
        require_auth();
    }

    protected function requireAdmin(): void {
        require_role(['admin', 'superadmin']);
    }
}


