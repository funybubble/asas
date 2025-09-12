<?php
// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Use Firebase JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Show errors for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration - using SQLite for development in Replit
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', 'cebelarstvo_cigoj.db');

// Establish database connection (SQLite for now)
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/" . DB_NAME);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign key support for SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch(PDOException $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage());
}

// Set headers for API (only set when needed to avoid conflicts with HTML pages)
if (isset($_SERVER['REQUEST_URI']) && 
    strpos($_SERVER['REQUEST_URI'], '.php') !== false && 
    strpos($_SERVER['REQUEST_URI'], '.html') === false &&
    !strpos($_SERVER['REQUEST_URI'], 'backend.html') &&
    !strpos($_SERVER['REQUEST_URI'], 'backend.php') &&
    !strpos($_SERVER['REQUEST_URI'], 'admin_login.php') &&
    !strpos($_SERVER['REQUEST_URI'], 'checkout.php') &&
    !strpos($_SERVER['REQUEST_URI'], 'thank_you.php')) {
    header("Content-Type: application/json; charset=UTF-8");
    // More secure CORS - restrict to localhost for development
    $allowed_origins = ['http://localhost:5000', 'https://localhost:5000'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . $origin);
    }
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

// JWT Secret key - use environment variable for security
$jwt_secret = 'your_hardcoded_secret_here';
define('JWT_SECRET', $jwt_secret);

// JWT verification middleware function - fixed for PHP built-in server
function verifyToken() {
    // Use getallheaders() with fallback for PHP built-in server
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    
    // Fallback for PHP built-in server
    if (empty($headers) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    if(!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(array("message" => "Access Denied. No token provided."));
        exit;
    }

    try {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return $decoded;
    } catch(Exception $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Invalid token", "error" => $e->getMessage()));
        exit;
    }
}
?>