<?php



session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET CUSTOMERS
|--------------------------------------------------------------------------
*/

$customers = [];

$stmt = $conn->prepare("
    SELECT
        id,
        first_name,
        last_name,
        email,
        phone,
        address,
        city,
        province,
        postal_code,
        created_at
    FROM users
    ORDER BY created_at DESC
");

if (!$stmt) {
    die("Unable to prepare customer request.");
}

if (!$stmt->execute()) {
    die("Unable to load customers.");
}

$result = $stmt->get_result();

$customers = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Customers | JAC Admin</title>

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

        /* CUSTOMER TABLE */

        .customer-box {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 30px;

            overflow-x: auto;
        }

        .customer-box h3 {
            font-size: 18px;

            font-weight: 500;

            margin-bottom: 25px;
        }

        .customer-table {
            width: 100%;

            border-collapse: collapse;

            min-width: 900px;
        }

        .customer-table th {
            padding: 14px;

            background: #111;

            color: white;

            text-align: left;

            font-size: 12px;

            letter-spacing: 1px;

            font-weight: normal;
        }

        .customer-table td {
            padding: 16px 14px;

            border-bottom: 1px solid #e5e5e5;

            font-size: 13px;

            vertical-align: middle;
        }

        .customer-table tbody tr:hover {
            background: #fafafa;
        }

        .customer-name {
            font-weight: bold;
        }

        .customer-email {
            color: #555;
        }

        .customer-location {
            line-height: 1.5;

            color: #555;
        }

        .empty-message {
            padding: 40px 20px;

            text-align: center;

            color: #777;

            font-size: 14px;
        }

        /* RESPONSIVE */

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

            .topbar {
                gap: 20px;

                align-items: flex-start;

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

        <a href="variants.php">
            INVENTORY
        </a>

        <a href="orders.php">
            ORDERS
        </a>

        <a
            href="customers.php"
            class="active"
        >
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
                Customers
            </h2>

            <div class="admin-name">

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION["admin_first_name"]
                    ?? "Admin"
                ); ?>

            </div>

        </div>


        <!-- CUSTOMER LIST -->

        <section class="customer-box">

            <h3>
                REGISTERED CUSTOMERS
            </h3>

            <?php if (empty($customers)): ?>

                <div class="empty-message">

                    No customers have registered yet.

                </div>

            <?php else: ?>

                <table class="customer-table">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                CUSTOMER
                            </th>

                            <th>
                                E-MAIL
                            </th>

                            <th>
                                PHONE
                            </th>

                            <th>
                                LOCATION
                            </th>

                            <th>
                                REGISTERED
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($customers as $customer): ?>

                            <tr>

                                <td>
                                    #<?= (int) $customer["id"]; ?>
                                </td>

                                <td>

                                    <div class="customer-name">

                                        <?= htmlspecialchars(
                                            $customer["first_name"] .
                                            " " .
                                            $customer["last_name"]
                                        ); ?>

                                    </div>

                                </td>

                                <td>

                                    <div class="customer-email">

                                        <?= htmlspecialchars(
                                            $customer["email"]
                                        ); ?>

                                    </div>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $customer["phone"]
                                        ?? "—"
                                    ); ?>

                                </td>

                                <td>

                                    <div class="customer-location">

                                        <?= htmlspecialchars(
                                            $customer["city"]
                                            ?? ""
                                        ); ?>

                                        <?php if (
                                            !empty(
                                                $customer["province"]
                                            )
                                        ): ?>

                                            <br>

                                            <?= htmlspecialchars(
                                                $customer["province"]
                                            ); ?>

                                        <?php endif; ?>

                                    </div>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        date(
                                            "M j, Y",
                                            strtotime(
                                                $customer["created_at"]
                                            )
                                        )
                                    ); ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>