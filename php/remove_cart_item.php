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
    GET CART ITEM ID
*/

$cart_item_id = isset($_POST["cart_item_id"])
    ? (int) $_POST["cart_item_id"]
    : 0;


if ($cart_item_id <= 0) {

    header("Location: ../user%20details/my%20cart.php");
    exit;

}


/*
    DELETE ONLY IF THE ITEM
    BELONGS TO THE LOGGED-IN USER
*/

$stmt = $conn->prepare("
    DELETE cart_items
    FROM cart_items

    INNER JOIN cart
        ON cart_items.cart_id = cart.id

    WHERE cart_items.id = ?
    AND cart.user_id = ?
");


if (!$stmt) {

    die("Unable to prepare remove cart item request.");

}


$stmt->bind_param(
    "ii",
    $cart_item_id,
    $user_id
);


if (!$stmt->execute()) {

    die("Unable to remove cart item.");

}


/*
    RETURN TO CART
*/

header("Location: ../user%20details/my%20cart.php");
exit;

?>