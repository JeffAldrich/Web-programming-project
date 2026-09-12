<?php

session_start();

require_once "../php/db.php";


/*
|--------------------------------------------------------------------------
| CHECK ADMIN LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {

    header("Location: login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET CATEGORY ID
|--------------------------------------------------------------------------
*/

$category_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($category_id <= 0) {

    header("Location: categories.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK CATEGORY EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name
    FROM categories
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare category request.");
}

$stmt->bind_param(
    "i",
    $category_id
);

if (!$stmt->execute()) {
    die("Unable to load category.");
}

$result = $stmt->get_result();

$category = $result->fetch_assoc();

$stmt->close();


if (!$category) {

    header("Location: categories.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK PRODUCTS USING THIS CATEGORY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS product_count
    FROM products
    WHERE category_id = ?
");

if (!$stmt) {
    die("Unable to prepare product check.");
}

$stmt->bind_param(
    "i",
    $category_id
);

if (!$stmt->execute()) {
    die("Unable to check category products.");
}

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$product_count = (int) $row["product_count"];

$stmt->close();


/*
|--------------------------------------------------------------------------
| DO NOT DELETE CATEGORY WITH PRODUCTS
|--------------------------------------------------------------------------
*/

if ($product_count > 0) {

    $message =
        "This category cannot be deleted because " .
        $product_count .
        " product" .
        ($product_count === 1 ? " is" : "s are") .
        " assigned to it.";

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>Cannot Delete Category | JAC Admin</title>

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

        .message-box {
            max-width: 650px;

            background: white;

            border: 1px solid #e5e5e5;

            padding: 40px;

            text-align: center;
        }

        .message-box h3 {
            margin-bottom: 15px;

            font-size: 20px;

            font-weight: 500;
        }

        .message-box p {
            margin-bottom: 30px;

            color: #666;

            font-size: 14px;

            line-height: 1.6;
        }

        .back-button {
            display: inline-block;

            padding: 13px 24px;

            background: #111;

            color: white;

            border: 1px solid #111;

            text-decoration: none;

            font-size: 12px;

            letter-spacing: 1px;

            transition: 0.2s ease;
        }

        .back-button:hover {
            background: #333;
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

            .topbar {
                gap: 20px;

                align-items: flex-start;

                flex-direction: column;
            }

            .message-box {
                padding: 30px 20px;
            }

        }

        </style>

    </head>

    <body>


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

        <a href="orders.php">
            ORDERS
        </a>

        <a href="customers.php">
            CUSTOMERS
        </a>

        <a href="categories.php" class="active">
            CATEGORIES
        </a>

        <a href="logout.php" class="logout">
            LOGOUT
        </a>

    </aside>


    <main class="main">

        <div class="topbar">

            <h2>
                Cannot Delete Category
            </h2>

            <div class="admin-name">

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION["admin_first_name"] ?? "Admin"
                ); ?>

            </div>

        </div>


        <section class="message-box">

            <h3>
                Category Cannot Be Deleted
            </h3>

            <p>
                <?= htmlspecialchars($message); ?>
            </p>

            <a
                href="categories.php"
                class="back-button"
            >
                BACK TO CATEGORIES
            </a>

        </section>

    </main>

    </body>

    </html>

    <?php

    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE EMPTY CATEGORY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM categories
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare category deletion.");
}

$stmt->bind_param(
    "i",
    $category_id
);

if (!$stmt->execute()) {
    die("Unable to delete category.");
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| RETURN TO CATEGORIES
|--------------------------------------------------------------------------
*/

header("Location: categories.php");
exit;

?>