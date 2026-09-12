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
    DEFAULT VALUES
*/

$name = "";
$price = "";
$description = "";
$category_id = "";

$errors = [];


/*
    GET CATEGORIES
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

    $categories =
        $result->fetch_all(
            MYSQLI_ASSOC
        );

}


/*
    FORM SUBMISSION
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $category_id =
        (int) ($_POST["category_id"] ?? 0);


    /*
        VALIDATION
    */

    if ($name === "") {

        $errors[] =
            "Product name is required.";

    }


    if ($price === "") {

        $errors[] =
            "Price is required.";

    } elseif (!is_numeric($price) || $price < 0) {

        $errors[] =
            "Enter a valid price.";

    }


    if ($description === "") {

        $errors[] =
            "Product description is required.";

    }


    if ($category_id <= 0) {

        $errors[] =
            "Please select a category.";

    }


    /*
        IMAGE VALIDATION
    */

    $image_name = "";


    if (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            "Product image is required.";

    } elseif (
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {

        $errors[] =
            "There was a problem uploading the image.";

    } else {

        $image = $_FILES["image"];


        /*
            MAX FILE SIZE
            5 MB
        */

        if ($image["size"] > 5 * 1024 * 1024) {

            $errors[] =
                "Image must be 5 MB or smaller.";

        }


        /*
            CHECK ACTUAL IMAGE TYPE
        */

        $image_info =
            getimagesize($image["tmp_name"]);


        if ($image_info === false) {

            $errors[] =
                "The uploaded file must be an image.";

        } else {

            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            ];


            $mime_type =
                $image_info["mime"];


            if (
                !isset(
                    $allowed_types[$mime_type]
                )
            ) {

                $errors[] =
                    "Only JPG, PNG, and WEBP images are allowed.";

            }

        }

    }


    /*
        CREATE PRODUCT
    */

    if (empty($errors)) {


        /*
            CREATE UNIQUE IMAGE NAME
        */

        $extension =
            $allowed_types[$mime_type];

        $image_name =
            "product_" .
            bin2hex(random_bytes(8)) .
            "." .
            $extension;


        /*
            IMAGE DESTINATION
        */

        $image_destination =
            "../Images/" .
            $image_name;


        /*
            MOVE IMAGE
        */

        if (
            !move_uploaded_file(
                $image["tmp_name"],
                $image_destination
            )
        ) {

            $errors[] =
                "Unable to save the uploaded image.";

        }

    }


    /*
        INSERT PRODUCT
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            INSERT INTO products
            (
                name,
                price,
                image,
                description,
                category_id
            )
            VALUES (?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            $errors[] =
                "Database error.";

        } else {

            $price_value =
                (float) $price;


            $stmt->bind_param(
                "sdssi",
                $name,
                $price_value,
                $image_name,
                $description,
                $category_id
            );


            if ($stmt->execute()) {

                header(
                    "Location: products.php"
                );

                exit;

            } else {

                /*
                    DELETE IMAGE IF DATABASE
                    INSERT FAILED
                */

                if (
                    file_exists(
                        $image_destination
                    )
                ) {

                    unlink(
                        $image_destination
                    );

                }


                $errors[] =
                    "Unable to create product.";

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

    <title>JAC Admin - Add Product</title>


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

            min-height:
                calc(100vh - 75px);
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

            padding:
                0 25px 15px;
        }


        .sidebar a {
            display: block;

            color: white;

            text-decoration: none;

            padding:
                13px 25px;

            font-size: 13px;
        }


        .sidebar a:hover {
            background: #333;
        }


        .content {
            flex: 1;

            padding: 40px;

            max-width: 1000px;
        }


        .page-header {
            margin-bottom: 30px;
        }


        .page-header h1 {
            margin-bottom: 7px;
        }


        .page-header p {
            color: #777;

            font-size: 13px;
        }


        .form-container {
            background: white;

            padding: 35px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.06);
        }


        .form-group {
            margin-bottom: 25px;
        }


        .form-group label {
            display: block;

            font-size: 11px;

            letter-spacing: 1px;

            margin-bottom: 8px;
        }


        .form-group input,
        .form-group textarea,
        .form-group select {

            width: 100%;

            padding: 13px;

            border: 1px solid #ccc;

            font-family: inherit;

            font-size: 14px;
        }


        .form-group textarea {

            min-height: 150px;

            resize: vertical;
        }


        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {

            outline: none;

            border-color: #000;
        }


        .image-help {

            margin-top: 7px;

            font-size: 11px;

            color: #777;
        }


        .errors {

            background: #fff1f1;

            border: 1px solid #d00000;

            padding: 15px;

            margin-bottom: 25px;

            color: #b00000;
        }


        .errors p {

            font-size: 13px;

            margin-bottom: 5px;
        }


        .errors p:last-child {

            margin-bottom: 0;
        }


        .form-actions {

            display: flex;

            gap: 10px;

            margin-top: 30px;
        }


        .save-button,
        .cancel-button {

            padding:
                13px 22px;

            font-size: 11px;

            letter-spacing: 1px;

            text-decoration: none;

            cursor: pointer;
        }


        .save-button {

            background: #000;

            color: white;

            border: 1px solid #000;
        }


        .save-button:hover {

            background: #333;
        }


        .cancel-button {

            background: white;

            color: #000;

            border: 1px solid #000;
        }


        .cancel-button:hover {

            background: #eee;
        }


        @media (max-width: 800px) {

            .sidebar {

                width: 190px;
            }

            .content {

                padding: 25px;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<header class="admin-header">

    <div class="admin-logo">
        JAC ADMIN
    </div>


    <div class="admin-user">

        <span>

            <?php
            echo htmlspecialchars(
                $_SESSION[
                    "admin_first_name"
                ]
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

            <h1>
                ADD PRODUCT
            </h1>

            <p>
                Add a new product to the JAC store.
            </p>

        </div>



        <?php if (!empty($errors)): ?>

            <div class="errors">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $error
                        );
                        ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>



        <div class="form-container">


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- PRODUCT NAME -->

                <div class="form-group">

                    <label>
                        PRODUCT NAME
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?php
                            echo htmlspecialchars(
                                $name
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- PRICE -->

                <div class="form-group">

                    <label>
                        PRICE
                    </label>

                    <input
                        type="number"
                        name="price"
                        value="<?php
                            echo htmlspecialchars(
                                $price
                            );
                        ?>"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        required
                    >

                </div>



                <!-- CATEGORY -->

                <div class="form-group">

                    <label>
                        CATEGORY
                    </label>

                    <select
                        name="category_id"
                        required
                    >

                        <option value="">
                            SELECT CATEGORY
                        </option>


                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php
                                    echo $category["id"];
                                ?>"
                                <?php
                                if (
                                    $category_id ==
                                    $category["id"]
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

                    <label>
                        DESCRIPTION
                    </label>

                    <textarea
                        name="description"
                        required
                    ><?php
                        echo htmlspecialchars(
                            $description
                        );
                    ?></textarea>

                </div>



                <!-- IMAGE -->

                <div class="form-group">

                    <label>
                        PRODUCT IMAGE
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <p class="image-help">
                        JPG, PNG, or WEBP. Maximum 5 MB.
                    </p>

                </div>



                <!-- BUTTONS -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="save-button"
                    >
                        CREATE PRODUCT
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