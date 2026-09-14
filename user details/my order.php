<?php

session_start();

require_once "../php/db.php";

 $is_logged_in = isset($_SESSION["user_id"]);

 $user_id = $is_logged_in
    ? (int) $_SESSION["user_id"]
    : 0;

 $user = null;
 $counts = ["orders" => 0, "history" => 0, "cart" => 0, "wishlist" => 0];

if ($is_logged_in) {

    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    function count_rows($conn, $sql, $uid) {
        $s = $conn->prepare($sql);
        $s->bind_param("i", $uid);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        $s->close();
        return (int) $r["c"];
    }

    $counts["orders"]   = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status NOT IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
    $counts["history"]  = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
    $counts["cart"]     = count_rows($conn, "SELECT COUNT(*) AS c FROM cart_items INNER JOIN cart ON cart_items.cart_id = cart.id WHERE cart.user_id = ?", $user_id);
    $counts["wishlist"] = count_rows($conn, "SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?", $user_id);
}


 $view = (isset($_GET["view"]) && $_GET["view"] === "history") ? "history" : "orders";


 $orders = [];

if ($is_logged_in) {

    $stmt = $conn->prepare("
        SELECT
            id,
            total_amount,
            payment_method,
            payment_reference,
            status,
            shipping_name,
            created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

    if ($stmt) {

        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $stmt->close();
    }
}


function is_past_status($status) {
    $s = strtolower($status ?? "");
    return in_array($s, ["delivered", "completed", "cancelled"]);
}


/* Split into active + history */
 $active_orders = [];
 $past_orders = [];

foreach ($orders as $order) {
    if (is_past_status($order["status"] ?? "")) {
        $past_orders[] = $order;
    } else {
        $active_orders[] = $order;
    }
}


 $order_items = [];

 $all_ids = array_merge(
    array_map(fn($o) => (int) $o["id"], $active_orders),
    array_map(fn($o) => (int) $o["id"], $past_orders)
);

if (!empty($all_ids)) {

    $placeholders = implode(",", array_fill(0, count($all_ids), "?"));
    $types = str_repeat("i", count($all_ids));

    $sql = "
        SELECT
            order_items.order_id,
            order_items.quantity,
            order_items.price,

            products.name AS product_name,
            products.image AS product_image,

            product_variants.size,
            product_variants.color

        FROM order_items

        INNER JOIN products
            ON order_items.product_id = products.id

        LEFT JOIN product_variants
            ON order_items.variant_id = product_variants.id

        WHERE order_items.order_id IN ($placeholders)

        ORDER BY order_items.id ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param($types, ...$all_ids);

        if ($stmt->execute()) {

            $result = $stmt->get_result();

            while ($item = $result->fetch_assoc()) {

                $oid = (int) $item["order_id"];

                if (!isset($order_items[$oid])) {
                    $order_items[$oid] = [];
                }

                $order_items[$oid][] = $item;
            }
        }

        $stmt->close();
    }
}


 $reordered = isset($_GET["reordered"]) ? (int) $_GET["reordered"] : null;
 $skipped   = isset($_GET["skipped"]) ? (int) $_GET["skipped"] : 0;
 $removed   = isset($_GET["removed"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $view === "history" ? "My History" : "My Orders" ?> | JAC</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../account.css">
    <link rel="stylesheet" href="../orders.css">

</head>

<body>


<header>

    <a href="../index.php">
        <img src="../Images/Final LOGO.svg" alt="JAC Logo" class="logo">
    </a>

    <nav>
        <a href="../index.php#home">HOME</a>
        <a href="../index.php#about">ABOUT US</a>
        <a href="../index.php#collections">COLLECTION</a>
        <a href="../store.php">STORE</a>
        <a href="../index.php#contact">CONTACT</a>
    </nav>

</header>


<main class="orders-page">

    <div class="account-layout">

        <!-- SIDEBAR -->
        <aside class="account-sidebar">

            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo $is_logged_in ? strtoupper(substr($user["first_name"] ?? "J", 0, 1)) : "👤"; ?>
                </div>
                <p class="sidebar-name">
                    <?php echo $is_logged_in
                        ? strtoupper(htmlspecialchars(($user["first_name"] ?? "") . " " . ($user["last_name"] ?? "")))
                        : "GUEST"; ?>
                </p>
                <p class="sidebar-email">
                    <?php echo $is_logged_in ? htmlspecialchars($user["email"] ?? "") : "Not logged in"; ?>
                </p>
            </div>

            <nav class="sidebar-nav">
                <a href="my%20account.php?tab=profile">PROFILE</a>
                <a href="my%20account.php?tab=security">SECURITY</a>

                <a href="my%20order.php" class="<?php echo ($is_logged_in && $view === "orders") ? "active" : ""; ?>">
                    MY ORDERS <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["orders"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20order.php?view=history" class="<?php echo ($is_logged_in && $view === "history") ? "active" : ""; ?>">
                    MY HISTORY <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["history"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20cart.php">
                    MY CART <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["cart"]; ?></span><?php endif; ?>
                </a>
                <a href="wishlist.php">
                    WISHLIST <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["wishlist"]; ?></span><?php endif; ?>
                </a>
            </nav>

            <div class="sidebar-logout">
                <?php if ($is_logged_in): ?>
                    <a href="../logout.php">LOG OUT</a>
                <?php else: ?>
                    <a href="../login.php">LOG IN</a>
                <?php endif; ?>
            </div>

        </aside>


        <!-- CONTENT -->
        <div class="account-content">


        <section class="orders-section">


            <div class="orders-heading">

                <h1><?= $view === "history" ? "MY HISTORY" : "MY ORDERS"; ?></h1>

                <p>
                    <?= $view === "history"
                        ? "Your completed and cancelled orders. Buy again or remove them from your history."
                        : "Your orders that are currently being processed."; ?>
                </p>

            </div>


            <?php if ($reordered !== null): ?>

                <div class="reorder-banner <?= $skipped > 0 ? "warn" : "ok"; ?>">

                    <?php if ($reordered > 0 && $skipped > 0): ?>
                        Added <?= $reordered ?> item(s) back to your cart.
                        <?= $skipped ?> item(s) were out of stock or adjusted.
                    <?php elseif ($reordered > 0): ?>
                        <?= $reordered ?> item(s) added back to your cart!
                    <?php else: ?>
                        None of these items are currently in stock.
                    <?php endif; ?>

                    — <a href="my%20cart.php">view cart</a>

                </div>

            <?php endif; ?>


            <?php if ($removed): ?>

                <div class="reorder-banner ok">
                    Order removed from your history.
                </div>

            <?php endif; ?>


            <?php if (!$is_logged_in) { ?>


                <div class="logged-out-card">

                    <div class="logged-out-avatar">👤</div>

                    <h2>NOT LOGGED IN</h2>

                    <p>
                        Please log in to view your orders.
                        Don't have an account yet? Create one for free.
                    </p>

                    <div class="logged-out-actions">

                        <a href="../login.php">LOGIN</a>

                        <a href="../register.php" class="secondary">REGISTER</a>

                    </div>

                </div>


            <?php } else {


                $display_orders = ($view === "history") ? $past_orders : $active_orders;

                $empty_title = ($view === "history") ? "NO ORDER HISTORY YET" : "NO ACTIVE ORDERS";

                $empty_text = ($view === "history")
                    ? "Completed and cancelled orders will appear here."
                    : "Orders being processed appear here. Completed orders move to My History.";


                if (empty($display_orders)) { ?>


                    <div class="orders-empty">

                        <h2><?= $empty_title; ?></h2>

                        <p><?= $empty_text; ?></p>

                        <a href="../store.php" class="orders-button">SHOP NOW</a>

                    </div>


                <?php } else { ?>


                    <div class="orders-list">


                        <?php foreach ($display_orders as $order) { ?>


                            <?php

                            $order_id = (int) $order["id"];

                            $status = $order["status"] ?? "Pending";

                            $status_class = strtolower(str_replace(" ", "-", $status));

                            $order_total = (float) $order["total_amount"];

                            $order_date = date("F j, Y", strtotime($order["created_at"]));

                            ?>


                            <article class="order-card">


                                <div class="order-card-header">

                                    <div>

                                        <span class="order-label">ORDER</span>

                                        <h2>#<?= $order_id; ?></h2>

                                    </div>

                                    <div class="order-date">
                                        <?= htmlspecialchars($order_date); ?>
                                    </div>

                                </div>


                                <div class="order-status-row">

                                    <span class="order-label">STATUS</span>

                                    <span class="order-status <?= htmlspecialchars($status_class); ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </span>

                                </div>

                                <div class="order-status-row" style="margin-top: 6px; font-size: 13px;">

                                    <span class="order-label">PAYMENT</span>

                                    <span style="font-weight: 500;">
                                        <?= htmlspecialchars($order["payment_method"] ?? "Cash on Delivery"); ?>
                                        <?php if (!empty($order["payment_reference"])): ?>
                                            <span style="color: #666; font-size: 12px; margin-left: 5px;">(Ref: <?= htmlspecialchars($order["payment_reference"]); ?>)</span>
                                        <?php endif; ?>
                                    </span>

                                </div>


                                <div class="order-items">


                                    <?php $items = $order_items[$order_id] ?? []; ?>


                                    <?php if (!empty($items)) { ?>


                                        <?php foreach ($items as $item) { ?>


                                            <?php

                                            $image = trim($item["product_image"] ?? "");

                                            if (stripos($image, "images/") === 0) {
                                                $image_path = "../" . $image;
                                            } else {
                                                $image_path = "../Images/" . $image;
                                            }

                                            $quantity = (int) $item["quantity"];

                                            ?>


                                            <div class="order-item">


                                                <div class="order-item-image">

                                                    <?php if ($image !== "") { ?>

                                                        <img
                                                            src="<?= htmlspecialchars($image_path); ?>"
                                                            alt="<?= htmlspecialchars($item["product_name"]); ?>"
                                                        >

                                                    <?php } else { ?>

                                                        <div class="order-no-image">JAC</div>

                                                    <?php } ?>

                                                </div>


                                                <div class="order-item-info">

                                                    <h3><?= htmlspecialchars($item["product_name"]); ?></h3>


                                                    <div class="order-item-details">

                                                        <?php if (!empty($item["size"])) { ?>

                                                            <span>
                                                                Size:
                                                                <?= htmlspecialchars($item["size"]); ?>
                                                            </span>

                                                        <?php } ?>


                                                        <?php if (!empty($item["color"])) { ?>

                                                            <span>
                                                                Color:
                                                                <?= htmlspecialchars($item["color"]); ?>
                                                            </span>

                                                        <?php } ?>

                                                        <span>Qty: <?= $quantity; ?></span>

                                                    </div>

                                                </div>


                                            </div>


                                        <?php } ?>


                                    <?php } else { ?>


                                        <p class="order-no-items">
                                            Order items unavailable.
                                        </p>


                                    <?php } ?>


                                </div>


                                <div class="order-card-footer">

                                    <div>

                                        <span class="order-label">TOTAL</span>

                                        <strong>₱<?= number_format($order_total, 2); ?></strong>

                                    </div>


                                    <?php if ($view === "history"): ?>

                                        <div class="history-actions">

                                            <form action="../php/reorder.php" method="POST" class="reorder-form">

                                                <input type="hidden" name="order_id" value="<?= $order_id; ?>">

                                                <button type="submit" class="reorder-button">
                                                    BUY AGAIN
                                                </button>

                                            </form>


                                            <form action="../php/remove_order_history.php" method="POST"
                                                  onsubmit="return confirm('Remove order #<?= $order_id; ?> from your history? This cannot be undone.');">

                                                <input type="hidden" name="order_id" value="<?= $order_id; ?>">

                                                <button type="submit" class="remove-history-button">
                                                    REMOVE
                                                </button>

                                            </form>

                                        </div>

                                    <?php elseif (in_array($status, ["Pending", "Processing"])): ?>

                                        <button
                                            type="button"
                                            class="cancel-order-btn"
                                            data-order-id="<?= $order_id; ?>"
                                        >
                                            CANCEL ORDER
                                        </button>

                                    <?php endif; ?>

                                </div>


                            </article>


                        <?php } ?>


                    </div>


                <?php } ?>

            <?php } ?>


        </section>


        </div><!-- /account-content -->

    </div>

</main>


<footer>

    <div class="footer-bottom">

        <p>© 2026 All Rights Reserved.</p>

        <div class="footer-right">

            <a href="../legal/privacy.html">PRIVACY POLICY</a>
            <a href="../legal/terms.html">TERMS OF USE</a>

            <div class="footer-social">

                <a href="#"><img src="../images/instagram.png" alt="Instagram"></a>
                <a href="#"><img src="../images/twitter.png" alt="Twitter"></a>
                <a href="#"><img src="../images/facebook.png" alt="Facebook"></a>

            </div>

        </div>

    </div>

</footer>


<style>

.orders-page {
    min-height: 700px;
    padding: 15px 10% 40px;
    background-color: rgb(240, 240, 240);
}

.account-content {
    padding: 20px 40px 40px;
}

.orders-section {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.orders-heading {
    margin-bottom: 20px;
    text-align: center;
}

.orders-heading h1 {
    margin-bottom: 10px;
    font-size: 40px;
    font-weight: bold;
    letter-spacing: 2px;
}

.orders-heading p {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
}

.reorder-banner {
    margin: 0 0 20px;
    padding: 12px 20px;
    font-size: 14px;
}

.reorder-banner.ok {
    background: #eaf3ea;
    border: 1px solid #bcd9bc;
    color: #2c5e2c;
}

.reorder-banner.warn {
    background: #f5f0e5;
    border: 1px solid #ddd0b0;
    color: #6b5b2a;
}

.reorder-banner a {
    color: inherit;
    font-weight: bold;
}

.orders-empty {
    padding: 70px 30px;
    background: white;
    border: 1px solid #e2e2e2;
    text-align: center;
}

.orders-empty h2 {
    margin-bottom: 15px;
    font-size: 25px;
    font-weight: bold;
}

.orders-empty p {
    margin-bottom: 30px;
    color: #666;
    font-size: 14px;
}

.orders-button {
    display: inline-block;
    padding: 13px 28px;
    background: black;
    color: white;
    text-decoration: none;
    border: 1px solid black;
    font-size: 12px;
    letter-spacing: 1px;
    transition: 0.2s ease;
}

.orders-button:hover {
    background: #333;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    max-height: 370px;
    overflow-y: auto;
    padding-right: 10px;
}

.orders-list::-webkit-scrollbar {
    width: 6px;
}

.orders-list::-webkit-scrollbar-track {
    background: #f5f5f5;
}

.orders-list::-webkit-scrollbar-thumb {
    background: #ccc;
}

.order-card {
    background: white;
    border: 1px solid #e2e2e2;
}

.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 25px;
    border-bottom: 1px solid #e5e5e5;
}

.order-card-header h2 {
    margin: 2px 0 0;
    font-size: 17px;
    font-weight: 500;
    line-height: 1.2;
}

.order-label {
    display: block;
    margin-bottom: 2px;
    color: #777;
    font-size: 9px;
    letter-spacing: 1.5px;
    line-height: 1.2;
}

.order-date {
    color: #666;
    font-size: 12px;
    margin: 0;
}

.order-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 25px;
    border-bottom: 1px solid #e5e5e5;
}

.order-status {
    padding: 4px 10px;
    border: 1px solid #ccc;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.order-status.pending {
    background: #f5f5f5;
}

.order-status.processing {
    background: #eeeeee;
}

.order-status.shipped {
    background: #e8e8e8;
}

.order-status.delivered {
    background: #dedede;
}

.order-status.cancelled {
    background: #f1e5e5;
    color: #8a2222;
}

.order-items {
    padding: 0 25px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 12px 0;
    border-bottom: 1px solid #eeeeee;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 180px;
    height: 220px;
    flex-shrink: 0;
    background: #f1f1f1;
    overflow: hidden;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #777;
    font-size: 12px;
    letter-spacing: 2px;
}

.order-item-info {
    flex: 1;
}

.order-item-info h3 {
    margin-bottom: 5px;
    font-size: 17px;
    font-weight: 500;
}

.order-item-details {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    color: #777;
    font-size: 12px;
}

.order-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 25px;
    border-top: 1px solid #e5e5e5;
}

.order-card-footer strong {
    display: block;
    margin-top: 2px;
    font-size: 15px;
    font-weight: 500;
}

.history-actions {
    display: flex;
    gap: 10px;
}

.reorder-form {
    margin: 0;
}

.reorder-button {
    padding: 10px 20px;
    background: white;
    color: black;
    border: 1px solid black;
    cursor: pointer;
    font-size: 12px;
    letter-spacing: 1px;
}

.reorder-button:hover {
    background: black;
    color: white;
}

.remove-history-button {
    padding: 10px 20px;
    background: white;
    color: #8a2222;
    border: 1px solid #8a2222;
    cursor: pointer;
    font-size: 12px;
    letter-spacing: 1px;
}

.remove-history-button:hover {
    background: #8a2222;
    color: white;
}

@media (max-width: 700px) {

    .orders-page {
        padding: 20px 20px 60px;
    }

    .account-content {
        padding: 15px 20px 25px;
    }

    .orders-heading h1 {
        font-size: 26px;
    }

    .order-card-header {
        padding: 15px;
        align-items: flex-start;
        gap: 15px;
    }

    .order-status-row {
        padding: 12px 15px;
    }

    .order-items {
        padding: 5px 15px;
    }

    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .order-item-image {
        width: 70px;
        height: 90px;
    }

    .order-item-info h3 {
        font-size: 14px;
    }

    .order-item-details {
        flex-direction: column;
        gap: 5px;
    }

    .order-card-footer {
        padding: 15px;
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

}


.cancel-order-btn {
    padding: 10px 20px;
    background: white;
    color: #c0392b;
    border: 1.5px solid #c0392b;
    cursor: pointer;
    font-size: 12px;
    letter-spacing: 1px;
    font-family: inherit;
    transition: background 0.2s, color 0.2s;
}

.cancel-order-btn:hover {
    background: #c0392b;
    color: white;
}

/* Cancel Confirmation Modal */
#cancel-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

#cancel-modal-overlay.open {
    display: flex;
}

#cancel-modal {
    background: #fff;
    padding: 36px 32px 28px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 40px rgba(0,0,0,0.22);
}

#cancel-modal h3 {
    font-size: 18px;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
}

