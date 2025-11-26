<?php
$pageTitle = 'Bestelling';
$activeMenu = 'orders';
require_once __DIR__ . '/_header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo '<p class="text-red-600 text-sm">Ongeldig bestelling-ID.</p>';
    require_once __DIR__ . '/_footer.php';
    exit;
}

// Bestelling ophalen
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo '<p class="text-red-600 text-sm">Bestelling niet gevonden.</p>';
    require_once __DIR__ . '/_footer.php';
    exit;
}

// Orderregels ophalen
$stmt = $pdo->prepare("
    SELECT id, product_name, qty, unit_price, total_price
    FROM order_items
    WHERE order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extras ophalen
$stmt = $pdo->prepare("
    SELECT oie.order_item_id, e.name AS extra_name, oie.price
    FROM order_item_extras oie
    JOIN extras e ON e.id = oie.extra_id
    WHERE oie.order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
");
$stmt->execute([$id]);
$extrasRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extras per order item groeperen
$extrasByItem = [];
foreach ($extrasRaw as $ex) {
    $extrasByItem[$ex['order_item_id']][] = $ex;
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Klantgegevens -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Klantgegevens</h2>
            <a href="orders.php" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">← Terug naar lijst</a>
        </div>

        <div class="text-sm space-y-1">
            <p><span class="font-semibold">Naam:</span> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><span class="font-semibold">Telefoon:</span> <?= htmlspecialchars($order['phone']) ?></p>
            <p><span class="font-semibold">E-mail:</span> <?= htmlspecialchars($order['email']) ?></p>
            <p>
                <span class="font-semibold">Adres:</span>
                <?= htmlspecialchars($order['street']) ?>
                <?= htmlspecialchars($order['house_number']) ?>,
                <?= htmlspecialchars($order['zip']) ?>
                <?= htmlspecialchars($order['city']) ?>
            </p>
            <p class="text-xs text-slate-400">Geplaatst op <?= $order['created_at'] ?></p>
        </div>

        <!-- Producten -->
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-lg font-semibold mb-3">Bestelde producten</h2>

            <div class="divide-y divide-slate-100">
                <?php foreach ($items as $item): ?>
                    <div class="py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="text-xs text-slate-500">Aantal: <?= $item['qty'] ?></div>
                            </div>
                            <div class="text-sm font-semibold">
                                €<?= number_format($item['total_price'], 2) ?>
                            </div>
                        </div>

                        <!-- Extras -->
                        <?php if (!empty($extrasByItem[$item['id']])): ?>
                            <div class="mt-2 pl-4 border-l border-dashed border-slate-200 space-y-1">
                                <?php foreach ($extrasByItem[$item['id']] as $ex): ?>
                                    <div class="flex items-center justify-between text-xs text-slate-600">
                                        <span>+ <?= htmlspecialchars($ex['extra_name']) ?></span>
                                        <span>€<?= number_format($ex['price'], 2) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <p class="text-sm text-slate-500">Geen orderregels gevonden.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Totaaloverzicht -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
        <h2 class="text-lg font-semibold mb-3">Totaal</h2>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span>Subtotaal</span>
                <span>€<?= number_format($order['subtotal'], 2) ?></span>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Bezorgkosten</span>
                <span>€<?= number_format($order['delivery_fee'], 2) ?></span>
            </div>
            <div class="border-t border-slate-100 pt-2 mt-2 flex justify-between font-semibold">
                <span>Totaal</span>
                <span>€<?= number_format($order['total'], 2) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>