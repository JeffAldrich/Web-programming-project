<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$product_count = 0;
$customer_count = 0;
$order_count = 0;
$revenue = 0;

/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM products
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $product_count = (int) $row["total"];
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| TOTAL CUSTOMERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'customer' OR role IS NULL
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $customer_count = (int) $row["total"];
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| TOTAL ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $order_count = (int) $row["total"];
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| TOTAL REVENUE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $revenue = (float) $row["total"];
    }

    $stmt->close();
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

    <title>JAC Admin Dashboard</title>

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

            margin-bottom: 40px;
        }

        .topbar h2 {
            font-size: 28px;
            font-weight: 500;
        }

        .admin-name {
            font-size: 13px;
            color: #666;
        }

        /* STAT CARDS */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 40px;
        }

        .stat-card {
            background: white;

            padding: 28px;

            border: 1px solid #e5e5e5;
        }

        .stat-card h3 {
            font-size: 12px;

            font-weight: normal;

            letter-spacing: 1.5px;

            color: #777;

            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 32px;

            font-weight: 500;
        }

        /* CONTENT */

        .content-box {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 30px;
        }

        .content-box h3 {
            font-size: 18px;

            font-weight: 500;

            margin-bottom: 10px;
        }

        .content-box p {
            color: #777;

            font-size: 14px;

            line-height: 1.6;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {

            .stats {
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

            .stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h1>JAC</h1>

        <a
            href="dashboard.php"
            class="active"
        >
            DASHBOARD
        </a>

        <a href="products.php">
            PRODUCTS
        </a>

        <a href="inventory.php">
            INVENTORY
        </a>

        <a href="orders.php">
            ORDERS
        </a>

        <a href="customers.php">
            CUSTOMERS
        </a>

        <a href="categories.php">
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
                Dashboard
            </h2>

            <div class="admin-name">

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION["admin_first_name"]
                    ?? "Admin"
                ); ?>

            </div>

        </div>


        <!-- STATISTICS -->

        <section class="stats">

            <div class="stat-card">

                <h3>
                    PRODUCTS
                </h3>

                <div class="number">
                    <?= $product_count; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>
                    CUSTOMERS
                </h3>

                <div class="number">
                    <?= $customer_count; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>
                    ORDERS
                </h3>

                <div class="number">
                    <?= $order_count; ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>
                    REVENUE
                </h3>

                <div class="number">
                    ₱<?= number_format(
                        $revenue,
                        2
                    ); ?>
                </div>

            </div>

        </section>


        <!-- DASHBOARD CONTENT -->

        <section class="content-box">

            <h3>
                Welcome to JAC Admin
            </h3>

            <p>
                Manage your products, inventory,
                customers, orders, and other
                store information from the
                administration panel.
            </p>

        </section>

    </main>

</body>

</html>