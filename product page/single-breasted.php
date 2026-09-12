<?php

session_start();

require_once "../php/db.php";

$is_logged_in = isset($_SESSION["user_id"]);

/*
|--------------------------------------------------------------------------
| SINGLE-BREASTED PRODUCT
|--------------------------------------------------------------------------
*/

$product_id = 1;

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        price,
        image,
        description
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT VARIANTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        size,
        color,
        stock
    FROM product_variants
    WHERE product_id = ?
    ORDER BY id ASC
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

$variants = $result->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| BUILD VARIANT DATA FOR JAVASCRIPT
|--------------------------------------------------------------------------
*/

$variant_data = [];

foreach ($variants as $variant) {

    $variant_data[] = [
        "id" => (int) $variant["id"],
        "size" => $variant["size"],
        "color" => $variant["color"],
        "stock" => (int) $variant["stock"]
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>JAC - Single Breasted Suit</title>

    <link
        rel="stylesheet"
        href="../style.css"
    >

</head>

<body>


<!-- HEADER -->

<header>

    <img
        src="../Images/Final LOGO.svg"
        alt="JAC Logo"
        class="logo"
    >

    <nav>

        <a href="../index.php#home">
            HOME
        </a>

        <a href="../index.php#about">
            ABOUT US
        </a>

        <a href="../index.php#collections">
            COLLECTION
        </a>

        <a href="../store.php">
            STORE
        </a>

        <a href="../index.php#contact">
            CONTACT
        </a>

    </nav>


    <div class="account-menu">

        <button
            type="button"
            class="account-button"
        >
            👤
        </button>


        <div class="account-dropdown">
            <a href="/JAC/user%20details/my%20account.php">
                MY ACCOUNT
            </a>
            <a href="/JAC/user%20details/my%20order.html">
                MY ORDERS
            </a>
            <a href="/JAC/user%20details/my%20cart.php">
                MY CART
            </a>
            <a href="/JAC/user%20details/wishlist.html">
                WISHLIST
            </a>
            <div class="account-divider"></div>
            <a
            href="/JAC/logout.php"
            class="logout"
            >
            LOG OUT
        </a>
    
    </div>

</div>

</header>

<!-- PRODUCT -->

<section class="product-page">

    <div class="product-container">


        <div class="product-image">

            <img
                src="../images/single-breasted.jpg"
                alt="Single Breasted Suit"
            >

        </div>


        <div class="product-details">

            <h1>
                <?= htmlspecialchars($product["name"]) ?>
            </h1>


            <p class="product-price">
                ₱<?= number_format($product["price"]) ?>
            </p>


            <p class="product-description">

                <?= htmlspecialchars($product["description"]) ?>

            </p>


            <h3>
                SIZE
            </h3>


            <div class="size-options">

                <button type="button">
                    S
                </button>

                <button type="button">
                    M
                </button>

                <button type="button">
                    L
                </button>

                <button type="button">
                    XL
                </button>

            </div>


            <h3>
                COLOR
            </h3>


            <div class="color-options">

                <button
                    type="button"
                    class="color-black"
                    title="Black"
                >
                </button>

                <button
                    type="button"
                    class="color-white"
                    title="White"
                >
                </button>

                <button
                    type="button"
                    class="color-navy"
                    title="Navy"
                >
                </button>

                <button
                    type="button"
                    class="color-gray"
                    title="Gray"
                >
                </button>

            </div>


            <h3>
                QUANTITY
            </h3>


            <input
                type="number"
                class="product-quantity"
                value="1"
                min="1"
            >


            <div class="product-actions">

                <button
                    type="button"
                    class="add-to-cart"
                >
                    ADD TO CART
                </button>


                <button
                    type="button"
                    class="add-to-wishlist"
                >
                    ♡
                </button>

            </div>


            <a
                href="../store.php"
                class="back-to-collections"
            >
                BACK TO STORE
            </a>

        </div>

    </div>

</section>


<!-- LOGIN REQUIRED POPUP -->

<div
    id="login-required-popup"
    class="login-required-popup"
>

    <div class="login-required-box">

        <button
            type="button"
            class="login-popup-close"
            aria-label="Close"
        >
            ×
        </button>


        <h2>
            LOGIN REQUIRED
        </h2>


        <p>
            Please log in to your JAC account before adding items to your cart.
        </p>


        <div class="login-popup-actions">

            <a
                href="../login.php"
                class="login-popup-login"
            >
                LOGIN
            </a>


            <button
                type="button"
                class="login-popup-cancel"
            >
                CANCEL
            </button>

        </div>

    </div>

</div>


<!-- PRODUCT DATABASE DATA -->

<script>

    window.JAC_IS_LOGGED_IN =
        <?= $is_logged_in ? 'true' : 'false' ?>;

    window.JAC_PRODUCT_ID =
        <?= $product_id ?>;

    window.JAC_PRODUCT_VARIANTS =
        <?= json_encode(
            $variant_data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ) ?>;

</script>

<script src="../script.js?v=2"></script>

</body>

</html>