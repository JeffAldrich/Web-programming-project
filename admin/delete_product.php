<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

$product_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        image
    FROM products
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare product request.");
}

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

$image = $product["image"];


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | DELETE CART ITEMS
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE product_id = ?
    ");

    if (!$stmt) {
        throw new Exception(
            "Unable to prepare cart item deletion."
        );
    }

    $stmt->bind_param(
        "i",
        $product_id
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to delete related cart items."
        );
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT VARIANTS
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM product_variants
        WHERE product_id = ?
    ");

    if (!$stmt) {
        throw new Exception(
            "Unable to prepare variant deletion."
        );
    }

    $stmt->bind_param(
        "i",
        $product_id
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to delete product variants."
        );
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception(
            "Unable to prepare product deletion."
        );
    }

    $stmt->bind_param(
        "i",
        $product_id
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to delete product."
        );
    }

    if ($stmt->affected_rows !== 1) {

        throw new Exception(
            "Product could not be deleted."
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
    | DELETE IMAGE FILE
    |--------------------------------------------------------------------------
    */

    if ($image !== "") {

        $image_path =
            "../Images/" .
            $image;

        if (
            file_exists($image_path)
        ) {

            unlink($image_path);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RETURN TO PRODUCTS
    |--------------------------------------------------------------------------
    */

    header("Location: products.php");
    exit;

} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    die(
        "Unable to delete product: " .
        htmlspecialchars($e->getMessage())
    );
}

?>