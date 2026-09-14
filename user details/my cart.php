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

 $cart_items = [];
 $total = 0;

if ($is_logged_in) {

    $stmt = $conn->prepare("
        SELECT id
        FROM cart
        WHERE user_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $cart = $stmt->get_result()->fetch_assoc();

        if ($cart) {

            $cart_id = (int) $cart['id'];

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

                INNER JOIN products
                    ON cart_items.product_id = products.id

                INNER JOIN product_variants
                    ON cart_items.variant_id = product_variants.id

                WHERE cart_items.cart_id = ?

                ORDER BY cart_items.id ASC
            ");

            if ($stmt) {

                $stmt->bind_param("i", $cart_id);
                $stmt->execute();

                $result = $stmt->get_result();

                while ($item = $result->fetch_assoc()) {

                    $item['item_total'] = $item['price'] * $item['quantity'];

                    $total += $item['item_total'];

                    $cart_items[] = $item;
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JAC - My Cart</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../account.css">

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


<section class="cart-page">

    <div class="account-layout">

        <!-- SIDEBAR (always visible, full menu) -->
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

                <a href="my%20order.php">
                    MY ORDERS <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["orders"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20order.php?view=history">
                    MY HISTORY <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["history"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20cart.php" class="active">
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


        <div class="cart-container">

            <h1>MY CART</h1>

            <p class="cart-intro">Review the products you have selected.</p>


            <?php if (!$is_logged_in): ?>

                <div class="logged-out-card">

                    <div class="logged-out-avatar">👤</div>

                    <h2>NOT LOGGED IN</h2>

                    <p>
                        Please log in to view your cart.
                        Don't have an account yet? Create one for free.
                    </p>

                    <div class="logged-out-actions">

                        <a href="../login.php">LOGIN</a>

                        <a href="../register.php" class="secondary">REGISTER</a>

                    </div>

                </div>


            <?php elseif (empty($cart_items)): ?>

                <div class="empty-cart">

                    <h2>YOUR CART IS EMPTY</h2>

                    <p>You haven't added any products to your cart yet.</p>

                    <a href="../store.php" class="account-action">SHOP NOW</a>

                </div>


            <?php else: ?>


                <div class="cart-items">

                    <?php foreach ($cart_items as $item): ?>

                        <div class="cart-item">

                            <div class="cart-item-image">

                                <img src="../images/<?php echo htmlspecialchars($item['image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">

                            </div>


                            <div class="cart-item-details">

                                <h2><?php echo htmlspecialchars($item['name']); ?></h2>

                                <p>₱<?php echo number_format($item['price'], 2); ?></p>

                                <?php if (!empty($item['size']) && $item['size'] !== 'N/A'): ?><p>SIZE: <?php echo htmlspecialchars($item['size']); ?></p><?php endif; ?>

                                <p>COLOR: <?php echo htmlspecialchars($item['color']); ?></p>


                                <form action="../php/update_cart.php" method="POST" class="cart-quantity-form">

                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">

                                    <label>
                                        QUANTITY:
                                        <input type="number" name="quantity" class="cart-quantity"
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1" max="<?php echo $item['stock']; ?>"
                                               onchange="this.form.submit()">
                                    </label>

                                </form>


                                <form action="../php/remove_cart_item.php" method="POST">

                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">

                                    <button type="submit" class="remove-cart-item">REMOVE</button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="cart-total">

                    <h2>CART TOTAL</h2>

                    <p class="cart-price">₱<?php echo number_format($total, 2); ?></p>

                    <a href="checkout.php" class="checkout-button">CHECKOUT</a>

                </div>


            <?php endif; ?>

        </div>


        </div><!-- /account-content -->

    </div>

</section>


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


<script src="../script.js?v=3"></script>

</body>

</html>