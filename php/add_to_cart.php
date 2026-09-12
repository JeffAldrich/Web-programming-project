<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| JSON HELPER
|--------------------------------------------------------------------------
*/

function respond($success, $message, $extra = []) {
    header("Content-Type: application/json");
    echo json_encode(array_merge(
        ["success" => $success, "message" => $message],
        $extra
    ));
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    respond(false, "Please log in first.", [
        "redirect" => "../login.php?required=cart"
    ]);
}

 $user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| CHECK REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request.");
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT INFORMATION
|--------------------------------------------------------------------------
*/

 $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
 $variant_id = isset($_POST['variant_id']) ? (int) $_POST['variant_id'] : 0;
 $quantity   = isset($_POST['quantity'])   ? (int) $_POST['quantity']   : 1;

if ($product_id <= 0 || $variant_id <= 0 || $quantity <= 0) {
    respond(false, "Invalid product information.");
}


/*
|--------------------------------------------------------------------------
| CHECK VARIANT
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT id, product_id, stock
    FROM product_variants
    WHERE id = ?
");

 $stmt->bind_param("i", $variant_id);
 $stmt->execute();

 $variant = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$variant) {
    respond(false, "Product variant not found.");
}

if ((int) $variant['product_id'] !== $product_id) {
    respond(false, "Invalid product variant.");
}


/*
|--------------------------------------------------------------------------
| CHECK STOCK
|--------------------------------------------------------------------------
*/

if ($variant['stock'] <= 0) {
    respond(false, "This variant is out of stock.");
}

if ($quantity > $variant['stock']) {
    respond(false, "Not enough stock available.");
}


/*
|--------------------------------------------------------------------------
| FIND OR CREATE USER'S CART
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

 $stmt->bind_param("i", $user_id);
 $stmt->execute();

 $cart = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$cart) {

    $stmt = $conn->prepare("
        INSERT INTO cart (user_id)
        VALUES (?)
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $cart_id = $conn->insert_id;
    $stmt->close();

} else {

    $cart_id = (int) $cart['id'];
}


/*
|--------------------------------------------------------------------------
| CHECK IF THIS VARIANT IS ALREADY IN CART
|--------------------------------------------------------------------------
*/

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

 $existing_item = $stmt->get_result()->fetch_assoc();
 $stmt->close();


/*
|--------------------------------------------------------------------------
| ADD OR UPDATE
|--------------------------------------------------------------------------
*/

if ($existing_item) {

    $new_quantity = (int) $existing_item['quantity'] + $quantity;

    if ($new_quantity > $variant['stock']) {
        respond(false, "You cannot add more than the available stock.");
    }

    $stmt = $conn->prepare("
        UPDATE cart_items
        SET quantity = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $new_quantity, $existing_item['id']);
    $stmt->execute();
    $stmt->close();

} else {

    $stmt = $conn->prepare("
        INSERT INTO cart_items
        (
            cart_id,
            product_id,
            variant_id,
            quantity
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("iiii", $cart_id, $product_id, $variant_id, $quantity);
    $stmt->execute();
    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| COUNT ITEMS IN CART (for the badge)
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT COUNT(*) AS c
    FROM cart_items
    WHERE cart_id = ?
");

 $stmt->bind_param("i", $cart_id);
 $stmt->execute();

 $count = (int) $stmt->get_result()->fetch_assoc()['c'];
 $stmt->close();


/*
|--------------------------------------------------------------------------
| DONE
|--------------------------------------------------------------------------
*/

respond(true, "Added to cart!", ["count" => $count]);

?>