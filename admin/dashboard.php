<?php
// dashboard.php
session_start();
require_once __DIR__ . '/../config.php';

// Aktif menu
$activePage = 'dashboard';

// Basit metrikler
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

$today = date('Y-m-d');
$ordersToday = (int)$pdo->query("
    SELECT COUNT(*) FROM orders
    WHERE DATE(created_at) = '$today'
")->fetchColumn();

$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();

$revenueToday = (float)$pdo->query("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE DATE(created_at) = '$today'
")->fetchColumn();

$activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$activeExtras   = (int)$pdo->query("SELECT COUNT(*) FROM extras WHERE is_active = 1")->fetchColumn();

$lastOrdersStmt = $pdo->query("
    SELECT id, customer_name, total, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
");
$lastOrders = $lastOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Smaaky Admin – Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen flex">

    <?php include __DIR__ . '/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="flex-1">
        <!-- Header bar -->
        <header class="h-16 px-8 flex items-center justify-between border-b border-slate-200 bg-white">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-xs text-slate-500 mt-0.5">Overzicht van je Smaaky bezorg-app.</p>
            </div>
            <div class="text-xs text-slate-500">
                <?php echo date('d-m-Y H:i'); ?>
            </div>
        </header>

        <!-- Content -->
        <section class="p-8 space-y-8">

            <!-- Stat cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Totaal bestellingen -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Totaal bestellingen</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?php echo $totalOrders; ?></p>
                </div>

                <!-- Bestellingen vandaag -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Bestellingen vandaag</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?php echo $ordersToday; ?></p>
                </div>

                <!-- Totale omzet -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Totale omzet</p>
                    <p class="mt-3 text-3xl font-black tracking-tight">
                        €<?php echo number_format($totalRevenue, 2, ',', '.'); ?>
                    </p>
                </div>

                <!-- Omzet vandaag -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Omzet vandaag</p>
                    <p class="mt-3 text-3xl font-black tracking-tight">
                        €<?php echo number_format($revenueToday, 2, ',', '.'); ?>
                    </p>
                </div>
            </div>

            <!-- Active counts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Actieve producten</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?php echo $activeProducts; ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Actieve toppings</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?php echo $activeExtras; ?></p>
                </div>
            </div>

            <!-- Last orders -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold tracking-tight">Laatste bestellingen</h2>
                    <a href="/admin/orders.php" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Alles bekijken →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Klant</th>
                            <th class="px-6 py-3 text-left">Totaal</th>
                            <th class="px-6 py-3 text-left">Datum</th>
                            <th class="px-6 py-3 text-right">Actie</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lastOrders as $order): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-6 py-3 text-slate-500">#<?php echo $order['id']; ?></td>
                                <td class="px-6 py-3"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td class="px-6 py-3">
                                    €<?php echo number_format($order['total'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-3 text-slate-500 text-xs">
                                    <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="/admin/order_view.php?id=<?php echo $order['id']; ?>"
                                       class="text-xs font-semibold text-slate-700 hover:text-black">
                                        Bekijken →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lastOrders)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-slate-400 text-sm">
                                    Nog geen bestellingen.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </main>
</div>
</body>
</html>
