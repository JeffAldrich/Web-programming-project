<?php

session_start();

require_once "db.php";

/* ── Auth ── */
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    exit(json_encode(["ok" => false, "message" => "Not logged in."]));
}

$user_id = (int) $_SESSION["user_id"];

/* ── Input ── */
$order_id = isset($_POST["order_id"]) ? (int) $_POST["order_id"] : 0;

if ($order_id <= 0) {
    exit(json_encode(["ok" => false, "message" => "Invalid order."]));
}

/* ── Verify ownership and cancellable status ── */
$stmt = $conn->prepare("
    SELECT id, status
    FROM orders
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    exit(json_encode(["ok" => false, "message" => "Order not found."]));
}

$cancellable = ["Pending", "Processing"];
if (!in_array($order["status"], $cancellable, true)) {
    exit(json_encode([
        "ok"      => false,
        "message" => "This order can no longer be cancelled (status: {$order['status']})."
    ]));
}

/* ── Cancel the order and restore stock inside a transaction ── */
$conn->begin_transaction();

try {

    /* Restore stock for each item */
    $items_stmt = $conn->prepare("
        SELECT variant_id, quantity
        FROM order_items
        WHERE order_id = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $items_stmt->close();

    foreach ($items as $item) {
        if (!empty($item["variant_id"])) {
            $upd = $conn->prepare("
                UPDATE product_variants
                SET stock = stock + ?
                WHERE id = ?
            ");
            $upd->bind_param("ii", $item["quantity"], $item["variant_id"]);
            $upd->execute();
            $upd->close();
        }
    }

    /* Update order status */
    $cancel_stmt = $conn->prepare("
        UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?
    ");
    $cancel_stmt->bind_param("ii", $order_id, $user_id);
    $cancel_stmt->execute();
    $cancel_stmt->close();

    $conn->commit();

    exit(json_encode(["ok" => true, "message" => "Order #$order_id has been cancelled."]));

} catch (Exception $e) {
    $conn->rollback();
    exit(json_encode(["ok" => false, "message" => "Something went wrong. Please try again."]));
}
