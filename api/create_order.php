<?php
header("Content-Type: application/json");

// DB bağlantısı
require_once "../config.php";

// JSON body al
$input = json_decode(file_get_contents("php://input"), true);

// Eğer JSON gelmemişse hata döndür
if (!$input) {
    echo json_encode([
        "status" => "error",
        "message" => "No JSON received"
    ]);
    exit;
}

// Müşteri bilgileri
$name   = $input["customer"]["name"] ?? "";
$phone  = $input["customer"]["phone"] ?? "";
$postal = $input["customer"]["postal"] ?? "";
$address = $input["customer"]["address"] ?? "";

// Sepet toplamları
$subtotal = $input["totals"]["subtotal"] ?? 0;
$delivery = $input["totals"]["delivery_fee"] ?? 0;
$total    = $input["totals"]["total"] ?? 0;

// Sepetteki ürünler
$cart = $input["cart"] ?? [];

try {
    // Transaction başlat
    $pdo->beginTransaction();

    // 1️⃣ ORDER ekle
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_name, phone, postal, address, subtotal, delivery_fee, total)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name, $phone, $postal, $address, $subtotal, $delivery, $total]);
    $orderId = $pdo->lastInsertId();

    // 2️⃣ SEPETTEKİ HER ÜRÜNÜ order_items tablosuna ekle
    foreach ($cart as $item) {

        $productId = $item["id"];
        $qty       = $item["qty"];
        $basePrice = $item["base_price"];
        $extrasPrice = $item["extras_price"];
        $totalPrice = $item["total_price"];

        // order_item ekle
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, qty, base_price, extras_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $productId, $qty, $basePrice, $extrasPrice, $totalPrice]);

        $orderItemId = $pdo->lastInsertId();

        // Eğer bu üründe ekstra varsa → order_item_extras içine ekle
        if (!empty($item["extras"])) {
            foreach ($item["extras"] as $ex) {
                $extraId = $ex["id"];

                $stmt = $pdo->prepare("
                    INSERT INTO order_item_extras (order_item_id, extra_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$orderItemId, $extraId]);
            }
        }
    }

    // Transaction tamamla
    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "order_id" => $orderId
    ]);

} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>