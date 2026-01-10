<?php

define('APP_NAME', 'Online Shopping System');
define('APP_TIMEZONE', 'Asia/Kuala_Lumpur');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000'); 
define('APP_BASE_URL', '/');
define('APP_UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('APP_UPLOAD_URL', '/public/uploads/');

$GLOBALS['_roles'] = [
    'superadmin' => 'Super Admin',
    'admin' => 'Admin',
    'member' => 'Member',
];

$GLOBALS['_order_statuses'] = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];

date_default_timezone_set(APP_TIMEZONE);


