<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables BEFORE config files that use them
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/lib/helper.php';
require_once __DIR__ . '/lib/html.php';
require_once __DIR__ . '/lib/validation.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/upload.php';
require_once __DIR__ . '/lib/rewards.php';
require_once __DIR__ . '/lib/qr_code.php';

require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', '/', $relative);
        $segments = explode('/', $relativePath);
        $segments[0] = strtolower($segments[0]);
        $file = __DIR__ . '/' . implode('/', $segments) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

$GLOBALS['_db'] = db();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
