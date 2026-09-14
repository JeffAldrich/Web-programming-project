<?php

session_start();

require_once "../php/db.php";


/*
|--------------------------------------------------------------------------
| CHECK ADMIN LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {

    header("Location: ../login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$name = "";
$description = "";

$name_error = "";
$description_error = "";
$general_error = "";


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | VALIDATE NAME
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $name_error = "Category name is required.";

    } elseif (strlen($name) > 100) {

        $name_error = "Category name must not exceed 100 characters.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE DESCRIPTION
    |--------------------------------------------------------------------------
    */

    if (strlen($description) > 1000) {

        $description_error =
            "Description must not exceed 1000 characters.";

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE CATEGORY
    |--------------------------------------------------------------------------
    */

    if (
        $name_error === "" &&
        $description_error === ""
    ) {

        $stmt = $conn->prepare("
            SELECT id
            FROM categories
            WHERE LOWER(name) = LOWER(?)
            LIMIT 1
        ");

        if (!$stmt) {

            $general_error =
                "Unable to check category name.";

        } else {

            $stmt->bind_param(
                "s",
                $name
            );

            if (!$stmt->execute()) {

                $general_error =
                    "Unable to check category name.";

            } else {

                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $name_error =
                        "A category with this name already exists.";

                }

            }

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT CATEGORY
    |--------------------------------------------------------------------------
    */

    if (
        $name_error === "" &&
        $description_error === "" &&
        $general_error === ""
    ) {

        $stmt = $conn->prepare("
            INSERT INTO categories
            (
                name,
                description
            )
            VALUES
            (
                ?,
                ?
            )
        ");

        if (!$stmt) {

            $general_error =
                "Unable to prepare category creation.";

        } else {

            $stmt->bind_param(
                "ss",
                $name,
                $description
            );

            if ($stmt->execute()) {

                $stmt->close();

                header("Location: categories.php");
                exit;

            } else {

                $general_error =
                    "Unable to create category.";

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

<title>Add Category | JAC Admin</title>

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


/* FORM BOX */

.category-box {
    max-width: 750px;

    background: white;

    border: 1px solid #e5e5e5;

    padding: 35px;
}

.category-box h3 {
    font-size: 18px;

    font-weight: 500;

    margin-bottom: 30px;
}


/* FORM */

.category-form {
    display: flex;
    flex-direction: column;

    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;

    gap: 8px;
}

.form-group label {
    font-size: 12px;

    letter-spacing: 1px;

    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;

    padding: 13px 14px;

    border: 1px solid #ccc;

    background: white;

    color: #111;

    font-family: Arial, Helvetica, sans-serif;

    font-size: 14px;

    outline: none;

    transition: 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #111;
}

.form-group textarea {
    min-height: 150px;

    resize: vertical;

    line-height: 1.5;
}


/* ERROR */

.field-error {
    color: #8a2222;

    font-size: 12px;

    line-height: 1.4;
}

.general-error {
    padding: 14px 16px;

    background: #f5eaea;

    border: 1px solid #d8bcbc;

    color: #8a2222;

    font-size: 13px;

    line-height: 1.5;
}


/* BUTTONS */

.form-actions {
    display: flex;

    gap: 12px;

    margin-top: 10px;
}

.save-button,
.cancel-button {
    display: inline-block;

    padding: 13px 24px;

    border: 1px solid #111;

    font-size: 12px;

    letter-spacing: 1px;

    text-decoration: none;

    cursor: pointer;

    transition: 0.2s ease;
}

.save-button {
    background: #111;

    color: white;
}

.save-button:hover {
    background: #333;
}

.cancel-button {
    background: white;

    color: #111;
}

.cancel-button:hover {
    background: #eee;
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

    .category-box {
        padding: 25px;
    }

    .form-actions {
        flex-direction: column;
    }

    .save-button,
    .cancel-button {
        text-align: center;
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


<!-- MAIN -->

<main class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <h2>
            Add Category
        </h2>

        <div class="admin-name">

            Welcome,
            <?= htmlspecialchars(
                $_SESSION["admin_first_name"] ?? "Admin"
            ); ?>

        </div>

    </div>


    <!-- FORM -->

    <section class="category-box">

        <h3>
            CREATE NEW CATEGORY
        </h3>


        <?php if ($general_error !== "") { ?>

            <div class="general-error">

                <?= htmlspecialchars($general_error); ?>

            </div>

        <?php } ?>


        <form
            action="add_category.php"
            method="POST"
            class="category-form"
        >


            <!-- CATEGORY NAME -->

            <div class="form-group">

                <label for="name">
                    CATEGORY NAME
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    value="<?= htmlspecialchars($name); ?>"
                    placeholder="Enter category name"
                    required
                >

                <?php if ($name_error !== "") { ?>

                    <div class="field-error">

                        <?= htmlspecialchars($name_error); ?>

                    </div>

                <?php } ?>

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    DESCRIPTION
                </label>

                <textarea
                    id="description"
                    name="description"
                    maxlength="1000"
                    placeholder="Enter category description"
                ><?= htmlspecialchars($description); ?></textarea>

                <?php if ($description_error !== "") { ?>

                    <div class="field-error">

                        <?= htmlspecialchars($description_error); ?>

                    </div>

                <?php } ?>

            </div>


            <!-- ACTIONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="save-button"
                >
                    CREATE CATEGORY
                </button>

                <a
                    href="categories.php"
                    class="cancel-button"
                >
                    CANCEL
                </a>

            </div>


        </form>

    </section>


</main>

</body>

</html>