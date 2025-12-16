<?php
//Entry Point

// Start session 
session_start();
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log(print_r($_SESSION, true));


// Path definitions
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/src');
require_once APP_PATH . '/config/config.php';
// Autoloader (loads the files from app path (everything in src))
spl_autoload_register(function ($class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});



// Database connection
$db = Core\Database::connect();

// Initialize router with database (for lazy loading)
$router = new Core\Router($db);

// Load routes
require_once APP_PATH . '/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
