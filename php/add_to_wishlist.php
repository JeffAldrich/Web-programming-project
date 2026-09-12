<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| HELPERS
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

function redirect_out() {
    header("Location: ../user%20details/wishlist.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    if (isset($_POST["ajax"])) {
        respond(false, "Please log in first.", [
            "redirect" => "../login.php?required=wishlist"
        ]);
    }

    redirect_out();
}

 $user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| REQUEST CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect_out();
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT ID
|--------------------------------------------------------------------------
*/

 $product_id = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;

if ($product_id <= 0) {

    if (isset($_POST["ajax"])) {
        respond(false, "Invalid product.");
    }

    redirect_out();
}


/*
|--------------------------------------------------------------------------
| MAKE SURE PRODUCT EXISTS
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT id
    FROM products
    WHERE id = ?
    LIMIT 1
");

 $stmt->bind_param("i", $product_id);
 $stmt->execute();

 $product = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$product) {

    if (isset($_POST["ajax"])) {
        respond(false, "Product not found.");
    }

    redirect_out();
}


/*
|--------------------------------------------------------------------------
| CHECK IF ALREADY IN WISHLIST
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT id
    FROM wishlist
    WHERE user_id = ?
    AND product_id = ?
    LIMIT 1
");

 $stmt->bind_param("ii", $user_id, $product_id);
 $stmt->execute();

 $existing = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if ($existing) {

    if (isset($_POST["ajax"])) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS c
            FROM wishlist
            WHERE user_id = ?
        ");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_assoc()["c"];
        $stmt->close();

        respond(true, "Already in your wishlist!", ["count" => $count]);
    }

    redirect_out();
}


/*
|--------------------------------------------------------------------------
| ADD TO WISHLIST
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    INSERT INTO wishlist (user_id, product_id)
    VALUES (?, ?)
");

 $stmt->bind_param("ii", $user_id, $product_id);
 $stmt->execute();
 $stmt->close();


/*
|--------------------------------------------------------------------------
| COUNT FOR BADGE
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    SELECT COUNT(*) AS c
    FROM wishlist
    WHERE user_id = ?
");

 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $count = (int) $stmt->get_result()->fetch_assoc()["c"];
 $stmt->close();


/*
|--------------------------------------------------------------------------
| DONE
|--------------------------------------------------------------------------
*/

if (isset($_POST["ajax"])) {
    respond(true, "Added to wishlist!", ["count" => $count]);
}

redirect_out();

?>