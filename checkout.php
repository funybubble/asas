<?php
require_once 'config.php';

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['customer_name', 'customer_email', 'customer_address', 'customer_phone', 'cart_items'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field $field is required"]);
            exit;
        }
    }
    
    $customer_name = trim($input['customer_name']);
    $customer_email = filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL);
    $customer_address = trim($input['customer_address']);
    $customer_phone = trim($input['customer_phone']);
    $cart_items = $input['cart_items'];
    $shipping_method = $input['shipping_method'] ?? 'posta_slovenije';
    
    if (!$customer_email) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Add shipping cost (Pošta Slovenije - 4.90 euro)
    $shipping_cost = 0;
    if ($shipping_method === 'posta_slovenije') {
        $shipping_cost = 4.90;
    }
    
    $total = $subtotal + $shipping_cost;
    
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, customer_phone, subtotal, shipping_cost, total_amount, shipping_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)");
        $stmt->execute([
            $customer_name,
            $customer_email,
            $customer_address,
            $customer_phone,
            $subtotal,
            $shipping_cost,
            $total,
            $shipping_method
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping_cost,
            'total' => $total,
            'message' => 'Naročilo je bilo uspešno oddano!'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Napaka pri oddaji naročila: ' . $e->getMessage()]);
    }
    
    exit;
}

// Show checkout form
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blagajna | Čebelarstvo Cigoj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        .form-input:focus {
            outline: none;
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-amber-800 text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <i data-feather="hexagon" class="mr-2"></i>
                <h1 class="text-xl font-bold">Čebelarstvo Cigoj</h1>
            </div>
            <a href="index.html" class="flex items-center text-amber-200 hover:text-white">
                <i data-feather="arrow-left" class="mr-1"></i>
                Nazaj v trgovino
            </a>
        </div>
    </nav>

    <!-- Checkout Form -->
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-amber-50 px-6 py-4 border-b">
                <h2 class="text-2xl font-bold text-amber-900">Blagajna</h2>
                <p class="text-amber-700 mt-1">Vnesite podatke za oddajo naročila</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 p-6">
                <!-- Customer Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Podatki za dostavo</h3>
                    <form id="checkout-form" class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Ime in priimek *</label>
                            <input type="text" id="customer_name" name="customer_name" required
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">E-mail naslov *</label>
                            <input type="email" id="customer_email" name="customer_email" required
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Naslov za dostavo *</label>
                            <textarea id="customer_address" name="customer_address" rows="3" required
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>

                        <!-- Shipping Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Način dostave</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border border-amber-200 rounded-lg bg-amber-50">
                                    <input type="radio" name="shipping_method" value="posta_slovenije" checked
                                        class="text-amber-600 focus:ring-amber-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-900">Pošta Slovenije</span>
                                            <span class="font-bold text-amber-700">4,90 €</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Standardna dostava v 2-3 delovnih dneh</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Povzetek naročila</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div id="order-items" class="space-y-3 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>
                        
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Izdelki:</span>
                                <span id="subtotal">0,00 €</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Dostava (Pošta Slovenije):</span>
                                <span>4,90 €</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Skupaj:</span>
                                <span id="total">4,90 €</span>
                            </div>
                        </div>
                        
                        <button type="submit" form="checkout-form" 
                            class="w-full mt-6 bg-amber-600 hover:bg-amber-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                            <i data-feather="credit-card" class="inline mr-2"></i>
                            Oddaj naročilo
                        </button>
                        
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            * Plačilo ob prevzemu (gotovina ali kartica)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i data-feather="check" class="text-green-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Naročilo uspešno oddano!</h3>
                <p class="text-gray-500 mb-6">Vaše naročilo št. <span id="order-number"></span> je bilo prejeto. Kontaktirali vas bomo v kratkem.</p>
                <button onclick="window.location.href='index.html'" 
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-lg">
                    Nazaj v trgovino
                </button>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
        
        // Load cart items from localStorage
        function loadCartItems() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const orderItemsDiv = document.getElementById('order-items');
            const subtotalSpan = document.getElementById('subtotal');
            const totalSpan = document.getElementById('total');
            
            if (cart.length === 0) {
                orderItemsDiv.innerHTML = '<p class="text-gray-500 text-center">Košarica je prazna</p>';
                return;
            }
            
            let subtotal = 0;
            orderItemsDiv.innerHTML = '';
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex justify-between items-center';
                itemDiv.innerHTML = `
                    <div>
                        <span class="font-medium">${item.name}</span>
                        <span class="text-gray-500 text-sm">× ${item.quantity}</span>
                    </div>
                    <span>${itemTotal.toFixed(2)} €</span>
                `;
                orderItemsDiv.appendChild(itemDiv);
            });
            
            const shipping = 4.90;
            const total = subtotal + shipping;
            
            subtotalSpan.textContent = subtotal.toFixed(2) + ' €';
            totalSpan.textContent = total.toFixed(2) + ' €';
        }
        
        // Handle form submission
        document.getElementById('checkout-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            if (cart.length === 0) {
                alert('Košarica je prazna!');
                return;
            }
            
            const data = {
                customer_name: formData.get('customer_name'),
                customer_email: formData.get('customer_email'),
                customer_phone: formData.get('customer_phone'),
                customer_address: formData.get('customer_address'),
                shipping_method: formData.get('shipping_method'),
                cart_items: cart
            };
            
            try {
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Clear cart
                    localStorage.removeItem('cart');
                    
                    // Show success modal
                    document.getElementById('order-number').textContent = result.order_id;
                    document.getElementById('success-modal').classList.remove('hidden');
                    document.getElementById('success-modal').classList.add('flex');
                } else {
                    alert(result.error || 'Napaka pri oddaji naročila');
                }
            } catch (error) {
                alert('Napaka pri povezavi s strežnikom');
                console.error('Error:', error);
            }
        });
        
        // Load cart items on page load
        loadCartItems();
    </script>
</body>
</html>