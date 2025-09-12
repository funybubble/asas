<?php
// Authentication middleware - include at the top of protected pages
require_once 'config.php';

function requireAuth($required_role = null) {
    // Check for JWT token in Authorization header or cookie
    $token = null;
    
    // First try Authorization header
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (empty($headers) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }
    // Fallback to cookie
    elseif (isset($_COOKIE['refresh_token'])) {
        $token = $_COOKIE['refresh_token'];
    }
    
    if (!$token) {
        // If the client expects HTML (browser navigation), redirect to login page
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isBrowser = strpos($accept, 'text/html') !== false || (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '.php') !== false));
        if ($isBrowser) {
            header('Location: /admin_login.php');
            exit;
        }

        http_response_code(401);
        echo json_encode(['message' => 'Authentication required']);
        exit;
    }
    
    try {
        // Use fully-qualified class names to ensure the Firebase JWT classes are found
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(JWT_SECRET, 'HS256'));
        
        // Verify user still exists and is active (tolerant of missing is_active column)
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$decoded->user_id]);
        $user = $stmt->fetch();
        // If is_active column exists, enforce it; otherwise assume active
        $inactive = false;
        if ($user && array_key_exists('is_active', $user) && (int)$user['is_active'] !== 1) {
            $inactive = true;
        }
        if (!$user || $inactive) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $isBrowser = strpos($accept, 'text/html') !== false || (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '.php') !== false));
            if ($isBrowser) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<h2 style='color:red'>Admin user not found or inactive. Please check your database.</h2>";
                exit;
            }
            http_response_code(401);
            echo json_encode(['message' => 'User not found or inactive']);
            exit;
        }
        
        // Check role if specified
        if ($required_role && (!isset($user['role']) || $user['role'] !== $required_role)) {
            http_response_code(403);
            echo json_encode(['message' => 'Insufficient permissions']);
            exit;
        }
        
        // Set global user data
        global $current_user;
        $current_user = $user;
        
        return $decoded;
        
    } catch (Exception $e) {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isBrowser = strpos($accept, 'text/html') !== false || (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '.php') !== false));
        if ($isBrowser) {
            header('Location: /admin_login.php');
            exit;
        }

        http_response_code(401);
        echo json_encode(['message' => 'Invalid or expired token', 'error' => $e->getMessage()]);
        exit;
    }
}

// Call requireAuth() to protect the current page with admin role
requireAuth('admin');
?>