#cancel-modal p {
    font-size: 14px;
    color: #555;
    margin-bottom: 26px;
    line-height: 1.6;
}

.cancel-modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

#cancel-modal-confirm {
    padding: 11px 28px;
    background: #c0392b;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 13px;
    letter-spacing: 1px;
    font-family: inherit;
}

#cancel-modal-confirm:hover { background: #a93226; }

#cancel-modal-dismiss {
    padding: 11px 28px;
    background: white;
    color: #333;
    border: 1.5px solid #aaa;
    cursor: pointer;
    font-size: 13px;
    letter-spacing: 1px;
    font-family: inherit;
}

#cancel-modal-dismiss:hover { background: #f5f5f5; }
</style>

<!-- Cancel Confirmation Modal -->
<div id="cancel-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
    <div id="cancel-modal">
        <h3 id="cancel-modal-title">Cancel Order?</h3>
        <p id="cancel-modal-text">Are you sure you want to cancel Order <strong id="cancel-order-num"></strong>?<br>This action cannot be undone.</p>
        <div class="cancel-modal-actions">
            <button id="cancel-modal-confirm">YES, CANCEL</button>
            <button id="cancel-modal-dismiss">KEEP ORDER</button>
        </div>
    </div>
</div>

<script src="../script.js?v=3"></script>

