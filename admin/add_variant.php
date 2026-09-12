<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

$product_id = "";
$size = "";
$color = "";
$stock = "";

/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

$products = [];

$result = $conn->query("
    SELECT
        id,
        name
    FROM products
    ORDER BY name ASC
");

if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}


/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = (int) ($_POST["product_id"] ?? 0);
    $size = trim($_POST["size"] ?? "");
    $color = trim($_POST["color"] ?? "");
    $stock = trim($_POST["stock"] ?? "");

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($product_id <= 0) {

        $error = "Please select a product.";

    } elseif ($size === "") {

        $error = "Please select a size.";

    } elseif ($color === "") {

        $error = "Please select a color.";

    } elseif ($stock === "") {

        $error = "Please enter the stock quantity.";

    } elseif (!ctype_digit($stock)) {

        $error = "Stock must be a whole number.";

    } else {

        $stock = (int) $stock;

        if ($stock < 0) {

            $error = "Stock cannot be negative.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | CHECK PRODUCT EXISTS
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                SELECT id
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            if (!$stmt) {

                $error = "A database error occurred.";

            } else {

                $stmt->bind_param(
                    "i",
                    $product_id
                );

                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 0) {

                    $error = "Selected product does not exist.";

                }

                $stmt->close();
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE VARIANT
            |--------------------------------------------------------------------------
            */

            if ($error === "") {

                $stmt = $conn->prepare("
                    SELECT id
                    FROM product_variants
                    WHERE product_id = ?
                    AND size = ?
                    AND color = ?
                    LIMIT 1
                ");

                if (!$stmt) {

                    $error = "A database error occurred.";

                } else {

                    $stmt->bind_param(
                        "iss",
                        $product_id,
                        $size,
                        $color
                    );

                    $stmt->execute();

                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {

                        $error =
                            "This size and color variant already exists.";

                    }

                    $stmt->close();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT VARIANT
            |--------------------------------------------------------------------------
            */

            if ($error === "") {

                $stmt = $conn->prepare("
                    INSERT INTO product_variants
                    (
                        product_id,
                        size,
                        color,
                        stock
                    )
                    VALUES (?, ?, ?, ?)
                ");

                if (!$stmt) {

                    $error =
                        "Unable to prepare the database request.";

                } else {

                    $stmt->bind_param(
                        "issi",
                        $product_id,
                        $size,
                        $color,
                        $stock
                    );

                    if ($stmt->execute()) {

                        header("Location: variants.php");
                        exit;

                    } else {

                        $error =
                            "Unable to add the product variant.";
                    }

                    $stmt->close();
                }
            }
        }
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

    <title>JAC Admin - Add Variant</title>

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

        /* FORM */

        .form-container {
            max-width: 700px;
            background: white;
            border: 1px solid #ddd;
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            font-size: 12px;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ccc;
            background: white;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #111;
        }

        .form-help {
            margin-top: 7px;
            font-size: 11px;
            color: #777;
        }

        /* ERROR */

        .error {
            background: #fff0f0;
            border: 1px solid #d99;
            color: #a00000;
            padding: 14px;
            margin-bottom: 25px;
            font-size: 13px;
        }

        /* BUTTONS */

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 35px;
        }

        .save-button,
        .cancel-button {
            padding: 13px 24px;
            font-size: 12px;
            letter-spacing: 1px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .save-button {
            border: 1px solid #111;
            background: #111;
            color: white;
        }

        .save-button:hover {
            background: #333;
        }

        .cancel-button {
            border: 1px solid #111;
            background: white;
            color: #111;
        }

        .cancel-button:hover {
            background: #eee;
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
                padding: 25px;
            }

            .form-container {
                padding: 25px;
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

            <h2>ADD PRODUCT VARIANT</h2>

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


        <div class="form-container">

            <?php if ($error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action=""
            >

                <!-- PRODUCT -->

                <div class="form-group">

                    <label for="product_id">
                        PRODUCT
                    </label>

                    <select
                        name="product_id"
                        id="product_id"
                        required
                    >

                        <option value="">
                            Select a product
                        </option>

                        <?php foreach ($products as $product): ?>

                            <option
                                value="<?php
                                echo (int) $product["id"];
                                ?>"
                                <?php
                                if (
                                    (int) $product["id"] ===
                                    (int) $product_id
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $product["name"]
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- SIZE -->

                <div class="form-group">

                    <label for="size">
                        SIZE
                    </label>

                    <select
                        name="size"
                        id="size"
                        required
                    >

                        <option value="">
                            Select a size
                        </option>

                        <option
                            value="S"
                            <?php
                            echo $size === "S"
                                ? "selected"
                                : "";
                            ?>
                        >
                            S
                        </option>

                        <option
                            value="M"
                            <?php
                            echo $size === "M"
                                ? "selected"
                                : "";
                            ?>
                        >
                            M
                        </option>

                        <option
                            value="L"
                            <?php
                            echo $size === "L"
                                ? "selected"
                                : "";
                            ?>
                        >
                            L
                        </option>

                        <option
                            value="XL"
                            <?php
                            echo $size === "XL"
                                ? "selected"
                                : "";
                            ?>
                        >
                            XL
                        </option>

                    </select>

                </div>


                <!-- COLOR -->

                <div class="form-group">

                    <label for="color">
                        COLOR
                    </label>

                    <select
                        name="color"
                        id="color"
                        required
                    >

                        <option value="">
                            Select a color
                        </option>

                        <option
                            value="Black"
                            <?php
                            echo $color === "Black"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Black
                        </option>

                        <option
                            value="White"
                            <?php
                            echo $color === "White"
                                ? "selected"
                                : "";
                            ?>
                        >
                            White
                        </option>

                        <option
                            value="Navy"
                            <?php
                            echo $color === "Navy"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Navy
                        </option>

                        <option
                            value="Gray"
                            <?php
                            echo $color === "Gray"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Gray
                        </option>

                    </select>

                </div>


                <!-- STOCK -->

                <div class="form-group">

                    <label for="stock">
                        STOCK QUANTITY
                    </label>

                    <input
                        type="number"
                        name="stock"
                        id="stock"
                        min="0"
                        step="1"
                        value="<?php
                        echo htmlspecialchars($stock);
                        ?>"
                        required
                    >

                    <div class="form-help">
                        Enter the number of units currently available.
                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="save-button"
                    >
                        ADD VARIANT
                    </button>

                    <a
                        href="variants.php"
                        class="cancel-button"
                    >
                        CANCEL
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>