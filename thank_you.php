```php
<?php
require_once 'config.php';

$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hvala za naročilo | Čebelarstvo Cigoj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="font-sans bg-gray-50">
    <header class="bg-amber-800 text-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <h1 class="text-xl font-bold">Čebelarstvo Cigoj</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-lg mx-auto text-center bg-white rounded-lg shadow p-8">
            <div class="text-green-500 mb-4">
                <i data-feather="check-circle" class="w-16 h-16 mx-auto"></i>
            </div>
            <h2 class="text-2xl font-bold text-amber-900 mb-4">Hvala za vaše naročilo!</h2>
            <p class="text-gray-600 mb-6">Vaše naročilo številka <span class="font-bold">#<?php echo $order_id; ?></span> je bilo uspešno oddano.</p>
            <p class="text-gray-600 mb-6">Potrdilo naročila smo vam poslali na vaš e-mail naslov.</p>
            <a href="/" class="inline-block bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition">
                Nazaj v trgovino
            </a>
        </div>
    </main>

    <script>
        feather.replace();
    </script>
</body>
</html>
```

These changes add:
1. Cart functionality using localStorage
2. Checkout page with order summary
3. Order processing page
4. Thank you page after successful order
5. Complete order flow from cart to confirmation

To fully implement this, make sure your orders.php API endpoint is properly set up to handle the order creation. You might also want to add:
- Payment gateway integration (like Stripe or PayPal)
- Email notifications
- Order status tracking
- Better validation in the checkout form