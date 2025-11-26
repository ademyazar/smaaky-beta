<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config.php';

try {
    // 1) KATEGORİLERİ ÇEK
    $stmt = $pdo->query("SELECT id, name, sort_order FROM categories ORDER BY sort_order ASC, name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) ÜRÜNLERİ ÇEK
    $stmt = $pdo->query("
        SELECT id, category_id, name, description, price, image, image_url, has_toppings, is_active
        FROM products
        WHERE is_active = 1
        ORDER BY category_id ASC, name ASC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) PRODUCT → EXTRAS ilişkilerini tek seferde çek
    $stmt = $pdo->query("
        SELECT pe.product_id, e.id AS extra_id, e.name, e.price
        FROM product_extras pe
        JOIN extras e ON e.id = pe.extra_id
        WHERE e.is_active = 1
        ORDER BY e.name ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $extras_by_product = [];
    foreach ($rows as $r) {
        $pid = $r['product_id'];
        if (!isset($extras_by_product[$pid])) {
            $extras_by_product[$pid] = [];
        }
        $extras_by_product[$pid][] = [
            "id"    => (int)$r['extra_id'],
            "name"  => $r['name'],
            "price" => (float)$r['price']
        ];
    }

    echo json_encode([
        "status" => "success",
        "categories" => $categories,
        "products" => $products,
        "extras_by_product" => $extras_by_product
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
