<?php

session_start();

require_once "php/db.php";

$email_error = "";
$password_error = "";
$general_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
        EMAIL VALIDATION
    */

    if ($email === "") {

        $email_error = "E-mail is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $email_error = "Enter a valid e-mail address.";
    }


    /*
        PASSWORD VALIDATION
    */

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
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $general_error = "A database error occurred.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            if (!$stmt->execute()) {

                $general_error = "Unable to process your login.";

            } else {

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();


                    /*
                        CHECK PASSWORD
                    */

                    if (
                        password_verify(
                            $password,
                            $user["password"]
                        )
                    ) {

                        /*
                            SUCCESSFUL LOGIN
                        */

                        session_regenerate_id(true);

                        $_SESSION["user_id"] =
                            $user["id"];

                        $_SESSION["first_name"] =
                            $user["first_name"];

                        $_SESSION["last_name"] =
                            $user["last_name"];

                        $_SESSION["email"] =
                            $user["email"];


                        /*
                            REDIRECT AFTER LOGIN
                        */

                        header("Location: index.php");
                        exit;


                    } else {

                        /*
                            WRONG PASSWORD
                        */

                        $password_error =
                            "Incorrect password.";
                    }


                } else {

                    /*
                        EMAIL DOES NOT EXIST
                    */

                    $email_error =
                        "No account was found with this e-mail.";
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

    <title>Login - JAC</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>

<body>

    <section class="login-section">

        <div class="login-container">

            <h1>LOGIN</h1>

            <p class="login-intro">
                Welcome back to JAC.
            </p>


            <?php if ($general_error !== ""): ?>

                <div class="login-errors">

                    <p>
                        <?= htmlspecialchars($general_error) ?>
                    </p>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="login.php"
            >


                <!-- EMAIL -->

                <label for="email">
                    E-mail
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your e-mail"
                    value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                    required
                >

                <?php if ($email_error !== ""): ?>

                    <p class="login-field-error">
                        ⚠ <?= htmlspecialchars($email_error) ?>
                    </p>

                <?php endif; ?>


                <!-- PASSWORD -->

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', this)"
                        aria-label="Show password"
                    >
                        👁
                    </button>

                </div>

                <?php if ($password_error !== ""): ?>

                    <p class="login-field-error">
                        ⚠ <?= htmlspecialchars($password_error) ?>
                    </p>

                <?php endif; ?>


                <!-- LOGIN BUTTON -->

                <button type="submit">
                    LOGIN
                </button>


            </form>


            <p class="forgot-password">

                <a href="#">
                    Forgot your password?
                </a>

            </p>


            <p class="register-link">

                Don't have an account?

                <a href="register.php">
                    REGISTER
                </a>

            </p>

        </div>

    </section>


    <script src="script.js"></script>

</body>

</html>

<?php if ($password_error !== ""): ?>

    <p class="login-field-error">
        ⚠ <?= htmlspecialchars($password_error) ?>
    </p>

<?php endif; ?>