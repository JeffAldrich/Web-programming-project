<?php

session_start();

require_once "../php/db.php";


/*
    ADMIN ACCESS PROTECTION
*/

if (!isset($_SESSION["admin_id"])) {

    header("Location: login.php");
    exit;

}


/*
    GET PRODUCTS
*/

$products = [];

$stmt = $conn->prepare("
    SELECT
        products.id,
        products.name,
        products.price,
        products.image,
        products.description,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    ORDER BY products.id DESC
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    $products = $result->fetch_all(
        MYSQLI_ASSOC
    );
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

    <title>JAC Admin - Products</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #111;
        }

        .admin-header {
            height: 75px;
            background: #000;
            color: white;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 40px;
        }

        .admin-logo {
            letter-spacing: 3px;
            font-size: 20px;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 13px;
        }

        .logout {
            color: white;
            text-decoration: none;

            border: 1px solid white;
            padding: 8px 15px;
        }

        .logout:hover {
            background: white;
            color: black;
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 75px);
        }

        .sidebar {
            width: 230px;
            background: #222;
            color: white;
            padding: 30px 0;
            flex-shrink: 0;
        }

        .sidebar h3 {
            font-size: 11px;
            letter-spacing: 2px;
            color: #aaa;
            padding: 0 25px 15px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;

            padding: 13px 25px;

            font-size: 13px;
        }

        .sidebar a:hover {
            background: #333;
        }

        .content {
            flex: 1;
            padding: 40px;
            overflow-x: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 30px;
        }

        .page-header h1 {
            margin-bottom: 6px;
        }

        .page-header p {
            color: #777;
            font-size: 13px;
        }

        .add-button {
            background: black;
            color: white;

            text-decoration: none;

            padding: 13px 20px;

            font-size: 12px;
            letter-spacing: 1px;
        }

        .add-button:hover {
            background: #333;
        }

        .products-table {
            width: 100%;
            min-width: 850px;

            border-collapse: collapse;

            background: white;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.06);
        }

        .products-table th {
            text-align: left;

            background: #111;
            color: white;

            padding: 15px;

            font-size: 11px;
            letter-spacing: 1px;
        }

        .products-table td {
            padding: 15px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            vertical-align: middle;
        }

        .product-image {
            width: 70px;
            height: 85px;

            object-fit: cover;

            background: #eee;
        }

        .product-name {
            font-weight: bold;
        }

        .product-description {
            color: #777;
            max-width: 250px;

            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-button,
        .delete-button {
            padding: 8px 12px;

            text-decoration: none;

            font-size: 11px;
        }

        .edit-button {
            background: black;
            color: white;
        }

        .delete-button {
            background: white;
            color: #c00000;

            border: 1px solid #c00000;
        }

        .edit-button:hover {
            background: #333;
        }

        .delete-button:hover {
            background: #c00000;
            color: white;
        }

        .empty-products {
            background: white;
            padding: 50px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 800px) {

            .sidebar {
                width: 190px;
            }

            .content {
                padding: 25px;
            }

            .page-header {
                align-items: flex-start;
                gap: 20px;
            }

        }

    </style>

</head>

<body>


<header class="admin-header">

    <div class="admin-logo">
        JAC ADMIN
    </div>

    <div class="admin-user">

        <span>

            <?php
            echo htmlspecialchars(
                $_SESSION["admin_first_name"]
            );
            ?>

        </span>

        <a
            href="logout.php"
            class="logout"
        >
            LOG OUT
        </a>

    </div>

</header>


<div class="layout">


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h3>
            MANAGEMENT
        </h3>

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

        <a href="customers.php">
            CUSTOMERS
        </a>

        <a href="categories.php">
            CATEGORIES
        </a>

    </aside>


    <!-- CONTENT -->

    <main class="content">


        <div class="page-header">

            <div>

                <h1>
                    PRODUCTS
                </h1>

                <p>
                    Manage products in the JAC store.
                </p>

            </div>


            <a
                href="add_product.php"
                class="add-button"
            >
                + ADD PRODUCT
            </a>

        </div>


        <?php if (empty($products)): ?>

            <div class="empty-products">

                <h2>
                    NO PRODUCTS FOUND
                </h2>

                <p>
                    Add your first product.
                </p>

            </div>

        <?php else: ?>


            <table class="products-table">

                <thead>

                    <tr>

                        <th>
                            IMAGE
                        </th>

                        <th>
                            PRODUCT
                        </th>

                        <th>
                            PRICE
                        </th>

                        <th>
                            CATEGORY
                        </th>

                        <th>
                            DESCRIPTION
                        </th>

                        <th>
                            ACTIONS
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($products as $product): ?>

                        <tr>


                            <td>

                                <?php if (!empty($product["image"])): ?>

                                    <img
                                        src="../Images/<?php
                                            echo htmlspecialchars(
                                                $product["image"]
                                            );
                                        ?>"
                                        class="product-image"
                                        alt=""
                                    >

                                <?php else: ?>

                                    <div
                                        class="product-image"
                                    ></div>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="product-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $product["name"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                ₱<?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["category_name"]
                                    ?? "Uncategorized"
                                );
                                ?>

                            </td>


                            <td>

                                <div
                                    class="product-description"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product["description"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <div
                                    class="action-buttons"
                                >

                                    <a
                                        href="edit_product.php?id=<?php
                                            echo $product["id"];
                                        ?>"
                                        class="edit-button"
                                    >
                                        EDIT
                                    </a>


                                    <a
                                        href="delete_product.php?id=<?php
                                            echo $product["id"];
                                        ?>"
                                        class="delete-button"
                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                    >
                                        DELETE
                                    </a>

                                </div>

                            </td>


                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>


        <?php endif; ?>


    </main>

</div>


</body>

</html>