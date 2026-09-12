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

 $result = null;

if ($is_logged_in) {

    $stmt = $conn->prepare("
        SELECT
            wishlist.id AS wishlist_id,
            products.id AS product_id,
            products.name,
            products.price,
            products.image
        FROM wishlist
        INNER JOIN products
            ON wishlist.product_id = products.id
        WHERE wishlist.user_id = ?
        ORDER BY wishlist.created_at DESC
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JAC - My Wishlist</title>

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



<main class="account-page">

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
                <a href="my%20cart.php">
                    MY CART <?php if ($is_logged_in): ?><span class="sidebar-count"><?php echo $counts["cart"]; ?></span><?php endif; ?>
                </a>
                <a href="wishlist.php" class="active">
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


        <div class="account-container">

            <h1>MY WISHLIST</h1>


            <?php if (!$is_logged_in): ?>


                <div class="logged-out-card">

                    <div class="logged-out-avatar">👤</div>

                    <h2>NOT LOGGED IN</h2>

                    <p>
                        Please log in to view your wishlist.
                        Don't have an account yet? Create one for free.
                    </p>

                    <div class="logged-out-actions">

                        <a href="../login.php">LOGIN</a>

                        <a href="../register.php" class="secondary">REGISTER</a>

                    </div>

                </div>


            <?php elseif ($result->num_rows === 0): ?>


                <div class="empty-orders">

                    <h2>YOUR WISHLIST IS EMPTY</h2>

                    <p>You have not added any products to your wishlist yet.</p>

                    <a href="../store.php" class="account-action">SHOP NOW</a>

                </div>


            <?php else: ?>


                <div class="wishlist-grid">


                    <?php while ($item = $result->fetch_assoc()): ?>

                        <?php

                        $image = trim($item["image"]);

                        if (stripos($image, "images/") === 0) {
                            $image_path = "../" . $image;
                        } else {
                            $image_path = "../Images/" . $image;
                        }

                        ?>

                        <div class="wishlist-card">


                            <a href="../product page/product.php?id=<?php echo (int) $item["product_id"]; ?>">

                                <img
                                    src="<?php echo htmlspecialchars($image_path); ?>"
                                    alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                >

                            </a>


                            <div class="wishlist-info">

                                <h2><?php echo htmlspecialchars($item["name"]); ?></h2>

                                <p class="wishlist-price">
                                    ₱<?php echo number_format((float) $item["price"], 2); ?>
                                </p>


                                <div class="wishlist-buttons">

                                    <a href="../product page/product.php?id=<?php echo (int) $item["product_id"]; ?>"
                                       class="account-action">
                                        VIEW PRODUCT
                                    </a>

                                    <form action="../php/remove_from_wishlist.php" method="POST">

                                        <input type="hidden" name="wishlist_id"
                                               value="<?php echo (int) $item["wishlist_id"]; ?>">

                                        <button type="submit" class="wishlist-remove">REMOVE</button>

                                    </form>

                                </div>

                            </div>

                        </div>


                    <?php endwhile; ?>


                </div>


            <?php endif; ?>


        </div>


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


<script src="../script.js?v=3"></script>

</body>

</html>


<?php

if (isset($stmt)) {
    $stmt->close();
}

?>