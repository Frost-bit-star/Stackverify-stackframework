<?php

/**
 * Stack Framework init.php
 * Loads environment, autoloads classes, initializes DB connection, and loads global env constants.
 */

date_default_timezone_set('UTC');
session_start();

// Autoload Core Files (logic, db, connection)
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../logic/' . $class . '.php',
        __DIR__ . '/../db/' . $class . '.php',
        __DIR__ . '/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Load .env config at root (adjusted to go two levels up)
$configPath = __DIR__ . '/../../.env';
if (!file_exists($configPath)) {
    die("⚠️ .env file not found at $configPath");
}

$config = parse_ini_file($configPath, false, INI_SCANNER_RAW);
if (!$config) {
    die("⚠️ Failed to parse .env config file. Ensure no quotes around values.");
}

// Define environment constants safely
define('EMAIL_API_URL', $config['EMAIL_API_URL'] ?? '');
define('EMAIL_API_KEY', $config['EMAIL_API_KEY'] ?? '');

// Validate required environment variables
if (!EMAIL_API_URL || !EMAIL_API_KEY) {
    die("⚠️ Missing EMAIL_API_URL or EMAIL_API_KEY in .env");
}

// SQLite Connection
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/app.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB Connection Failed: ' . $e->getMessage()]));
}

// JSON Response Helper (for APIs)
function respond($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Debug loaded environment if needed
if (isset($_GET['debug_env'])) {
    header('Content-Type: text/plain');
    print_r($config);
    exit;
}