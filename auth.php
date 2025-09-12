<?php
require_once 'config.php';

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Validate admin credentials
    if($data->username === 'admin' && $data->password === 'admin123') {
        $token = array(
            "iss" => "cebelarstvo_cigoj",
            "iat" => time(),
            "exp" => time() + (60 * 60),
            "data" => array(
                "username" => "admin",
                "role" => "admin"
            )
        );
        
        $jwt = \Firebase\JWT\JWT::encode($token, JWT_SECRET, 'HS256');
        echo json_encode(array("success" => true, "token" => $jwt));
    } else {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Invalid credentials"));
    }
}
?>