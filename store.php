<?php

session_start();

require_once "php/db.php";

 $result = $conn->query("
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    ORDER BY products.id ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JAC - Store</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- HEADER -->
    <header>

        <a href="index.php">
            <img src="Images/Final LOGO.svg" alt="JAC Logo" class="logo">
        </a>

        <nav>
            <a href="index.php#home">HOME</a>
            <a href="index.php#about">ABOUT US</a>
            <a href="index.php#collections">COLLECTION</a>
            <a href="store.php">STORE</a>
            <a href="index.php#contact">CONTACT</a>
        </nav>

        <div class="account-menu">

            <button class="account-button">👤</button>

            <div class="account-dropdown">

                <a href="/JAC/user%20details/my%20account.php">MY ACCOUNT</a>

                <!-- FIXED: was my order.html (dead file) -->
                <a href="/JAC/user%20details/my%20order.php">MY ORDERS</a>

                <a href="/JAC/user%20details/my%20cart.php">MY CART</a>
                <a href="/JAC/user%20details/wishlist.php">WISHLIST</a>

                <div class="account-divider"></div>

                <a href="logout.php" class="logout">LOG OUT</a>

            </div>

        </div>

    </header>


    <!-- STORE -->
    <section id="store">

        <h1>JAC STORE</h1>

        <p class="store-intro">
            Explore our collection of timeless pieces designed
            for confidence, elegance, and every occasion.
        </p>


        <!-- CATEGORIES -->
        <div class="store-categories">

            <button class="active" data-category="all">ALL</button>

            <button data-category="suits">SUITS</button>

            <button data-category="shirts">SHIRTS</button>

            <button data-category="vests">VESTS</button>

            <button data-category="trousers">TROUSERS</button>

            <button data-category="accessories">ACCESSORIES</button>

        </div>


        <!-- PRODUCTS -->
        <div class="store-container">

            <?php while ($product = $result->fetch_assoc()): ?>

                <article
                    class="store-product"
                    data-category="<?php echo strtolower(htmlspecialchars($product['category_name'])); ?>"
                >

                    <img
                        src="images/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                    >

                    <h2>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h2>

                    <p>
                        ₱<?php echo number_format($product['price'], 2); ?>
                    </p>

                    <a href="product page/product.php?id=<?php echo (int) $product['id']; ?>">
                        VIEW PRODUCT
                    </a>

                </article>

            <?php endwhile; ?>


            <!-- COMING SOON -->
            <article class="store-product">

                <div class="product-placeholder">
                    <p>COMING SOON</p>
                </div>

            </article>

        </div>

    </section>


    <!-- FOOTER -->
    <footer>

        <div class="footer-bottom">

            <p>© 2026 All Rights Reserved.</p>

            <div class="footer-right">

                <!-- FIXED: was href="#" placeholders -->
                <a href="legal/privacy.html">PRIVACY POLICY</a>

                <a href="legal/terms.html">TERMS OF USE</a>

                <div class="footer-social">

                    <a href="#">
                        <img src="images/instagram.png" alt="Instagram">
                    </a>

                    <a href="#">
                        <img src="images/twitter.png" alt="Twitter">
                    </a>

                    <a href="#">
                        <img src="images/facebook.png" alt="Facebook">
                    </a>

                </div>

            </div>

        </div>

    </footer>


    <script src="script.js"></script>

</body>

</html>