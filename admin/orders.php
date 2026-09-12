<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET ORDERS
|--------------------------------------------------------------------------
*/

$orders = [];

$stmt = $conn->prepare("
    SELECT
        orders.id,
        orders.user_id,
        orders.total_amount,
        orders.status,
        orders.shipping_name,
        orders.shipping_email,
        orders.created_at
    FROM orders
    ORDER BY orders.created_at DESC
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    $orders = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| ORDER COUNTS
|--------------------------------------------------------------------------
*/

$total_orders = count($orders);

$pending_orders = 0;
$processing_orders = 0;
$shipped_orders = 0;
$delivered_orders = 0;
$cancelled_orders = 0;

foreach ($orders as $order) {

    switch ($order["status"]) {

        case "Pending":
            $pending_orders++;
            break;

        case "Processing":
            $processing_orders++;
            break;

        case "Shipped":
            $shipped_orders++;
            break;

        case "Delivered":
            $delivered_orders++;
            break;

        case "Cancelled":
            $cancelled_orders++;
            break;
    }
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

    <title>JAC Admin - Orders</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f4f4;
            color: #111;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;

            left: 0;
            top: 0;

            width: 240px;
            height: 100vh;

            background: #111;
            color: white;

            padding: 30px 20px;
        }

        .sidebar h1 {
            text-align: center;

            font-size: 24px;
            letter-spacing: 4px;

            margin-bottom: 45px;
        }

        .sidebar a {
            display: block;

            padding: 14px 18px;

            margin-bottom: 8px;

            color: white;
            text-decoration: none;

            font-size: 13px;
            letter-spacing: 1px;

            transition: 0.2s ease;
        }

        .sidebar a:hover {
            background: #333;
        }

        .sidebar a.active {
            background: white;
            color: black;
        }

        .logout {
            position: absolute;

            bottom: 30px;

            left: 20px;
            right: 20px;

            text-align: center;

            border: 1px solid #555;
        }

        .logout:hover {
            background: #222 !important;
        }

        /* MAIN */

        .main {
            margin-left: 240px;

            min-height: 100vh;

            padding: 40px;
        }

        .topbar {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 35px;
        }

        .topbar h2 {
            font-size: 28px;

            font-weight: 500;
        }

        .admin-name {
            font-size: 13px;

            color: #666;
        }

        /* SUMMARY */

        .summary {
            display: grid;

            grid-template-columns:
                repeat(5, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }

        .summary-card {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 20px;
        }

        .summary-card h3 {
            font-size: 10px;

            font-weight: normal;

            letter-spacing: 1.3px;

            color: #777;

            margin-bottom: 10px;
        }

        .summary-number {
            font-size: 25px;

            font-weight: 500;
        }

        /* ORDERS BOX */

        .orders-box {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 30px;
        }

        .orders-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;
        }

        .orders-header h3 {
            font-size: 18px;

            font-weight: 500;
        }

        /* TABLE */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }

        th {
            padding: 15px 12px;

            text-align: left;

            background: #f7f7f7;

            border-bottom: 1px solid #ddd;

            font-size: 10px;

            font-weight: 500;

            letter-spacing: 1.2px;
        }

        td {
            padding: 16px 12px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* ORDER ID */

        .order-id {
            font-weight: 500;
        }

        /* CUSTOMER */

        .customer-name {
            font-weight: 500;

            margin-bottom: 4px;
        }

        .customer-email {
            color: #777;

            font-size: 11px;
        }

        /* TOTAL */

        .order-total {
            font-weight: 500;
        }

        /* STATUS */

        .status {
            display: inline-block;

            padding: 6px 10px;

            font-size: 10px;

            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff4d6;

            color: #8a6500;
        }

        .status-processing {
            background: #e7f0ff;

            color: #285ca8;
        }

        .status-shipped {
            background: #e9e5ff;

            color: #5541a8;
        }

        .status-delivered {
            background: #e8f5e9;

            color: #216b2a;
        }

        .status-cancelled {
            background: #fbe5e5;

            color: #a22;
        }

        /* DATE */

        .order-date {
            color: #666;

            font-size: 12px;
        }

        /* ACTION */

        .view-button {
            display: inline-block;

            padding: 7px 12px;

            border: 1px solid #222;

            color: #111;

            text-decoration: none;

            font-size: 10px;

            letter-spacing: 0.7px;

            transition: 0.2s ease;
        }

        .view-button:hover {
            background: black;

            color: white;
        }

        /* EMPTY */

        .empty {
            padding: 60px 20px;

            text-align: center;

            color: #777;

            font-size: 14px;
        }

        /* RESPONSIVE */

        @media (max-width: 1100px) {

            .summary {
                grid-template-columns:
                    repeat(3, 1fr);
            }

        }

        @media (max-width: 800px) {

            .summary {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 700px) {

            .sidebar {
                position: relative;

                width: 100%;
                height: auto;
            }

            .sidebar h1 {
                margin-bottom: 20px;
            }

            .logout {
                position: static;

                margin-top: 20px;
            }

            .main {
                margin-left: 0;

                padding: 25px;
            }

            .summary {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;

                gap: 15px;

                flex-direction: column;
            }

        }

    </style>

</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h1>JAC</h1>

        <a href="dashboard.php">
            DASHBOARD
        </a>

        <a href="products.php">
            PRODUCTS
        </a>

        <a href="inventory.php">
            INVENTORY
        </a>

        <a
            href="orders.php"
            class="active"
        >
            ORDERS
        </a>

        <a href="#">
            CUSTOMERS
        </a>

        <a href="#">
            CATEGORIES
        </a>

        <a
            href="logout.php"
            class="logout"
        >
            LOGOUT
        </a>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <div class="topbar">

            <h2>
                Orders
            </h2>

            <div class="admin-name">

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION["admin_first_name"]
                    ?? "Admin"
                ); ?>

            </div>

        </div>


        <!-- SUMMARY -->

        <section class="summary">

            <div class="summary-card">

                <h3>
                    TOTAL
                </h3>

                <div class="summary-number">
                    <?= $total_orders; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    PENDING
                </h3>

                <div class="summary-number">
                    <?= $pending_orders; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    PROCESSING
                </h3>

                <div class="summary-number">
                    <?= $processing_orders; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    SHIPPED
                </h3>

                <div class="summary-number">
                    <?= $shipped_orders; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    DELIVERED
                </h3>

                <div class="summary-number">
                    <?= $delivered_orders; ?>
                </div>

            </div>

        </section>


        <!-- ORDERS -->

        <section class="orders-box">

            <div class="orders-header">

                <h3>
                    All Orders
                </h3>

            </div>


            <?php if (empty($orders)): ?>

                <div class="empty">

                    No orders have been placed yet.

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    ORDER
                                </th>

                                <th>
                                    CUSTOMER
                                </th>

                                <th>
                                    TOTAL
                                </th>

                                <th>
                                    STATUS
                                </th>

                                <th>
                                    DATE
                                </th>

                                <th>
                                    ACTION
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach (
                                $orders
                                as $order
                            ): ?>

                                <?php

                                $status =
                                    $order["status"];

                                $status_class =
                                    "status-" .
                                    strtolower(
                                        $status
                                    );

                                ?>

                                <tr>

                                    <!-- ORDER -->

                                    <td>

                                        <span
                                            class="order-id"
                                        >
                                            #<?= (int) $order["id"]; ?>
                                        </span>

                                    </td>


                                    <!-- CUSTOMER -->

                                    <td>

                                        <div
                                            class="customer-name"
                                        >
                                            <?= htmlspecialchars(
                                                $order["shipping_name"]
                                            ); ?>
                                        </div>

                                        <div
                                            class="customer-email"
                                        >
                                            <?= htmlspecialchars(
                                                $order["shipping_email"]
                                            ); ?>
                                        </div>

                                    </td>


                                    <!-- TOTAL -->

                                    <td>

                                        <span
                                            class="order-total"
                                        >
                                            ₱<?= number_format(
                                                (float) $order["total_amount"],
                                                2
                                            ); ?>
                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="status <?= htmlspecialchars(
                                                $status_class
                                            ); ?>"
                                        >
                                            <?= htmlspecialchars(
                                                strtoupper(
                                                    $status
                                                )
                                            ); ?>
                                        </span>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <span
                                            class="order-date"
                                        >
                                            <?= date(
                                                "M d, Y h:i A",
                                                strtotime(
                                                    $order["created_at"]
                                                )
                                            ); ?>
                                        </span>

                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <a
                                            href="order_details.php?id=<?= (int) $order["id"]; ?>"
                                            class="view-button"
                                        >
                                            VIEW
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>