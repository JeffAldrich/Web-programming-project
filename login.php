<?php

session_start();

require_once "php/db.php";

/*
    ALREADY LOGGED IN CHECK
*/
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

if (isset($_SESSION["admin_id"])) {
    header("Location: admin/dashboard.php");
    exit;
}

$email_error    = "";
$password_error = "";
$general_error  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"] ?? "");
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

        /*
            STEP 1: Check admins table first.
            If the e-mail matches an admin account, authenticate against
            the admins table and redirect to the admin dashboard.
        */

        $admin_matched = false;

        $stmt_admin = $conn->prepare("
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

        if ($stmt_admin) {

            $stmt_admin->bind_param("s", $email);
            $stmt_admin->execute();
            $admin_result = $stmt_admin->get_result();

            if ($admin_result->num_rows === 1) {

                $admin_matched = true; // e-mail belongs to an admin
                $admin = $admin_result->fetch_assoc();

                if (password_verify($password, $admin["password"])) {

                    /*
                        SUCCESSFUL ADMIN LOGIN
                    */

                    session_regenerate_id(true);

                    $_SESSION["admin_id"]         = $admin["id"];
                    $_SESSION["admin_first_name"] = $admin["first_name"];
                    $_SESSION["admin_last_name"]  = $admin["last_name"];
                    $_SESSION["admin_email"]       = $admin["email"];

                    header("Location: admin/dashboard.php");
                    exit;

                } else {

                    /*
                        Admin e-mail found but wrong password.
                        Do NOT fall through to user check for security.
                    */
                    $password_error = "Incorrect password.";
                }
            }

            $stmt_admin->close();
        }


        /*
            STEP 2: If the e-mail did not match any admin, check users table.
        */

        if (!$admin_matched) {

            $stmt = $conn->prepare("
                SELECT
                    id,
                    first_name,
                    last_name,
                    email,
                    password,
                    last_login
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            if (!$stmt) {

                $general_error = "A database error occurred.";

            } else {

                $stmt->bind_param("s", $email);

                if (!$stmt->execute()) {

                    $general_error = "Unable to process your login.";

                } else {

                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {

                        $user = $result->fetch_assoc();


                        /*
                            CHECK PASSWORD
                        */

                        if (password_verify($password, $user["password"])) {

                            /*
                                SUCCESSFUL USER LOGIN
                            */

                            session_regenerate_id(true);

                            $_SESSION["user_id"]    = $user["id"];
                            $_SESSION["first_name"] = $user["first_name"];
                            $_SESSION["last_name"]  = $user["last_name"];
                            $_SESSION["email"]      = $user["email"];


                            /*
                                WELCOME BACK CHECK

                                last_login is NULL      -> first time logging in
                                last_login has a date   -> returning user
                            */

                            if ($user["last_login"] === null) {
                                $_SESSION["welcome_popup"] = "first";
                            } else {
                                $_SESSION["welcome_popup"] = "back";
                            }


                            /*
                                UPDATE last_login
                            */

                            $stmt_update = $conn->prepare("
                                UPDATE users
                                SET last_login = NOW()
                                WHERE id = ?
                            ");

                            $stmt_update->bind_param("i", $user["id"]);
                            $stmt_update->execute();


                            /*
                                REDIRECT AFTER LOGIN
                            */

                            header("Location: index.php");
                            exit;


                        } else {

                            $password_error = "Incorrect password.";
                        }


                    } else {

                        $email_error = "No account was found with this e-mail.";
                    }
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

    <script>
    /*
        CROSS-TAB LOGIN DETECTION
        If the user has multiple login tabs open and logs in on one tab,
        the other tab immediately redirects to index.php when switched to or submitted.
    */
    (function () {
        function checkLoginStatus() {
            fetch('php/get_counts.php')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && data.logged_in) {
                        localStorage.setItem('jac_logged_in', Date.now());
                        window.location.replace('index.php');
                    }
                })
                .catch(function () {});
        }

        // When user switches back to this tab
        window.addEventListener('focus', checkLoginStatus);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                checkLoginStatus();
            }
        });

        // When another tab logs in and writes to localStorage
        window.addEventListener('storage', function (e) {
            if (e.key === 'jac_logged_in') {
                window.location.replace('index.php');
            }
        });

        // Intercept form submission to prevent submitting if already logged in in another tab
        var loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                fetch('php/get_counts.php')
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data && data.logged_in) {
                            e.preventDefault();
                            window.location.replace('index.php');
                        }
                    })
                    .catch(function () {});
            });
        }
    })();
    </script>

</body>

</html>