<script>
(function () {
    const overlay    = document.getElementById("cancel-modal-overlay");
    const numEl      = document.getElementById("cancel-order-num");
    const confirmBtn = document.getElementById("cancel-modal-confirm");
    const dismissBtn = document.getElementById("cancel-modal-dismiss");
    let pendingOrderId = null;

    function openModal(orderId) {
        pendingOrderId = orderId;
        numEl.textContent = "#" + orderId;
        overlay.classList.add("open");
    }

    function closeModal() {
        overlay.classList.remove("open");
        pendingOrderId = null;
        confirmBtn.disabled = false;
        confirmBtn.textContent = "YES, CANCEL";
    }

    document.querySelectorAll(".cancel-order-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            openModal(btn.dataset.orderId);
        });
    });

    dismissBtn.addEventListener("click", closeModal);

    overlay.addEventListener("click", function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener("click", function () {
        if (!pendingOrderId) return;
        confirmBtn.disabled = true;
        confirmBtn.textContent = "Cancelling\u2026";

        fetch("../php/cancel_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "order_id=" + encodeURIComponent(pendingOrderId)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            closeModal();
            if (data.ok) {
                window.location.reload();
            } else {
                alert(data.message || "Could not cancel order.");
            }
        })
        .catch(function () {
            closeModal();
            alert("Network error. Please try again.");
        });
    });
})();
</script>

</body>

</html>