<?php
require_once 'config.php';

// GET all products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
}

// POST new product (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyToken();
    
    $data = json_decode(file_get_contents("php://input"));
    
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data->name, $data->description, $data->price, $data->stock, $data->image_url]);
    
    $product_id = $pdo->lastInsertId();
    $stmt = $pdo->query("SELECT * FROM products WHERE id = $product_id");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($product);
}

// UPDATE product (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    verifyToken();
    
    $data = json_decode(file_get_contents("php://input"));
    $id = $_GET['id'] ?? null;
    
    if($id) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$data->name, $data->description, $data->price, $data->stock, $data->image_url, $id]);
        
        echo json_encode(array("success" => true, "message" => "Product updated"));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Product ID required"));
    }
}

// DELETE product (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    verifyToken();
    
    $id = $_GET['id'] ?? null;
    
    if($id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(array("success" => true, "message" => "Product deleted"));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Product ID required"));
    }
}
?>