<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'futsal_rental');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL - Update this according to your server configuration
define('BASE_URL', '');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/assets/uploads');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOADS_PATH . '/payments')) {
    mkdir(UPLOADS_PATH . '/payments', 0777, true);
}
