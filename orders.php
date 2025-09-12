<?php
require_once 'config.php';

// GET all orders (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'require_auth.php'; // This now includes admin role checking
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $is_html = strpos($accept, 'text/html') !== false || isset($_GET['id']);
    $order_id = $_GET['id'] ?? null;
    if ($is_html && $order_id) {
        // Render HTML order details page
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            echo "<h2 style='color:red'>Order not found.</h2>";
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<!DOCTYPE html><html lang='sl'><head><meta charset='utf-8'><title>Podrobnosti naročila #{$order_id}</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-amber-50'>";
        echo "<div class='max-w-2xl mx-auto my-8 bg-white rounded-lg shadow p-6'>";
        echo "<h1 class='text-2xl font-bold mb-4 text-amber-900'>Naročilo #{$order_id}</h1>";
        echo "<div class='mb-4'><strong>Stranka:</strong> {$order['customer_name']}<br><strong>Email:</strong> {$order['customer_email']}<br><strong>Naslov:</strong> {$order['customer_address']}<br><strong>Telefon:</strong> {$order['customer_phone']}</div>";
        echo "<div class='mb-4'><strong>Status:</strong> {$order['status']}<br><strong>Skupaj:</strong> {$order['total_amount']} €<br><strong>Datum:</strong> {$order['created_at']}</div>";
        echo "<h2 class='text-xl font-semibold mb-2 text-amber-800'>Izdelki</h2>";
        echo "<table class='w-full border'><thead><tr class='bg-amber-100'><th class='p-2 text-left'>Izdelek</th><th class='p-2 text-right'>Količina</th><th class='p-2 text-right'>Cena</th></tr></thead><tbody>";
        foreach ($items as $item) {
            echo "<tr><td class='p-2'>{$item['product_name']}</td><td class='p-2 text-right'>{$item['quantity']}</td><td class='p-2 text-right'>{$item['total_price']} €</td></tr>";
        }
        echo "</tbody></table>";
        echo "<div class='mt-6'><a href='backend.php' class='bg-amber-700 text-white px-4 py-2 rounded'>Nazaj na nadzorno ploščo</a></div>";
        echo "</div></body></html>";
        exit;
    }
    // Default: return all orders as JSON (API)
    $stmt = $pdo->query("SELECT * FROM orders");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($orders as &$order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    header('Content-Type: application/json; charset=utf-8');
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

// UPDATE order status (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_once 'require_auth.php'; // This now includes admin role checking
    
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