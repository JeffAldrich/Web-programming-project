<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$product_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

$error = "";

$name = "";
$price = "";
$description = "";
$category_id = "";
$current_image = "";


/*
|--------------------------------------------------------------------------
| LOAD PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        price,
        image,
        description,
        category_id
    FROM products
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to load product.");
}

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

$name = $product["name"];
$price = $product["price"];
$current_image = $product["image"];
$description = $product["description"];
$category_id = (int) $product["category_id"];


/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

$result = $conn->query("
    SELECT
        id,
        name
    FROM categories
    ORDER BY name ASC
");

if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}


/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = (int) ($_POST["category_id"] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $error = "Product name is required.";

    } elseif ($price === "") {

        $error = "Price is required.";

    } elseif (!is_numeric($price)) {

        $error = "Price must be a valid number.";

    } elseif ((float) $price < 0) {

        $error = "Price cannot be negative.";

    } elseif ($description === "") {

        $error = "Product description is required.";

    } elseif ($category_id <= 0) {

        $error = "Please select a category.";

    } else {

        $price = (float) $price;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK CATEGORY
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $stmt = $conn->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "A database error occurred.";

        } else {

            $stmt->bind_param(
                "i",
                $category_id
            );

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {

                $error = "Selected category does not exist.";

            }

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE HANDLING
    |--------------------------------------------------------------------------
    */

    $new_image = $current_image;

    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES["image"]["error"] !==
            UPLOAD_ERR_OK
        ) {

            $error = "There was a problem uploading the image.";

        } elseif (
            $_FILES["image"]["size"] >
            5 * 1024 * 1024
        ) {

            $error = "Image must be 5 MB or smaller.";

        } else {

            $image_info =
                getimagesize(
                    $_FILES["image"]["tmp_name"]
                );

            if ($image_info === false) {

                $error = "The uploaded file is not a valid image.";

            } else {

                $mime =
                    $image_info["mime"];

                $allowed_mimes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (
                    !isset(
                        $allowed_mimes[$mime]
                    )
                ) {

                    $error =
                        "Only JPG, PNG, and WEBP images are allowed.";

                } else {

                    $extension =
                        $allowed_mimes[$mime];

                    $new_image =
                        "product_" .
                        bin2hex(
                            random_bytes(8)
                        ) .
                        "." .
                        $extension;

                    $upload_directory =
                        "../Images/";

                    $upload_path =
                        $upload_directory .
                        $new_image;

                    if (
                        !move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $upload_path
                        )
                    ) {

                        $error =
                            "Unable to save the uploaded image.";

                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PRODUCT
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $stmt = $conn->prepare("
            UPDATE products
            SET
                name = ?,
                price = ?,
                image = ?,
                description = ?,
                category_id = ?
            WHERE id = ?
        ");

        if (!$stmt) {

            $error =
                "Unable to prepare the database request.";

        } else {

            $stmt->bind_param(
                "sdssii",
                $name,
                $price,
                $new_image,
                $description,
                $category_id,
                $product_id
            );

            if ($stmt->execute()) {

                /*
                |--------------------------------------------------------------------------
                | DELETE OLD IMAGE AFTER SUCCESSFUL UPDATE
                |--------------------------------------------------------------------------
                */

                if (
                    $new_image !== $current_image &&
                    $current_image !== ""
                ) {

                    $old_image_path =
                        "../Images/" .
                        $current_image;

                    if (
                        file_exists(
                            $old_image_path
                        )
                    ) {

                        unlink(
                            $old_image_path
                        );
                    }
                }

                $stmt->close();

                header("Location: products.php");
                exit;

            } else {

                /*
                |--------------------------------------------------------------------------
                | DELETE NEW IMAGE IF DATABASE UPDATE FAILED
                |--------------------------------------------------------------------------
                */

                if (
                    $new_image !== $current_image &&
                    file_exists(
                        "../Images/" .
                        $new_image
                    )
                ) {

                    unlink(
                        "../Images/" .
                        $new_image
                    );
                }

                $error =
                    "Unable to update the product.";

                $stmt->close();
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

    <title>JAC Admin - Edit Product</title>

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
            max-width: 800px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ccc;
            background: white;
            font-size: 14px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
            line-height: 1.5;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #111;
        }

        /* CURRENT IMAGE */

        .current-image {
            margin-bottom: 12px;
        }

        .current-image img {
            width: 180px;
            height: 220px;
            object-fit: cover;
            display: block;
            border: 1px solid #ddd;
        }

        .current-image p {
            margin-top: 8px;
            font-size: 11px;
            color: #777;
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

        <a href="products.php" class="active">
            PRODUCTS
        </a>

        <a href="variants.php">
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

            <h2>EDIT PRODUCT</h2>

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
                enctype="multipart/form-data"
            >

                <!-- PRODUCT NAME -->

                <div class="form-group">

                    <label for="name">
                        PRODUCT NAME
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?php
                        echo htmlspecialchars($name);
                        ?>"
                        required
                    >

                </div>


                <!-- PRICE -->

                <div class="form-group">

                    <label for="price">
                        PRICE
                    </label>

                    <input
                        type="number"
                        name="price"
                        id="price"
                        min="0"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            (string) $price
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- CATEGORY -->

                <div class="form-group">

                    <label for="category_id">
                        CATEGORY
                    </label>

                    <select
                        name="category_id"
                        id="category_id"
                        required
                    >

                        <option value="">
                            Select a category
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php
                                echo (int) $category["id"];
                                ?>"
                                <?php
                                if (
                                    (int) $category["id"] ===
                                    $category_id
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $category["name"]
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        DESCRIPTION
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        required
                    ><?php
                    echo htmlspecialchars($description);
                    ?></textarea>

                </div>


                <!-- CURRENT IMAGE -->

                <div class="form-group">

                    <label>
                        CURRENT IMAGE
                    </label>

                    <?php if ($current_image !== ""): ?>

                        <div class="current-image">

                            <img
                                src="../Images/<?php
                                echo htmlspecialchars(
                                    $current_image
                                );
                                ?>"
                                alt="Current product image"
                            >

                            <p>
                                Current product image
                            </p>

                        </div>

                    <?php else: ?>

                        <p class="form-help">
                            This product currently has no image.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- NEW IMAGE -->

                <div class="form-group">

                    <label for="image">
                        REPLACE IMAGE
                    </label>

                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <div class="form-help">
                        Leave this empty to keep the current image.
                        Maximum 5 MB. JPG, PNG, and WEBP only.
                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="save-button"
                    >
                        SAVE CHANGES
                    </button>

                    <a
                        href="products.php"
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