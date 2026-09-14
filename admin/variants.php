<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET ALL VARIANTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        product_variants.id,
        product_variants.product_id,
        product_variants.size,
        product_variants.color,
        product_variants.stock,

        products.name AS product_name

    FROM product_variants

    INNER JOIN products
        ON product_variants.product_id = products.id

    ORDER BY
        products.name ASC,
        product_variants.color ASC,
        product_variants.id ASC
";

$result = $conn->query($sql);

$variants = [];

if ($result) {
    $variants = $result->fetch_all(MYSQLI_ASSOC);
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

    <title>JAC Admin - Product Variants</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 240px;
            background: #111;
            color: white;
            padding: 30px 20px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }

        .sidebar h1 {
            font-size: 22px;
            letter-spacing: 3px;
            margin-bottom: 40px;
        }

        .sidebar p {
            font-size: 11px;
            color: #999;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 12px;
            margin-bottom: 5px;
            font-size: 13px;
            letter-spacing: 1px;
            transition: 0.2s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2c2c2c;
        }

        /* MAIN */

        .main {
            margin-left: 240px;
            width: calc(100% - 240px);
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
            letter-spacing: 1px;
        }

        .admin-name {
            font-size: 13px;
            color: #555;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .add-button {
            display: inline-block;
            padding: 13px 22px;
            background: black;
            color: white;
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1px;
            transition: 0.2s ease;
        }

        .add-button:hover {
            background: #333;
        }

        /* TABLE */

        .table-container {
            background: white;
            border: 1px solid #ddd;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #111;
            color: white;
            padding: 16px;
            text-align: left;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 500;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #fafafa;
        }

        .variant-id {
            color: #888;
        }

        .size {
            font-weight: bold;
            letter-spacing: 1px;
        }

        .color {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .color-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 1px solid #bbb;
            display: inline-block;
        }

        .color-black {
            background: black;
        }

        .color-white {
            background: white;
        }

        .color-navy {
            background: navy;
        }

        .color-gray {
            background: gray;
        }

        .stock {
            font-weight: bold;
        }

        .stock.out {
            color: #b00020;
        }

        .stock.low {
            color: #b36b00;
        }

        .stock.good {
            color: #237a3b;
        }

        .edit-button,
        .delete-button {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            font-size: 11px;
            letter-spacing: 0.5px;
            margin-right: 5px;
        }

        .edit-button {
            background: #111;
            color: white;
        }

        .delete-button {
            background: white;
            color: #111;
            border: 1px solid #111;
        }

        .edit-button:hover {
            background: #333;
        }

        .delete-button:hover {
            background: #eee;
        }

        .empty {
            padding: 50px;
            text-align: center;
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 900px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 25px;
            }

        }

        @media (max-width: 700px) {

            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .admin-layout {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h1>JAC ADMIN</h1>

        <p>MANAGEMENT</p>

        <a href="dashboard.php">
            DASHBOARD
        </a>

        <a href="products.php">
            PRODUCTS
        </a>

        <a href="variants.php" class="active">
            VARIANTS
        </a>

        <a href="#">
            INVENTORY
        </a>

        <a href="#">
            ORDERS
        </a>

        <a href="#">
            CUSTOMERS
        </a>

        <a href="#">
            CATEGORIES
        </a>

        <br>

        <p>ACCOUNT</p>

        <a href="logout.php">
            LOG OUT
        </a>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <div class="topbar">

            <h2>PRODUCT VARIANTS</h2>

            <div class="admin-name">

                <?php
                echo htmlspecialchars(
                    $_SESSION["admin_first_name"] .
                    " " .
                    $_SESSION["admin_last_name"]
                );
                ?>

            </div>

        </div>


        <div class="actions">

            <a
                href="add_variant.php"
                class="add-button"
            >
                + ADD VARIANT
            </a>

        </div>


        <div class="table-container">

            <?php if (count($variants) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>PRODUCT</th>

                            <th>SIZE</th>

                            <th>COLOR</th>

                            <th>STOCK</th>

                            <th>ACTIONS</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($variants as $variant): ?>

                            <?php

                            $stock =
                                (int) $variant["stock"];

                            if ($stock <= 0) {

                                $stock_class = "out";

                            } elseif ($stock <= 5) {

                                $stock_class = "low";

                            } else {

                                $stock_class = "good";

                            }

                            $color =
                                strtolower(
                                    trim(
                                        $variant["color"]
                                    )
                                );

                            $color_class =
                                "color-" .
                                preg_replace(
                                    "/[^a-z0-9]/",
                                    "",
                                    $color
                                );

                            ?>

                            <tr>

                                <td class="variant-id">

                                    #<?php
                                    echo (int)
                                        $variant["id"];
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $variant["product_name"]
                                    );
                                    ?>

                                </td>

                                <td class="size">

                                    <?php
                                    echo htmlspecialchars(
                                        $variant["size"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    <span class="color">

                                        <span
                                            class="color-dot <?php
                                            echo htmlspecialchars(
                                                $color_class
                                            );
                                            ?>"
                                        ></span>

                                        <?php
                                        echo htmlspecialchars(
                                            $variant["color"]
                                        );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="stock <?php
                                        echo $stock_class;
                                        ?>"
                                    >

                                        <?php
                                        echo $stock;
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <a
                                        href="edit_variant.php?id=<?php echo (int) $variant["id"]; ?>"
                                        class="edit-button"
                                    >
                                        EDIT
                                    </a>

                                    <a
                                        href="delete_variant.php?id=<?php echo (int) $variant["id"]; ?>"
                                        class="delete-button"
                                        onclick="return confirm('Are you sure you want to delete this variant?');"
                                    >
                                        DELETE
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">

                    No product variants found.

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>