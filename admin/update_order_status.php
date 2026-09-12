<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: orders.php");
    exit;
}

$order_id = isset($_POST["order_id"])
    ? (int) $_POST["order_id"]
    : 0;

$status = trim($_POST["status"] ?? "");

$allowed_statuses = [
    "Pending",
    "Processing",
    "Shipped",
    "Delivered",
    "Cancelled"
];

if ($order_id <= 0) {
    header("Location: orders.php");
    exit;
}

if (!in_array($status, $allowed_statuses, true)) {
    header(
        "Location: order_details.php?id=" .
        $order_id .
        "&error=invalid_status"
    );
    exit;
}

$stmt = $conn->prepare("
    UPDATE orders
    SET status = ?
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare order status update.");
}

$stmt->bind_param(
    "si",
    $status,
    $order_id
);

if (!$stmt->execute()) {
    die("Unable to update order status.");
}

$stmt->close();

header(
    "Location: order_details.php?id=" .
    $order_id .
    "&updated=1"
);

exit;

?>