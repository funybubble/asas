<?php
require_once 'require_auth.php';
// Fetch orders and items
try {
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Naročila</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Vsa naročila</h1>

        <?php if (empty($orders)): ?>
            <p class="text-gray-600">Ni naročil za prikaz.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="bg-white shadow rounded mb-4 p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-semibold">Naročilo #<?php echo htmlspecialchars($order['id']); ?></h2>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?> — <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <p class="text-sm text-gray-600">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['created_at']); ?></p>
                            <p class="text-lg font-bold"><?php echo htmlspecialchars(number_format((float)$order['total_amount'], 2, ',', '.')) . '€'; ?></p>
                        </div>
                    </div>

                    <?php if (!empty($order['items'])): ?>
                        <div class="mt-3">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-gray-500 uppercase">
                                    <tr>
                                        <th>Izdelek</th>
                                        <th class="text-right">Količina</th>
                                        <th class="text-right">Enotna cena</th>
                                        <th class="text-right">Skupaj</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Izdelek'); ?></td>
                                            <td class="text-right"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td class="text-right"><?php echo htmlspecialchars(number_format((float)($item['unit_price'] ?? $item['price'] ?? 0), 2, ',', '.')) . '€'; ?></td>
                                            <td class="text-right"><?php echo htmlspecialchars(number_format((float)($item['unit_price'] * $item['quantity']), 2, ',', '.')) . '€'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
