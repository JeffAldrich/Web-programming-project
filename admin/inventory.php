<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET INVENTORY
|--------------------------------------------------------------------------
*/

$inventory = [];

$stmt = $conn->prepare("
    SELECT
        product_variants.id,
        product_variants.product_id,
        product_variants.size,
        product_variants.color,
        product_variants.stock,
        products.name AS product_name,
        products.image AS product_image
    FROM product_variants

    INNER JOIN products
        ON product_variants.product_id = products.id

    ORDER BY
        products.name ASC,
        product_variants.color ASC,
        product_variants.size ASC
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    $inventory = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| INVENTORY COUNTS
|--------------------------------------------------------------------------
*/

$total_variants = count($inventory);

$low_stock = 0;
$out_of_stock = 0;
$available_stock = 0;

foreach ($inventory as $item) {

    $stock = (int) $item["stock"];

    $available_stock += $stock;

    if ($stock <= 0) {
        $out_of_stock++;
    } elseif ($stock <= 5) {
        $low_stock++;
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

    <title>JAC Admin - Inventory</title>

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
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }

        .summary-card {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 25px;
        }

        .summary-card h3 {
            font-size: 11px;

            font-weight: normal;

            letter-spacing: 1.5px;

            color: #777;

            margin-bottom: 12px;
        }

        .summary-number {
            font-size: 28px;

            font-weight: 500;
        }

        /* INVENTORY BOX */

        .inventory-box {
            background: white;

            border: 1px solid #e5e5e5;

            padding: 30px;
        }

        .inventory-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;
        }

        .inventory-header h3 {
            font-size: 18px;

            font-weight: 500;
        }

        .add-variant {
            display: inline-block;

            padding: 11px 18px;

            background: black;
            color: white;

            text-decoration: none;

            font-size: 11px;

            letter-spacing: 1px;

            transition: 0.2s ease;
        }

        .add-variant:hover {
            background: #333;
        }

        /* TABLE */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
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
            padding: 15px 12px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* PRODUCT */

        .product-info {
            display: flex;

            align-items: center;

            gap: 12px;
        }

        .product-image {
            width: 50px;
            height: 60px;

            object-fit: cover;

            background: #eee;
        }

        .product-name {
            font-size: 13px;

            font-weight: 500;
        }

        /* COLOR */

        .color-info {
            display: flex;

            align-items: center;

            gap: 8px;
        }

        .color-dot {
            width: 15px;
            height: 15px;

            border-radius: 50%;

            border: 1px solid #ccc;
        }

        .color-black {
            background: black;
        }

        .color-white {
            background: white;
        }

        .color-navy {
            background: #172554;
        }

        .color-gray {
            background: #808080;
        }

        /* STOCK */

        .stock-number {
            font-weight: 500;
        }

        .stock-status {
            display: inline-block;

            padding: 5px 9px;

            font-size: 10px;

            letter-spacing: 0.5px;
        }

        .status-good {
            background: #e8f5e9;

            color: #216b2a;
        }

        .status-low {
            background: #fff4d6;

            color: #8a6500;
        }

        .status-out {
            background: #fbe5e5;

            color: #a22;
        }

        /* ACTION */

        .edit-button {
            display: inline-block;

            padding: 7px 12px;

            border: 1px solid #222;

            color: #111;

            text-decoration: none;

            font-size: 10px;

            letter-spacing: 0.7px;

            transition: 0.2s ease;
        }

        .edit-button:hover {
            background: black;

            color: white;
        }

        /* EMPTY */

        .empty {
            padding: 50px 20px;

            text-align: center;

            color: #777;

            font-size: 14px;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {

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

        <a
            href="inventory.php"
            class="active"
        >
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
                Inventory
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
                    TOTAL VARIANTS
                </h3>

                <div class="summary-number">
                    <?= $total_variants; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    LOW STOCK
                </h3>

                <div class="summary-number">
                    <?= $low_stock; ?>
                </div>

            </div>


            <div class="summary-card">

                <h3>
                    OUT OF STOCK
                </h3>

                <div class="summary-number">
                    <?= $out_of_stock; ?>
                </div>

            </div>

        </section>


        <!-- INVENTORY -->

        <section class="inventory-box">

            <div class="inventory-header">

                <h3>
                    Product Inventory
                </h3>

                <a
                    href="add_variant.php"
                    class="add-variant"
                >
                    + ADD VARIANT
                </a>

            </div>


            <?php if (empty($inventory)): ?>

                <div class="empty">

                    No product variants found.

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table>

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
                                    STOCK
                                </th>

                                <th>
                                    STATUS
                                </th>

                                <th>
                                    ACTION
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach (
                                $inventory
                                as $item
                            ): ?>

                                <?php

                                $stock =
                                    (int)
                                    $item["stock"];

                                if ($stock <= 0) {

                                    $status =
                                        "OUT OF STOCK";

                                    $status_class =
                                        "status-out";

                                } elseif ($stock <= 5) {

                                    $status =
                                        "LOW STOCK";

                                    $status_class =
                                        "status-low";

                                } else {

                                    $status =
                                        "IN STOCK";

                                    $status_class =
                                        "status-good";
                                }

                                $color =
                                    strtolower(
                                        trim(
                                            $item["color"]
                                        )
                                    );

                                $color_class =
                                    "color-" .
                                    $color;

                                ?>

                                <tr>

                                    <!-- PRODUCT -->

                                    <td>

                                        <div
                                            class="product-info"
                                        >

                                            <?php if (
                                                !empty(
                                                    $item["product_image"]
                                                )
                                            ): ?>

                                                <img
                                                    src="../Images/<?= htmlspecialchars(
                                                        $item["product_image"]
                                                    ); ?>"
                                                    alt="<?= htmlspecialchars(
                                                        $item["product_name"]
                                                    ); ?>"
                                                    class="product-image"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="product-image"
                                                ></div>

                                            <?php endif; ?>


                                            <span
                                                class="product-name"
                                            >
                                                <?= htmlspecialchars(
                                                    $item["product_name"]
                                                ); ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- SIZE -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $item["size"]
                                        ); ?>

                                    </td>


                                    <!-- COLOR -->

                                    <td>

                                        <div
                                            class="color-info"
                                        >

                                            <span
                                                class="color-dot <?= htmlspecialchars(
                                                    $color_class
                                                ); ?>"
                                            ></span>

                                            <?= htmlspecialchars(
                                                $item["color"]
                                            ); ?>

                                        </div>

                                    </td>


                                    <!-- STOCK -->

                                    <td>

                                        <span
                                            class="stock-number"
                                        >
                                            <?= $stock; ?>
                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="stock-status <?= $status_class; ?>"
                                        >
                                            <?= $status; ?>
                                        </span>

                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <a
                                            href="edit_variant.php?id=<?= (int) $item["id"]; ?>"
                                            class="edit-button"
                                        >
                                            EDIT
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