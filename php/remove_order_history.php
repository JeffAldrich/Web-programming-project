<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?required=orders");
    exit;
}

 $user_id = (int) $_SESSION["user_id"];


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user%20details/my%20order.php?view=history");
    exit;
}

 $order_id = isset($_POST["order_id"]) ? (int) $_POST["order_id"] : 0;

if ($order_id <= 0) {
    header("Location: ../user%20details/my%20order.php?view=history");
    exit;
}


/* Ownership check */
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
    header("Location: ../user%20details/my%20order.php?view=history");
    exit;
}
 $stmt->close();


/* Delete children first, then the order */
 $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
 $stmt->bind_param("i", $order_id);
 $stmt->execute();
 $stmt->close();

 $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
 $stmt->bind_param("ii", $order_id, $user_id);
 $stmt->execute();
 $stmt->close();


header("Location: ../user%20details/my%20order.php?view=history&removed=1");
exit;