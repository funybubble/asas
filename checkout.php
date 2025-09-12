```php
<?php
require_once 'config.php';
session_start();

// Check if cart exists
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : (isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : []);

// Initialize total
$total = 0;
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blagajna | Čebelarstvo Cigoj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="font-sans bg-gray-50">
    <header class="bg-amber-800 text-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <h1 class="text-xl font-bold">Čebelarstvo Cigoj - Blagajna</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2">
                    <h2 class="text-2xl font-bold text-amber-900 mb-6">Vaša naročila</h2>
                    
                    <?php if(empty($cart)): ?>
                        <div class="bg-white rounded-lg shadow p-6 text-center">
                            <p class="text-gray-600 mb-4">Vaša košarica je prazna</p>
                            <a href="/" class="text-amber-600 hover:text-amber-800">Nazaj v trgovino</a>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izdelek</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cena</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Količina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skupaj</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($cart as $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($item['price'], 2); ?>€</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['quantity']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($item['price'] * $item['quantity'], 2); ?>€</td>
                                        </tr>
                                        <?php $total += $item['price'] * $item['quantity']; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                        <h2 class="text-xl font-bold text3-amber-900 mb-4">Povzetek naročila</h2>
                        
                        <div class="border-b border-gray-200 mb-4">
                            <div class="flex justify-between py-2">
                                <span>Skupaj za izdelke:</span>
                                <span><?php echo number_format($total, 2); ?>€</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span>Dostava:</span>
                                <span>5.00€</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold py-2 mb-4">
                            <span>Skupaj:</span>
                            <span><?php echo number_format($total + 5, 2); ?>€</span>
                        </div>
                        
                        <a href="process_order.php" class="block w-full bg-amber-600 hover:bg-amber-700 text-white text-center py-3 px-4 rounded-lg font-medium transition">
                            Zaključi naročilo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        feather.replace();
    </script>
</body>
</html>
```