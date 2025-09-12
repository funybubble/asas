```php
<?php
require_once 'config.php';

// Get cart from session or cookie
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : (isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : []);

// Calculate total
$total = 0;
foreach($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone']
    ];
    
    // Prepare order data
    $order_data = [
        'customer' => $customer,
        'items' => $cart,
        'total' => $total + 5 // Including shipping
    ];
    
    // Create order (this would be a call to your orders.php API in a real app)
    $order_id = createOrder($order_data['customer'], $order_data['total'], $cart);
    
    // Clear cart
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, '/');
    
    // Redirect to thank you page
    header('Location: thank_you.php?order_id=' . $order_id);
    exit;
}

// Function to create order (simplified for example)
function createOrder($customer, $total, $items) {
    // In a real app, this would call your orders.php API endpoint
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    
    // Insert order
    $stmt = $db->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_amount) VALUES (?, ?, ?, ?)");
    $stmt->execute([$customer['name'], $customer['email'], $customer['address'], $total]);
    $order_id = $db->lastInsertId();
    
    // Insert order items
    foreach($items as $item) {
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['name'], $item['quantity'], $item['price']]);
        
        // Update product stock
        $stmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    
    return $order_id;
}
?>
```