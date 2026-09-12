<?php

session_start();

require_once "db.php";

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?required=checkout");
    exit;
}

$user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| REQUEST CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user%20details/checkout.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET SHIPPING INFORMATION
|--------------------------------------------------------------------------
*/

$shipping_name = trim($_POST["shipping_name"] ?? "");
$shipping_email = trim($_POST["shipping_email"] ?? "");
$shipping_phone = trim($_POST["shipping_phone"] ?? "");
$shipping_address = trim($_POST["shipping_address"] ?? "");
$shipping_city = trim($_POST["shipping_city"] ?? "");
$shipping_province = trim($_POST["shipping_province"] ?? "");
$shipping_postal_code = trim($_POST["shipping_postal_code"] ?? "");


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $shipping_name === "" ||
    $shipping_email === "" ||
    $shipping_phone === "" ||
    $shipping_address === "" ||
    $shipping_city === "" ||
    $shipping_province === "" ||
    $shipping_postal_code === ""
) {
    die("Please complete all shipping information.");
}

if (!filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}


/*
|--------------------------------------------------------------------------
| GET USER CART
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare cart request.");
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Unable to load cart.");
}

$result = $stmt->get_result();

$cart = $result->fetch_assoc();

$stmt->close();

if (!$cart) {
    die("Your cart is empty.");
}

$cart_id = (int) $cart["id"];


/*
|--------------------------------------------------------------------------
| GET CART ITEMS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        cart_items.id AS cart_item_id,
        cart_items.product_id,
        cart_items.variant_id,
        cart_items.quantity,

        products.name,
        products.price,

        product_variants.size,
        product_variants.color,
        product_variants.stock

    FROM cart_items

    INNER JOIN products
        ON cart_items.product_id = products.id

    INNER JOIN product_variants
        ON cart_items.variant_id = product_variants.id

    WHERE cart_items.cart_id = ?

    ORDER BY cart_items.id ASC
");

if (!$stmt) {
    die("Unable to prepare cart items request.");
}

$stmt->bind_param("i", $cart_id);

if (!$stmt->execute()) {
    die("Unable to load cart items.");
}

$result = $stmt->get_result();

$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();


/*
|--------------------------------------------------------------------------
| EMPTY CART CHECK
|--------------------------------------------------------------------------
*/

if (count($cart_items) === 0) {
    die("Your cart is empty.");
}


/*
|--------------------------------------------------------------------------
| BEGIN TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | VERIFY STOCK AND CALCULATE TOTAL
    |--------------------------------------------------------------------------
    */

    $order_total = 0;

    foreach ($cart_items as &$item) {

        $variant_id = (int) $item["variant_id"];
        $quantity = (int) $item["quantity"];

        /*
        | Lock the variant row while processing the order.
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                stock
            FROM product_variants
            WHERE id = ?
            FOR UPDATE
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to prepare stock verification."
            );
        }

        $stmt->bind_param(
            "i",
            $variant_id
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to verify product stock."
            );
        }

        $result = $stmt->get_result();

        $variant = $result->fetch_assoc();

        $stmt->close();

        if (!$variant) {
            throw new Exception(
                "A product variant in your cart no longer exists."
            );
        }

        $available_stock = (int) $variant["stock"];

        if ($available_stock <= 0) {
            throw new Exception(
                $item["name"] .
                " (" .
                $item["size"] .
                ", " .
                $item["color"] .
                ") is out of stock."
            );
        }

        if ($quantity > $available_stock) {
            throw new Exception(
                "Not enough stock for " .
                $item["name"] .
                " (" .
                $item["size"] .
                ", " .
                $item["color"] .
                "). Available stock: " .
                $available_stock .
                "."
            );
        }

        /*
        | Price comes from the database.
        */

        $price = (float) $item["price"];

        $item_total = $price * $quantity;

        $order_total += $item_total;

        /*
        | Store the verified values.
        */

        $item["verified_price"] = $price;
        $item["verified_stock"] = $available_stock;
    }

    unset($item);


    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO orders
        (
            user_id,
            total_amount,
            status,
            shipping_name,
            shipping_email,
            shipping_phone,
            shipping_address,
            shipping_city,
            shipping_province,
            shipping_postal_code
        )
        VALUES (?, ?, 'Pending', ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception(
            "Unable to prepare order creation."
        );
    }

    $stmt->bind_param(
        "idsssssss",
        $user_id,
        $order_total,
        $shipping_name,
        $shipping_email,
        $shipping_phone,
        $shipping_address,
        $shipping_city,
        $shipping_province,
        $shipping_postal_code
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to create order."
        );
    }

    $order_id = $conn->insert_id;

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | INSERT ORDER ITEMS
    |--------------------------------------------------------------------------
    */

    foreach ($cart_items as $item) {

        $product_id = (int) $item["product_id"];
        $variant_id = (int) $item["variant_id"];
        $quantity = (int) $item["quantity"];
        $price = (float) $item["verified_price"];

        $stmt = $conn->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                variant_id,
                quantity,
                price
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to prepare order item."
            );
        }

        $stmt->bind_param(
            "iiiid",
            $order_id,
            $product_id,
            $variant_id,
            $quantity,
            $price
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to save order item."
            );
        }

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | REDUCE STOCK
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE product_variants
            SET stock = stock - ?
            WHERE id = ?
            AND stock >= ?
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to prepare stock update."
            );
        }

        $stmt->bind_param(
            "iii",
            $quantity,
            $variant_id,
            $quantity
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to update product stock."
            );
        }

        if ($stmt->affected_rows !== 1) {
            throw new Exception(
                "Stock changed while your order was being processed."
            );
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR CART ITEMS
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE cart_id = ?
    ");

    if (!$stmt) {
        throw new Exception(
            "Unable to prepare cart clearing."
        );
    }

    $stmt->bind_param(
        "i",
        $cart_id
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to clear cart."
        );
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | REDIRECT TO SUCCESS PAGE
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../success.php?order_id=" .
        $order_id
    );

    exit;


} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    die(
        "Unable to place your order: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            "UTF-8"
        )
    );
}

?>