<?php

session_start();

require_once "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["logged_in" => false]);
    exit;
}

 $user_id = (int) $_SESSION["user_id"];

function count_rows($conn, $sql, $uid) {
    $s = $conn->prepare($sql);
    $s->bind_param("i", $uid);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    return (int) $r["c"];
}

 $orders   = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status NOT IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
 $history  = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
 $cart     = count_rows($conn, "SELECT COUNT(*) AS c FROM cart_items INNER JOIN cart ON cart_items.cart_id = cart.id WHERE cart.user_id = ?", $user_id);
 $wishlist = count_rows($conn, "SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?", $user_id);

echo json_encode([
    "logged_in" => true,
    "orders"    => $orders,
    "history"   => $history,
    "cart"      => $cart,
    "wishlist"  => $wishlist,

    /* Badge = "in motion" only. To include history in the circle too,
       change the line below to:
       "total" => $orders + $history + $cart + $wishlist */
    "total"     => $orders + $cart + $wishlist
]);