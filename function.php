<?php

require_once "php/db.php";

/*
    JAC DATABASE FUNCTIONS

    This file contains reusable functions for the JAC website.
    It uses the existing MySQLi database connection from php/db.php.
*/


/* ================================
   GET ALL PRODUCTS
================================ */
function getAllProducts($conn)
{
    $sql = "
        SELECT
            products.id,
            products.name,
            products.price,
            products.image,
            products.description,
            products.category_id,
            categories.name AS category_name
        FROM products
        LEFT JOIN categories
            ON products.category_id = categories.id
        ORDER BY products.id ASC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


/* ================================
   GET SINGLE PRODUCT
================================ */
function getProduct($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT
            products.id,
            products.name,
            products.price,
            products.image,
            products.description,
            products.category_id,
            categories.name AS category_name
        FROM products
        LEFT JOIN categories
            ON products.category_id = categories.id
        WHERE products.id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}


/* ================================
   GET PRODUCT VARIANTS
================================ */
function getProductVariants($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT
            id,
            product_id,
            size,
            color,
            stock
        FROM product_variants
        WHERE product_id = ?
        ORDER BY id ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


/* ================================
   GET USER
================================ */
function getUser($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}


/* ================================
   GET USER CART
================================ */
function getUserCart($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT id
        FROM cart
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}


/* ================================
   GET CART ITEMS
================================ */
function getCartItems($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT
            cart_items.id AS cart_item_id,
            cart_items.quantity,

            products.id AS product_id,
            products.name,
            products.price,
            products.image,

            product_variants.id AS variant_id,
            product_variants.size,
            product_variants.color,
            product_variants.stock

        FROM cart_items

        INNER JOIN cart
            ON cart_items.cart_id = cart.id

        INNER JOIN products
            ON cart_items.product_id = products.id

        INNER JOIN product_variants
            ON cart_items.variant_id = product_variants.id

        WHERE cart.user_id = ?

        ORDER BY cart_items.id ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


/* ================================
   CALCULATE CART TOTAL
================================ */
function getCartTotal($cart_items)
{
    $total = 0;

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    return $total;
}