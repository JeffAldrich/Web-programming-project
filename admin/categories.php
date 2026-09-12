<?php

session_start();

require_once "../php/db.php";


/*
|--------------------------------------------------------------------------
| ADMIN LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| LOAD CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

$stmt = $conn->prepare("
    SELECT
        categories.id,
        categories.name,
        categories.description,
        COUNT(products.id) AS product_count
    FROM categories
    LEFT JOIN products
        ON products.category_id = categories.id
    GROUP BY
        categories.id,
        categories.name,
        categories.description
    ORDER BY categories.id ASC
");

if (!$stmt) {
    die("Unable to prepare category request.");
}

if (!$stmt->execute()) {
    die("Unable to load categories.");
}

$result = $stmt->get_result();

$categories = $result->fetch_all(MYSQLI_ASSOC);

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

<title>Categories | JAC Admin</title>

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


/* TOP BAR */

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


/* CATEGORY BOX */

.category-box {
    background: white;

    border: 1px solid #e5e5e5;

    padding: 30px;

    overflow-x: auto;
}

.category-header {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
}

.category-header h3 {
    font-size: 18px;
    font-weight: 500;
}


/* ADD BUTTON */

.add-button {
    display: inline-block;

    padding: 12px 20px;

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

.category-table {
    width: 100%;

    border-collapse: collapse;

    min-width: 850px;
}

.category-table th {
    padding: 14px;

    background: #111;
    color: white;

    text-align: left;

    font-size: 12px;
    letter-spacing: 1px;

    font-weight: normal;
}

.category-table td {
    padding: 16px 14px;

    border-bottom: 1px solid #e5e5e5;

    font-size: 13px;

    vertical-align: middle;
}

.category-table tbody tr:hover {
    background: #fafafa;
}

.category-name {
    font-weight: bold;
}

.category-description {
    color: #555;

    line-height: 1.5;

    max-width: 450px;
}

.product-count {
    font-weight: bold;
}


/* ACTIONS */

.actions {
    display: flex;

    gap: 8px;
}

.edit-button,
.delete-button {
    display: inline-block;

    padding: 8px 12px;

    text-decoration: none;

    font-size: 11px;
    letter-spacing: 0.5px;

    border: 1px solid #111;
}

.edit-button {
    background: #111;
    color: white;
}

.edit-button:hover {
    background: #333;
}

.delete-button {
    background: white;
    color: #111;
}

.delete-button:hover {
    background: #eee;
}


/* EMPTY */

.empty-message {
    padding: 40px 20px;

    text-align: center;

    color: #777;

    font-size: 14px;
}


/* MOBILE */

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

    .category-header {
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

    <h1>
        JAC
    </h1>


    <a href="dashboard.php">
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


    <a href="categories.php" class="active">
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
            Categories
        </h2>


        <div class="admin-name">

            Welcome,
            <?= htmlspecialchars(
                $_SESSION["admin_first_name"] ?? "Admin"
            ); ?>

        </div>

    </div>


    <section class="category-box">


        <div class="category-header">

            <h3>
                PRODUCT CATEGORIES
            </h3>


            <a
                href="add_category.php"
                class="add-button"
            >
                + ADD CATEGORY
            </a>

        </div>


        <?php if (empty($categories)): ?>

            <div class="empty-message">

                No categories have been created yet.

            </div>


        <?php else: ?>


            <table class="category-table">


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            CATEGORY
                        </th>

                        <th>
                            DESCRIPTION
                        </th>

                        <th>
                            PRODUCTS
                        </th>

                        <th>
                            ACTIONS
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($categories as $category): ?>

                        <tr>


                            <td>
                                #<?= (int) $category["id"]; ?>
                            </td>


                            <td>

                                <div class="category-name">

                                    <?= htmlspecialchars(
                                        $category["name"]
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <div class="category-description">

                                    <?= htmlspecialchars(
                                        $category["description"]
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <span class="product-count">

                                    <?= (int) $category["product_count"]; ?>

                                </span>

                            </td>


                            <td>

                                <div class="actions">


                                    <a
                                        href="edit_category.php?id=<?= (int) $category["id"]; ?>"
                                        class="edit-button"
                                    >
                                        EDIT
                                    </a>


                                    <a
                                        href="delete_category.php?id=<?= (int) $category["id"]; ?>"
                                        class="delete-button"
                                        onclick="return confirm('Delete this category?');"
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


    </section>


</main>


</body>

</html>