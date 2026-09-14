<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

$variant_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($variant_id <= 0) {
    header("Location: variants.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE VARIANT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM product_variants
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare delete request.");
}

$stmt->bind_param(
    "i",
    $variant_id
);

if (!$stmt->execute()) {
    die("Unable to delete product variant.");
}

$stmt->close();

header("Location: variants.php");
exit;

?>