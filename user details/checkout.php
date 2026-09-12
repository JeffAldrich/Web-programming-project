<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?required=checkout");
    exit;
}

$user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| LOAD USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        first_name,
        last_name,
        email,
        phone,
        address,
        city,
        province,
        postal_code
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| LOAD CART
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$cart = $result->fetch_assoc();

$stmt->close();

$cart_items = [];

if ($cart) {

    $cart_id = (int) $cart["id"];

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

    $stmt->bind_param("i", $cart_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| CALCULATE DISPLAY TOTAL
|--------------------------------------------------------------------------
*/

$cart_total = 0;

foreach ($cart_items as $item) {

    $cart_total +=
        (float) $item["price"] *
        (int) $item["quantity"];

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

    <title>Checkout | JAC</title>

    <link
        rel="stylesheet"
        href="../style.css"
    >

</head>

<body>


<!-- HEADER -->

<header>

    <nav>

        <a href="../index.php">
            HOME
        </a>

        <a href="../index.php#about">
            ABOUT US
        </a>

        <a href="../store.php">
            COLLECTION
        </a>

        <a href="../index.php#contact">
            CONTACT
        </a>

    </nav>

</header>


<!-- CHECKOUT -->

<section class="checkout-page">

    <div class="checkout-container">

        <h1>CHECKOUT</h1>


        <?php if (empty($cart_items)): ?>

            <div class="checkout-empty">

                <h2>YOUR CART IS EMPTY</h2>

                <p>
                    Please add products to your cart
                    before checking out.
                </p>

                <a href="../store.php">
                    SHOP NOW
                </a>

            </div>

        <?php else: ?>


            <!-- CUSTOMER INFORMATION -->

            <div class="checkout-section">

                <h2>
                    CUSTOMER INFORMATION
                </h2>

                <div class="checkout-form">

                    <div class="checkout-field">

                        <label for="checkout-name">
                            FULL NAME
                        </label>

                        <input
                            type="text"
                            id="checkout-name"
                            name="shipping_name"
                            value="<?= htmlspecialchars(
                                ($user["first_name"] ?? "") .
                                " " .
                                ($user["last_name"] ?? "")
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-email">
                            E-MAIL
                        </label>

                        <input
                            type="email"
                            id="checkout-email"
                            name="shipping_email"
                            value="<?= htmlspecialchars(
                                $user["email"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-phone">
                            PHONE
                        </label>

                        <input
                            type="text"
                            id="checkout-phone"
                            name="shipping_phone"
                            value="<?= htmlspecialchars(
                                $user["phone"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-address">
                            ADDRESS
                        </label>

                        <input
                            type="text"
                            id="checkout-address"
                            name="shipping_address"
                            value="<?= htmlspecialchars(
                                $user["address"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-city">
                            CITY
                        </label>

                        <input
                            type="text"
                            id="checkout-city"
                            name="shipping_city"
                            value="<?= htmlspecialchars(
                                $user["city"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-province">
                            PROVINCE
                        </label>

                        <input
                            type="text"
                            id="checkout-province"
                            name="shipping_province"
                            value="<?= htmlspecialchars(
                                $user["province"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="checkout-postal-code">
                            POSTAL CODE
                        </label>

                        <input
                            type="text"
                            id="checkout-postal-code"
                            name="shipping_postal_code"
                            value="<?= htmlspecialchars(
                                $user["postal_code"] ?? ""
                            ); ?>"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- ORDER ITEMS -->

            <div class="checkout-section">

                <h2>
                    YOUR ORDER
                </h2>


                <div class="checkout-items">

                    <?php foreach ($cart_items as $item): ?>

                        <?php

                        $item_total =
                            (float) $item["price"] *
                            (int) $item["quantity"];

                        $image =
                            "../Images/" .
                            $item["image"];

                        ?>

                        <div class="checkout-item">

                            <div class="checkout-item-image">

                                <img
                                    src="<?= htmlspecialchars(
                                        $image
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $item["name"]
                                    ); ?>"
                                >

                            </div>


                            <div class="checkout-item-details">

                                <h3>
                                    <?= htmlspecialchars(
                                        $item["name"]
                                    ); ?>
                                </h3>

                                <p>
                                    PRICE:
                                    ₱<?= number_format(
                                        (float) $item["price"],
                                        2
                                    ); ?>
                                </p>

                                <p>
                                    SIZE:
                                    <?= htmlspecialchars(
                                        $item["size"]
                                    ); ?>
                                </p>

                                <p>
                                    COLOR:
                                    <?= htmlspecialchars(
                                        $item["color"]
                                    ); ?>
                                </p>

                                <p>
                                    QUANTITY:
                                    <?= (int) $item["quantity"]; ?>
                                </p>

                            </div>


                            <div class="checkout-item-price">

                                ₱<?= number_format(
                                    $item_total,
                                    2
                                ); ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- TOTAL -->

                <div class="checkout-total">

                    <span>
                        TOTAL
                    </span>

                    <strong>
                        ₱<?= number_format(
                            $cart_total,
                            2
                        ); ?>
                    </strong>

                </div>

            </div>


            <!-- PAYMENT -->

            <div class="checkout-section">

                <h2>
                    PAYMENT METHOD
                </h2>

                <div class="payment-options">

                    <label>

                        <input
                            type="radio"
                            name="payment"
                            value="Cash on Delivery"
                            checked
                        >

                        Cash on Delivery

                    </label>

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="checkout-actions">

                <a
                    href="my%20cart.php"
                    class="back-to-cart"
                >
                    BACK TO CART
                </a>


                <form
                    action="../php/place_order.php"
                    method="POST"
                    id="place-order-form"
                >

                    <input
                        type="hidden"
                        name="shipping_name"
                        id="form-shipping-name"
                    >

                    <input
                        type="hidden"
                        name="shipping_email"
                        id="form-shipping-email"
                    >

                    <input
                        type="hidden"
                        name="shipping_phone"
                        id="form-shipping-phone"
                    >

                    <input
                        type="hidden"
                        name="shipping_address"
                        id="form-shipping-address"
                    >

                    <input
                        type="hidden"
                        name="shipping_city"
                        id="form-shipping-city"
                    >

                    <input
                        type="hidden"
                        name="shipping_province"
                        id="form-shipping-province"
                    >

                    <input
                        type="hidden"
                        name="shipping_postal_code"
                        id="form-shipping-postal-code"
                    >

                    <input
                        type="hidden"
                        name="payment_method"
                        id="form-payment-method"
                        value="Cash on Delivery"
                    >

                    <button
                        type="submit"
                        class="place-order-button"
                    >
                        PLACE ORDER
                    </button>

                </form>

            </div>


        <?php endif; ?>

    </div>

</section>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const form =
            document.getElementById(
                "place-order-form"
            );

        if (!form) {
            return;
        }


        form.addEventListener(
            "submit",
            function (event) {

                const name =
                    document
                        .getElementById(
                            "checkout-name"
                        )
                        .value
                        .trim();

                const email =
                    document
                        .getElementById(
                            "checkout-email"
                        )
                        .value
                        .trim();

                const phone =
                    document
                        .getElementById(
                            "checkout-phone"
                        )
                        .value
                        .trim();

                const address =
                    document
                        .getElementById(
                            "checkout-address"
                        )
                        .value
                        .trim();

                const city =
                    document
                        .getElementById(
                            "checkout-city"
                        )
                        .value
                        .trim();

                const province =
                    document
                        .getElementById(
                            "checkout-province"
                        )
                        .value
                        .trim();

                const postalCode =
                    document
                        .getElementById(
                            "checkout-postal-code"
                        )
                        .value
                        .trim();


                /*
                |--------------------------------------------------------------------------
                | VALIDATE
                |--------------------------------------------------------------------------
                */

                if (
                    !name ||
                    !email ||
                    !phone ||
                    !address ||
                    !city ||
                    !province ||
                    !postalCode
                ) {

                    event.preventDefault();

                    alert(
                        "Please complete all shipping information."
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | COPY FORM VALUES
                |--------------------------------------------------------------------------
                */

                document
                    .getElementById(
                        "form-shipping-name"
                    )
                    .value = name;

                document
                    .getElementById(
                        "form-shipping-email"
                    )
                    .value = email;

                document
                    .getElementById(
                        "form-shipping-phone"
                    )
                    .value = phone;

                document
                    .getElementById(
                        "form-shipping-address"
                    )
                    .value = address;

                document
                    .getElementById(
                        "form-shipping-city"
                    )
                    .value = city;

                document
                    .getElementById(
                        "form-shipping-province"
                    )
                    .value = province;

                document
                    .getElementById(
                        "form-shipping-postal-code"
                    )
                    .value = postalCode;

            }
        );

    }
);

</script>


</body>

</html>