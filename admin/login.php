<?php

session_start();

require_once "../php/db.php";


/*
    ALREADY LOGGED IN
*/

if (isset($_SESSION["admin_id"])) {

    header("Location: dashboard.php");
    exit;

}


$email_error = "";
$password_error = "";
$general_error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
        VALIDATION
    */

    if ($email === "") {

        $email_error = "E-mail is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $email_error = "Enter a valid e-mail address.";

    }


    if ($password === "") {

        $password_error = "Password is required.";

    }


    /*
        LOGIN
    */

    if (
        $email_error === "" &&
        $password_error === ""
    ) {

        $stmt = $conn->prepare("
            SELECT
                id,
                first_name,
                last_name,
                email,
                password
            FROM admins
            WHERE email = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $general_error =
                "A database error occurred.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );


            if (!$stmt->execute()) {

                $general_error =
                    "Unable to process your login.";

            } else {

                $result =
                    $stmt->get_result();


                if ($result->num_rows === 1) {

                    $admin =
                        $result->fetch_assoc();


                    if (
                        password_verify(
                            $password,
                            $admin["password"]
                        )
                    ) {

                        session_regenerate_id(true);


                        $_SESSION["admin_id"] =
                            $admin["id"];

                        $_SESSION["admin_first_name"] =
                            $admin["first_name"];

                        $_SESSION["admin_last_name"] =
                            $admin["last_name"];

                        $_SESSION["admin_email"] =
                            $admin["email"];


                        header(
                            "Location: dashboard.php"
                        );

                        exit;


                    } else {

                        $password_error =
                            "Incorrect password.";

                    }

                } else {

                    $email_error =
                        "No admin account was found.";

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

    <title>JAC Admin Login</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f1f1f1;
        }

        .admin-login {
            width: 400px;
            max-width: 90%;

            background: white;

            padding: 45px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.12);
        }

        .admin-login h1 {
            text-align: center;

            margin-bottom: 10px;

            letter-spacing: 2px;
        }

        .admin-login .subtitle {
            text-align: center;

            color: #777;

            font-size: 13px;

            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            font-size: 12px;

            letter-spacing: 1px;

            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;

            padding: 13px;

            border: 1px solid #ccc;

            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;

            border-color: #000;
        }

        .error {
            color: #c00000;

            font-size: 12px;

            margin-top: 6px;
        }

        .general-error {
            color: #c00000;

            text-align: center;

            font-size: 13px;

            margin-bottom: 20px;
        }

        .login-button {
            width: 100%;

            padding: 14px;

            border: none;

            background: #000;

            color: white;

            cursor: pointer;

            letter-spacing: 2px;

            font-size: 12px;
        }

        .login-button:hover {
            background: #333;
        }

    </style>

</head>

<body>

<div class="admin-login">

    <h1>
        JAC
    </h1>

    <p class="subtitle">
        ADMINISTRATION
    </p>


    <?php if ($general_error !== ""): ?>

        <div class="general-error">

            <?php
            echo htmlspecialchars(
                $general_error
            );
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <div class="form-group">

            <label>
                E-MAIL
            </label>

            <input
                type="email"
                name="email"
                value="<?php
                    echo htmlspecialchars(
                        $_POST["email"] ?? ""
                    );
                ?>"
                required
            >

            <?php if ($email_error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars(
                        $email_error
                    );
                    ?>

                </div>

            <?php endif; ?>

        </div>


        <div class="form-group">

            <label>
                PASSWORD
            </label>

            <input
                type="password"
                name="password"
                required
            >

            <?php if ($password_error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars(
                        $password_error
                    );
                    ?>

                </div>

            <?php endif; ?>

        </div>


        <button
            type="submit"
            class="login-button"
        >
            ADMIN LOGIN
        </button>


    </form>

</div>

</body>

</html>