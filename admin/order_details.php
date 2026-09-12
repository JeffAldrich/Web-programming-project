<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($order_id <= 0) {
    header("Location: orders.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET ORDER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        orders.id,
        orders.user_id,
        orders.total_amount,
        orders.status,
        orders.shipping_name,
        orders.shipping_email,
        orders.shipping_phone,
        orders.shipping_address,
        orders.shipping_city,
        orders.shipping_province,
        orders.shipping_postal_code,
        orders.created_at
    FROM orders
    WHERE orders.id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare order request.");
}

$stmt->bind_param(
    "i",
    $order_id
);

$stmt->execute();

$result = $stmt->get_result();

$order = $result->fetch_assoc();

$stmt->close();

if (!$order) {
    header("Location: orders.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET ORDER ITEMS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        order_items.id,
        order_items.product_id,
        order_items.variant_id,
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

    WHERE order_items.order_id = ?

    ORDER BY order_items.id ASC
");

if (!$stmt) {
    die("Unable to prepare order items request.");
}

$stmt->bind_param(
    "i",
    $order_id
);

$stmt->execute();

$result = $stmt->get_result();

$order_items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

/*
|--------------------------------------------------------------------------
| CALCULATE ITEM TOTAL
|--------------------------------------------------------------------------
*/

$items_total = 0;

foreach ($order_items as $item) {

    $items_total +=
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

    <title>
        Order #<?= (int) $order["id"]; ?> | JAC Admin
    </title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
        }

        .admin-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .top-bar h1 {
            font-size: 28px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 18px;
            background: black;
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .back-button:hover {
            background: #333;
        }

        .success-message {
            margin-bottom: 25px;
            padding: 14px 18px;
            background: #e8f7e8;
            border: 1px solid #9ac89a;
            color: #245c24;
            font-size: 14px;
        }

        .error-message {
            margin-bottom: 25px;
            padding: 14px 18px;
            background: #fbeaea;
            border: 1px solid #d99;
            color: #8b2222;
            font-size: 14px;
        }

        .card {
            background: white;
            padding: 30px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }

        .card h2 {
            margin-bottom: 25px;
            font-size: 20px;
        }

        .order-header {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
        }

        .info-box {
            padding: 18px;
            background: #f7f7f7;
            border: 1px solid #ddd;
        }

        .info-box span {
            display: block;
            margin-bottom: 7px;
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
        }

        .info-box strong {
            font-size: 15px;
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-form select {
            min-width: 180px;
            padding: 11px 12px;
            border: 1px solid #aaa;
            background: white;
            font-size: 14px;
        }

        .status-form button {
            padding: 11px 20px;
            border: none;
            background: black;
            color: white;
            cursor: pointer;
            font-size: 13px;
        }

        .status-form button:hover {
            background: #333;
        }

        .customer-grid {
            display: grid;
            grid-template-columns:
                repeat(2, 1fr);
            gap: 18px;
        }

        .customer-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .customer-item span {
            display: block;
            margin-bottom: 6px;
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
        }

        .customer-item strong {
            font-size: 14px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            padding: 14px;
            background: #111;
            color: white;
            text-align: left;
            font-size: 12px;
        }

        .items-table td {
            padding: 15px 14px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            font-size: 14px;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-cell img {
            width: 70px;
            height: 80px;
            object-fit: cover;
            background: #eee;
        }

        .product-name {
            font-weight: bold;
        }

        .product-meta {
            margin-top: 5px;
            color: #777;
            font-size: 12px;
        }

        .order-total {
            display: flex;
            justify-content: flex-end;
            gap: 50px;
            margin-top: 25px;
            font-size: 18px;
        }

        .order-total strong {
            min-width: 130px;
            text-align: right;
        }

        @media (max-width: 800px) {

            .admin-page {
                padding: 20px;
            }

            .order-header {
                grid-template-columns: 1fr;
            }

            .customer-grid {
                grid-template-columns: 1fr;
            }

            .items-table {
                display: block;
                overflow-x: auto;
            }

            .top-bar {
                gap: 20px;
                align-items: flex-start;
                flex-direction: column;
            }

            .status-form {
                align-items: flex-start;
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<div class="admin-page">

    <div class="top-bar">

        <h1>
            ORDER #<?= (int) $order["id"]; ?>
        </h1>

        <a
            href="orders.php"
            class="back-button"
        >
            BACK TO ORDERS
        </a>

    </div>


    <?php if (isset($_GET["updated"])): ?>

        <div class="success-message">
            Order status updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["error"])): ?>

        <div class="error-message">
            Invalid order status.
        </div>

    <?php endif; ?>


    <!-- ORDER INFORMATION -->

    <div class="card">

        <h2>
            ORDER INFORMATION
        </h2>

        <div class="order-header">

            <div class="info-box">

                <span>
                    Order Number
                </span>

                <strong>
                    #<?= (int) $order["id"]; ?>
                </strong>

            </div>


            <div class="info-box">

                <span>
                    Date
                </span>

                <strong>
                    <?= htmlspecialchars(
                        date(
                            "F j, Y g:i A",
                            strtotime(
                                $order["created_at"]
                            )
                        )
                    ); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>
                    Current Status
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["status"]
                    ); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- UPDATE STATUS -->

    <div class="card">

        <h2>
            UPDATE ORDER STATUS
        </h2>

        <form
            action="update_order_status.php"
            method="POST"
            class="status-form"
        >

            <input
                type="hidden"
                name="order_id"
                value="<?= (int) $order["id"]; ?>"
            >

            <select name="status">

                <?php

                $statuses = [
                    "Pending",
                    "Processing",
                    "Shipped",
                    "Delivered",
                    "Cancelled"
                ];

                foreach ($statuses as $status):

                ?>

                    <option
                        value="<?= htmlspecialchars($status); ?>"
                        <?= $order["status"] === $status
                            ? "selected"
                            : ""; ?>
                    >
                        <?= htmlspecialchars($status); ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit">
                UPDATE STATUS
            </button>

        </form>

    </div>


    <!-- CUSTOMER INFORMATION -->

    <div class="card">

        <h2>
            CUSTOMER INFORMATION
        </h2>

        <div class="customer-grid">

            <div class="customer-item">

                <span>
                    Name
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_name"]
                    ); ?>
                </strong>

            </div>


            <div class="customer-item">

                <span>
                    E-mail
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_email"]
                    ); ?>
                </strong>

            </div>


            <div class="customer-item">

                <span>
                    Phone
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_phone"] ?? ""
                    ); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- SHIPPING INFORMATION -->

    <div class="card">

        <h2>
            SHIPPING INFORMATION
        </h2>

        <div class="customer-grid">

            <div class="customer-item">

                <span>
                    Address
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_address"]
                    ); ?>
                </strong>

            </div>


            <div class="customer-item">

                <span>
                    City
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_city"]
                    ); ?>
                </strong>

            </div>


            <div class="customer-item">

                <span>
                    Province
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_province"] ?? ""
                    ); ?>
                </strong>

            </div>


            <div class="customer-item">

                <span>
                    Postal Code
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $order["shipping_postal_code"] ?? ""
                    ); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- ORDER ITEMS -->

    <div class="card">

        <h2>
            ORDER ITEMS
        </h2>

        <table class="items-table">

            <thead>

                <tr>

                    <th>
                        PRODUCT
                    </th>

                    <th>
                        SIZE
                    </th>

                    <th>
                        COLOR
                    </th>

                    <th>
                        PRICE
                    </th>

                    <th>
                        QUANTITY
                    </th>

                    <th>
                        SUBTOTAL
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($order_items as $item): ?>

                    <?php

                    $subtotal =
                        (float) $item["price"] *
                        (int) $item["quantity"];

                    $image_path =
                        "../Images/" .
                        $item["product_image"];

                    ?>

                    <tr>

                        <td>

                            <div class="product-cell">

                                <img
                                    src="<?= htmlspecialchars(
                                        $image_path
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $item["product_name"]
                                    ); ?>"
                                >

                                <div>

                                    <div class="product-name">

                                        <?= htmlspecialchars(
                                            $item["product_name"]
                                        ); ?>

                                    </div>

                                    <div class="product-meta">

                                        Product ID:
                                        <?= (int) $item["product_id"]; ?>

                                    </div>

                                </div>

                            </div>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $item["size"] ?? "N/A"
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $item["color"] ?? "N/A"
                            ); ?>

                        </td>


                        <td>

                            ₱<?= number_format(
                                (float) $item["price"],
                                2
                            ); ?>

                        </td>


                        <td>

                            <?= (int) $item["quantity"]; ?>

                        </td>


                        <td>

                            ₱<?= number_format(
                                $subtotal,
                                2
                            ); ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>


        <div class="order-total">

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

    </div>

</div>

</body>

</html>