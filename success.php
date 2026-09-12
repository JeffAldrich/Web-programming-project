<?php

session_start();

require_once "php/db.php";

$order_id = filter_input(
    INPUT_GET,
    "order_id",
    FILTER_VALIDATE_INT
);

$user_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


/* =========================================================
   ORDER SUCCESS
========================================================= */

if ($order_id) {

    if (!isset($_SESSION["user_id"])) {

        header("Location: login.php");
        exit;

    }

    $session_user_id = (int) $_SESSION["user_id"];


    /* =====================================================
       GET ORDER
    ===================================================== */

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            total_amount,
            status,
            shipping_name,
            shipping_email,
            shipping_phone,
            shipping_address,
            shipping_city,
            shipping_province,
            shipping_postal_code,
            created_at

        FROM orders

        WHERE id = ?
        AND user_id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $order_id,
        $session_user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $order = $result->fetch_assoc();

    $stmt->close();


    if (!$order) {

        header("Location: index.php");
        exit;

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

    <title>JAC - Order Successful</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>

<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header>

    <img
        src="Images/Final LOGO.svg"
        alt="JAC Logo"
        class="logo"
    >


    <nav>

        <a href="index.php#home">
            HOME
        </a>

        <a href="index.php#about">
            ABOUT US
        </a>

        <a href="index.php#collections">
            COLLECTION
        </a>

        <a href="store.php">
            STORE
        </a>

        <a href="index.php#contact">
            CONTACT
        </a>

    </nav>


    <!-- ACCOUNT MENU -->

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

            <a href="/JAC/user%20details/my%20order.php">
                MY ORDERS
            </a>

            <a href="/JAC/user%20details/my%20cart.php">
                MY CART
            </a>

            <a href="/JAC/user%20details/wishlist.php">
                WISHLIST
            </a>


            <div class="account-divider"></div>


            <a
                href="logout.php"
                class="logout"
            >
                LOG OUT
            </a>

        </div>

    </div>

</header>



<!-- =====================================================
     SUCCESS PAGE
===================================================== -->

<section class="success-page">

    <div class="success-container">


        <div class="success-icon">
            ✓
        </div>


        <p class="success-label">
            THANK YOU FOR SHOPPING WITH JAC
        </p>


        <h1>
            ORDER<br>
            SUCCESSFUL
        </h1>


        <p class="success-message">

            Thank you,
            <?= htmlspecialchars(
                $order["shipping_name"]
            ); ?>.

            Your order has been successfully placed.

        </p>



        <!-- ORDER DETAILS -->

        <div class="success-details">

            <h2>
                ORDER DETAILS
            </h2>


            <div class="success-detail-row">

                <span>
                    ORDER NUMBER
                </span>

                <strong>
                    #<?= (int) $order["id"]; ?>
                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    TOTAL
                </span>

                <strong>
                    ₱<?= number_format(
                        (float) $order["total_amount"],
                        2
                    ); ?>
                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    PAYMENT
                </span>

                <strong>
                    Cash on Delivery
                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    STATUS
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["status"]
                    ); ?>
                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    E-MAIL
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_email"]
                    ); ?>
                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    DELIVERY ADDRESS
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $order["shipping_address"]
                    ); ?>


                    <?php if (
                        !empty(
                            $order["shipping_city"]
                        )
                    ): ?>

                        ,
                        <?= htmlspecialchars(
                            $order["shipping_city"]
                        ); ?>

                    <?php endif; ?>


                    <?php if (
                        !empty(
                            $order["shipping_province"]
                        )
                    ): ?>

                        ,
                        <?= htmlspecialchars(
                            $order["shipping_province"]
                        ); ?>

                    <?php endif; ?>

                </strong>

            </div>

        </div>



        <!-- ACTION BUTTONS -->

        <div class="success-actions">

            <a href="index.php">
                BACK TO HOME
            </a>

            <a href="store.php">
                CONTINUE SHOPPING
            </a>

        </div>


    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    <div class="footer-bottom">

        <p>
            © 2026 All Rights Reserved.
        </p>

    </div>

</footer>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="script.js"></script>


</body>

</html>

<?php

exit;

}



/* =========================================================
   REGISTRATION SUCCESS
========================================================= */

if ($user_id) {

    $stmt = $conn->prepare("
        SELECT
            id,
            first_name,
            last_name,
            email

        FROM users

        WHERE id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();


    if (!$user) {

        header("Location: index.php");
        exit;

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

    <title>JAC - Registration Successful</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>

<body>


<header>

    <img
        src="Images/Final LOGO.svg"
        alt="JAC Logo"
        class="logo"
    >


    <nav>

        <a href="index.php#home">
            HOME
        </a>

        <a href="index.php#about">
            ABOUT US
        </a>

        <a href="index.php#collections">
            COLLECTION
        </a>

        <a href="store.php">
            STORE
        </a>

        <a href="index.php#contact">
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

            <a href="/JAC/user%20details/my%20order.php">
                MY ORDERS
            </a>

            <a href="/JAC/user%20details/my%20cart.php">
                MY CART
            </a>

            <a href="/JAC/user%20details/wishlist.php">
                WISHLIST
            </a>


            <div class="account-divider"></div>


            <a
                href="logout.php"
                class="logout"
            >
                LOG OUT
            </a>

        </div>

    </div>

</header>



<section class="success-page">

    <div class="success-container">

        <div class="success-icon">
            ✓
        </div>


        <p class="success-label">
            WELCOME TO JAC
        </p>


        <h1>
            REGISTRATION<br>
            SUCCESSFUL
        </h1>


        <p class="success-message">

            Your JAC account has been successfully
            created.

        </p>


        <div class="success-details">

            <h2>
                ACCOUNT DETAILS
            </h2>


            <div class="success-detail-row">

                <span>
                    NAME
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $user["first_name"] . " " . $user["last_name"]
                    ); ?>

                </strong>

            </div>


            <div class="success-detail-row">

                <span>
                    E-MAIL
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $user["email"]
                    ); ?>
                </strong>

            </div>

        </div>


        <div class="success-actions">

            <a href="index.php">
                BACK TO HOME
            </a>

            <a href="store.php">
                CONTINUE SHOPPING
            </a>

        </div>

    </div>

</section>



<footer>

    <div class="footer-bottom">

        <p>
            © 2026 All Rights Reserved.
        </p>

    </div>

</footer>



<script src="script.js"></script>


</body>

</html>

<?php

exit;

}



/* =========================================================
   NO VALID PARAMETER
========================================================= */

header("Location: index.php");

exit;

?>