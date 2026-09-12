<?php

session_start();

require_once "db.php";


/*
    CHECK LOGIN
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.php?required=cart");
    exit;

}

$user_id = (int) $_SESSION["user_id"];


/*
    CHECK REQUEST
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../user%20details/my%20cart.php");
    exit;

}


/*
    GET DATA
*/

$cart_item_id = isset($_POST["cart_item_id"])
    ? (int) $_POST["cart_item_id"]
    : 0;

$quantity = isset($_POST["quantity"])
    ? (int) $_POST["quantity"]
    : 0;


/*
    BASIC VALIDATION
*/

if ($cart_item_id <= 0) {

    header("Location: ../user%20details/my%20cart.php");
    exit;

}

if ($quantity < 1) {

    $quantity = 1;

}


/*
    CHECK CART ITEM OWNERSHIP
    AND GET STOCK
*/

$stmt = $conn->prepare("
    SELECT
        cart_items.id,
        product_variants.stock

    FROM cart_items

    INNER JOIN cart
        ON cart_items.cart_id = cart.id

    INNER JOIN product_variants
        ON cart_items.variant_id = product_variants.id

    WHERE cart_items.id = ?
    AND cart.user_id = ?

    LIMIT 1
");


if (!$stmt) {

    die("Unable to prepare cart update request.");

}


$stmt->bind_param(
    "ii",
    $cart_item_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$item = $result->fetch_assoc();


/*
    CART ITEM DOES NOT BELONG
    TO THIS USER
*/

if (!$item) {

    header("Location: ../user%20details/my%20cart.php");
    exit;

}


/*
    CHECK STOCK
*/

$stock = (int) $item["stock"];

if ($stock <= 0) {

    header("Location: ../user%20details/my%20cart.php");
    exit;

}

if ($quantity > $stock) {

    $quantity = $stock;

}


/*
    UPDATE QUANTITY
*/

$stmt = $conn->prepare("
    UPDATE cart_items

    INNER JOIN cart
        ON cart_items.cart_id = cart.id

    SET cart_items.quantity = ?

    WHERE cart_items.id = ?
    AND cart.user_id = ?
");


if (!$stmt) {

    die("Unable to prepare quantity update.");

}


$stmt->bind_param(
    "iii",
    $quantity,
    $cart_item_id,
    $user_id
);

$stmt->execute();


/*
    RETURN TO CART
*/

header("Location: ../user%20details/my%20cart.php");
exit;

?>