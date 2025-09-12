
<?php
require_once 'config.php';

// JWT verification middleware
require_once 'require_auth.php';

// GET all customers
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM customers");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
}

// GET customer orders
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = (SELECT email FROM customers WHERE id = ?)");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($orders);
}
?>
