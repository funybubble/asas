<?php
require_once 'require_auth.php';

// Fetch basic stats and recent orders for the dashboard
try {
    // Orders count and revenue
    $stmt = $pdo->query("SELECT COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as revenue FROM orders");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $orders_count = $stats['orders_count'] ?? 0;
    $revenue = $stats['revenue'] ?? 0.00;

    // Products count
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $products_count = (int)$stmt->fetchColumn();

    // Customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
    $customers_count = (int)$stmt->fetchColumn();

    // Recent orders (latest 10)
    $stmt = $pdo->prepare("SELECT id, customer_name, status, total_amount, created_at FROM orders ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Graceful fallback to zeros/empty if the DB query fails
    $orders_count = $orders_count ?? 0;
    $revenue = $revenue ?? 0.00;
    $products_count = $products_count ?? 0;
    $customers_count = $customers_count ?? 0;
    $recentOrders = [];
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracija | Čebelarstvo Cigoj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background-color: rgba(251, 191, 36, 0.1);
        }
        .dashboard-card {
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Admin Layout -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar bg-amber-800 text-white w-64 flex-shrink-0">
            <div class="p-4 border-b border-amber-700">
                <h1 class="text-xl font-bold flex items-center">
                    <i data-feather="hexagon" class="mr-2"></i>
                    Čebelarstvo Cigoj
                </h1>
                <p class="text-xs text-amber-200 mt-1">Administracija</p>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="sidebar-link flex items-center px-3 py-2 rounded-lg bg-amber-700 text-white">
                            <i data-feather="home" class="mr-3"></i>
                            Nadzorna plošča
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-amber-200 hover:text-white">
                            <i data-feather="shopping-bag" class="mr-3"></i>
                            Izdelki
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-amber-200 hover:text-white">
                            <i data-feather="file-text" class="mr-3"></i>
                            Naročila
                        </a>
                    </li>
                    <li>
                        <a href="customers.php" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-amber-200 hover:text-white">
                            <i data-feather="users" class="mr-3"></i>
                            Stranke
                        </a>
                    </li>
                    <li>
                        <a href="reports.php" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-amber-200 hover:text-white">
                            <i data-feather="bar-chart-2" class="mr-3"></i>
                            Poročila
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-amber-200 hover:text-white">
                            <i data-feather="settings" class="mr-3"></i>
                            Nastavitve
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center p-4">
                    <div class="flex items-center">
                        <button class="p-2 rounded-full hover:bg-gray-100 mr-2">
                            <i data-feather="menu"></i>
                        </button>
                        <h2 class="text-lg font-semibold">Nadzorna plošča</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="p-2 rounded-full hover:bg-gray-100 relative">
                            <i data-feather="bell"></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-amber-200 flex items-center justify-center mr-2">
                                <i data-feather="user"></i>
                            </div>
                            <span class="text-sm">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="dashboard-card bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-sm">Skupni prihodek</p>
                                <h3 class="text-2xl font-bold mt-1"><?php echo htmlspecialchars(number_format((float)$revenue, 2, ',', '.')) . '€'; ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i data-feather="dollar-sign"></i>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2 flex items-center">
                            <i data-feather="trending-up" class="mr-1"></i> V primerjavi s prejšnjim mesecem
                        </p>
                    </div>

                    <div class="dashboard-card bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-sm">Naročila</p>
                                <h3 class="text-2xl font-bold mt-1"><?php echo htmlspecialchars((int)$orders_count); ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i data-feather="shopping-cart"></i>
                            </div>
                        </div>
                        <p class="text-blue-600 text-sm mt-2 flex items-center">
                            <i data-feather="trending-up" class="mr-1"></i> Zadnjih <?php echo count($recentOrders); ?> naročil
                        </p>
                    </div>

                    <div class="dashboard-card bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-sm">Izdelki</p>
                                <h3 class="text-2xl font-bold mt-1"><?php echo htmlspecialchars((int)$products_count); ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i data-feather="package"></i>
                            </div>
                        </div>
                        <p class="text-purple-600 text-sm mt-2 flex items-center">
                            <i data-feather="alert-circle" class="mr-1"></i> Pregled zalog
                        </p>
                    </div>

                    <div class="dashboard-card bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-sm">Stranke</p>
                                <h3 class="text-2xl font-bold mt-1"><?php echo htmlspecialchars((int)$customers_count); ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-amber-100 text-amber-600">
                                <i data-feather="users"></i>
                            </div>
                        </div>
                        <p class="text-amber-600 text-sm mt-2 flex items-center">
                            <i data-feather="trending-up" class="mr-1"></i> Novi uporabniki
                        </p>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="font-semibold">Zadnja naročila</h3>
                        <a href="#" class="text-sm text-amber-600 hover:text-amber-800">Prikaži vse</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Št. naročila</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kupec</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Znesek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500">Ni naročil za prikaz.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo htmlspecialchars($order['id']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                    $status = $order['status'] ?? 'pending';
                                                    $badgeClass = 'bg-gray-100 text-gray-800';
                                                    if ($status === 'paid' || $status === 'completed') $badgeClass = 'bg-green-100 text-green-800';
                                                    if ($status === 'pending') $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                    if ($status === 'shipped' || $status === 'sent') $badgeClass = 'bg-blue-100 text-blue-800';
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(number_format((float)$order['total_amount'], 2, ',', '.')) . '€'; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="orders.php?id=<?php echo urlencode($order['id']); ?>" class="text-amber-600 hover:text-amber-900">Ogled</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="font-semibold">Izdelki z nizko zalogo</h3>
                        <a href="#" class="text-sm text-amber-600 hover:text-amber-800">Prikaži vse</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izdelek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zaloga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded" src="https://static.photos/nature/200x200/101" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Cvetni prah 50g</div>
                                                <div class="text-sm text-gray-500">4,50€</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PRD-001</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Nizka zaloga</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="#" class="text-amber-600 hover:text-amber-900">Uredi</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded" src="https://static.photos/nature/200x200/102" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Balzam za ustnice</div>
                                                <div class="text-sm text-gray-500">2,50€</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PRD-005</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Opozorilo</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="#" class="text-amber-600 hover:text-amber-900">Uredi</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>