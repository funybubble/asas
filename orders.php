<?php
require_once 'config.php';

// GET all orders
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    verifyToken();
    
    $stmt = $pdo->query("SELECT * FROM orders");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Include order items for each order
    foreach($orders as &$order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($orders);
}

// POST create new order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data->customer->name,
            $data->customer->email,
            $data->customer->address,
            $data->total,
            'pending'
        ]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        foreach($data->items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $item->product_id,
                $item->product_name,
                $item->quantity,
                $item->unit_price
            ]);
            
            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item->quantity, $item->product_id]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        http_response_code(201);
        echo json_encode(array("success" => true, "order_id" => $order_id));
    } catch(Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => $e->getMessage()));
    }
}

// UPDATE order status
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    verifyToken();
    
    $data = json_decode(file_get_contents("php://input"));
    $id = $_GET['id'] ?? null;
    
    if($id) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data->status, $id]);
        
        echo json_encode(array("success" => true, "message" => "Order updated"));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Order ID required"));
    }
}
?>