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
            SINGLE USERS TABLE LOGIN
            Queries the single `users` table, verifies password,
            and uses if / else if on the `role` column to direct
            the user to the Admin Dashboard or Customer Storefront.
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                first_name,
                last_name,
                email,
                password,
                role,
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
                        VERIFY PASSWORD
                    */
                    if (password_verify($password, $user["password"])) {

                        session_regenerate_id(true);

                        $user_role = strtolower(trim($user["role"] ?? "customer"));

                        /*
                            ROLE-BASED REDIRECTION (if / else if)
                        */
                        if ($user_role === "admin") {

                            $_SESSION["admin_id"]         = $user["id"];
                            $_SESSION["admin_first_name"] = $user["first_name"];
                            $_SESSION["admin_last_name"]  = $user["last_name"];
                            $_SESSION["admin_email"]      = $user["email"];
                            $_SESSION["role"]             = "admin";

                            header("Location: admin/dashboard.php");
                            exit;

                        } elseif ($user_role === "customer") {

                            $_SESSION["user_id"]    = $user["id"];
                            $_SESSION["first_name"] = $user["first_name"];
                            $_SESSION["last_name"]  = $user["last_name"];
                            $_SESSION["email"]      = $user["email"];
                            $_SESSION["role"]       = "customer";

                            /* Welcome back popup flag */
                            if ($user["last_login"] === null) {
                                $_SESSION["welcome_popup"] = "first";
                            } else {
                                $_SESSION["welcome_popup"] = "back";
                            }

                            /* Update last login timestamp */
                            $stmt_update = $conn->prepare("
                                UPDATE users
                                SET last_login = NOW()
                                WHERE id = ?
                            ");
                            $stmt_update->bind_param("i", $user["id"]);
                            $stmt_update->execute();
                            $stmt_update->close();

                            header("Location: index.php");
                            exit;

                        } else {
                            $general_error = "Invalid account role assigned.";
                        }

                    } else {

                        $password_error = "Incorrect password.";

                    }

                } else {

                    $email_error = "No account was found with this e-mail.";

                }

            }

            $stmt->close();
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