<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?required=orders");
    exit;
}

 $user_id = (int) $_SESSION["user_id"];


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user%20details/my%20order.php");
    exit;
}

 $order_id = isset($_POST["order_id"]) ? (int) $_POST["order_id"] : 0;

if ($order_id <= 0) {
    header("Location: ../user%20details/my%20order.php");
    exit;
}


/* Verify the order belongs to this user */
 $stmt = $conn->prepare("
    SELECT id
    FROM orders
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

 $stmt->bind_param("ii", $order_id, $user_id);
 $stmt->execute();

if (!$stmt->get_result()->fetch_assoc()) {
    $stmt->close();
    header("Location: ../user%20details/my%20order.php");
    exit;
}
 $stmt->close();


/* Get the order's items with current variant stock */
 $stmt = $conn->prepare("
    SELECT
        order_items.product_id,
        order_items.variant_id,
        order_items.quantity,

        product_variants.stock
    FROM order_items
    INNER JOIN product_variants
        ON order_items.variant_id = product_variants.id
    WHERE order_items.order_id = ?
");

 $stmt->bind_param("i", $order_id);
 $stmt->execute();
 $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
 $stmt->close();

if (empty($items)) {
    header("Location: ../user%20details/my%20order.php");
    exit;
}


/* Find or create cart */
 $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $cart = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$cart) {
    $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $conn->insert_id;
    $stmt->close();
} else {
    $cart_id = (int) $cart["id"];
}

 $added = 0;
 $skipped = 0;

foreach ($items as $item) {

    $product_id = (int) $item["product_id"];
    $variant_id = (int) $item["variant_id"];
    $qty        = (int) $item["quantity"];
    $stock      = (int) $item["stock"];

    /* Skip out-of-stock variants */
    if ($stock <= 0) {
        $skipped++;
        continue;
    }

    /* Clamp requested qty to current stock */
    if ($qty > $stock) {
        $qty = $stock;
        $skipped++; // partially adjusted — still added though
    }


    /* Merge with existing cart line if present */
    $stmt = $conn->prepare("
        SELECT id, quantity
        FROM cart_items
        WHERE cart_id = ?
        AND product_id = ?
        AND variant_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("iii", $cart_id, $product_id, $variant_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {

        $new_qty = (int) $existing["quantity"] + $qty;

        if ($new_qty > $stock) {
            $new_qty = $stock;
        }

        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $existing["id"]);
        $stmt->execute();
        $stmt->close();

        $added++;

    } else {

        $stmt = $conn->prepare("
            INSERT INTO cart_items (cart_id, product_id, variant_id, quantity)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiii", $cart_id, $product_id, $variant_id, $qty);
        $stmt->execute();
        $stmt->close();

        $added++;
    }
}


header("Location: ../user%20details/my%20order.php?view=history&reordered=" . $added . "&skipped=" . $skipped);
exit;