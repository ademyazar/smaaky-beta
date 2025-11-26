<?php
$pageTitle = 'Bestellingen';
$pageSubtitle = 'Bekijk en beheer alle binnenkomende bestellingen.';
$activeMenu = 'orders';
require_once __DIR__ . '/_header.php';

$stmt = $pdo->query("
    SELECT id, customer_name, phone, street, house_number, zip, city, total, created_at
    FROM orders
    ORDER BY id DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">Binnenkomende bestellingen</h2>
        <span class="text-xs text-slate-400">Totaal: <?= count($orders) ?></span>
    </div>

    <div class="divide-y divide-slate-100">
        <?php foreach ($orders as $o): ?>
            <a href="order_view.php?id=<?= $o['id'] ?>"
               class="flex items-center px-5 py-3 hover:bg-slate-50 transition">
                <div class="w-12 text-xs font-mono text-slate-500">#<?= $o['id'] ?></div>

                <div class="flex-1">
                    <div class="text-sm font-semibold"><?= htmlspecialchars($o['customer_name']) ?></div>
                    <div class="text-xs text-slate-500">
                        <?= htmlspecialchars($o['street']) ?> <?= htmlspecialchars($o['house_number']) ?>,
                        <?= htmlspecialchars($o['zip']) ?> <?= htmlspecialchars($o['city']) ?>
                    </div>
                </div>

                <div class="w-32 text-right text-sm font-semibold">
                    €<?= number_format($o['total'], 2) ?>
                </div>

                <div class="w-48 text-right text-xs text-slate-400">
                    <?= $o['created_at'] ?>
                </div>

                <div class="ml-3 text-xs font-semibold text-orange-600">Bekijken →</div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($orders)): ?>
            <div class="px-5 py-6 text-sm text-slate-500">Nog geen bestellingen.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
