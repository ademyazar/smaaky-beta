<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config.php';

// --- RAW INPUT AL ---
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// --- ZORUNLU ALANLAR ---
$required = ["customer_name", "phone", "email", "street", "house_number", "zip", "city", "cart"];
foreach ($required as $r) {
    if (!isset($data[$r]) || $data[$r] === "") {
        echo json_encode(["status" => "error", "message" => "Missing: $r"]);
        exit;
    }
}

$cart = $data["cart"];
$items = $cart["items"] ?? [];
$method = $cart["method"] ?? "delivery"; // delivery | pickup

$subtotal = $cart["subtotal"] ?? 0;
$delivery_fee = $cart["delivery_fee"] ?? 0;
$total = $cart["total"] ?? 0;

// --- ORDER EKLE ---
$stmt = $pdo->prepare("
    INSERT INTO orders 
    (customer_name, email, phone, street, house_number, zip, city, subtotal, delivery_fee, total, method, created_at)
    VALUES (:name, :email, :phone, :street, :house, :zip, :city, :subtotal, :delivery_fee, :total, :method, NOW())
");

$stmt->execute([
    ":name"         => $data["customer_name"],
    ":email"        => $data["email"],
    ":phone"        => $data["phone"],
    ":street"       => $data["street"],
    ":house"        => $data["house_number"],
    ":zip"          => $data["zip"],
    ":city"         => $data["city"],
    ":subtotal"     => $subtotal,
    ":delivery_fee" => $delivery_fee,
    ":total"        => $total,
    ":method"       => $method,
]);

$order_id = $pdo->lastInsertId();

// --- ORDER ITEMS EKLE ---
foreach ($items as $i) {
    $stmtProd = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, qty, unit_price, total_price)
        VALUES (:oid, :pid, :name, :qty, :unit, :total)
    ");

    $stmtProd->execute([
        ":oid"   => $order_id,
        ":pid"   => $i["id"],
        ":name"  => $i["name"],
        ":qty"   => $i["quantity"],
        ":unit"  => $i["price"],
        ":total" => $i["total"],
    ]);

    $order_item_id = $pdo->lastInsertId();

    // --- EXTRAS EKLE (VARSA) ---
    foreach ($i["extras"] as $ex) {
        $stmtEx = $pdo->prepare("
            INSERT INTO order_item_extras (order_item_id, extra_id, price)
            VALUES (:oiid, :exid, :price)
        ");

        $stmtEx->execute([
            ":oiid" => $order_item_id,
            ":exid" => $ex["id"],
            ":price" => $ex["price"],
        ]);
    }
}

// --- OUTPUT ---
echo json_encode([
    "status" => "success",
    "order_id" => $order_id
]);
exit;
?>