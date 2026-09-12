<?php

require_once "php/db.php";
require_once "validation.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $result = validateUserInput($_POST);
    $errors = $result['errors'];

    if (empty($errors)) {

        $first_name = $result['data']['first_name'];
        $last_name = $result['data']['last_name'];
        $email = $result['data']['email'];
        $password = $result['data']['password'];

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $errors[] = "A system error occurred. Please try again later.";

        } else {

            $stmt->bind_param("s", $email);

            if (!$stmt->execute()) {

                $errors[] = "A system error occurred. Please try again later.";

            } else {

                $result_check = $stmt->get_result();

                if ($result_check->num_rows > 0) {

                    $errors[] = "An account with this email already exists.";

                } else {

                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare("
                        INSERT INTO users (
                            first_name,
                            last_name,
                            email,
                            password
                        )
                        VALUES (?, ?, ?, ?)
                    ");

                    if (!$stmt) {

                        $errors[] = "A system error occurred. Please try again later.";

                    } else {

                        $stmt->bind_param(
                            "ssss",
                            $first_name,
                            $last_name,
                            $email,
                            $hashed_password
                        );

                        if ($stmt->execute()) {

                            $new_id = $conn->insert_id;

                            header(
                                "Location: success.php?id=" . $new_id
                            );

                            exit;

                        } else {

                            $errors[] = "Registration could not be completed. Please try again.";
                        }
                    }
                }
            }
        }
    }
}


/*
    =========================================
    MATCH ERRORS TO THEIR FIELDS
    =========================================
*/

$first_name_error = "";
$last_name_error = "";
$email_error = "";
$password_error = "";
$confirm_password_error = "";
$general_errors = [];

foreach ($errors as $error) {

    if (str_starts_with($error, "First Name")) {

        $first_name_error = $error;

    } elseif (str_starts_with($error, "Last Name")) {

        $last_name_error = $error;

    } elseif (
        str_starts_with($error, "E-mail") ||
        $error === "Enter a valid email address." ||
        $error === "An account with this email already exists."
    ) {

        $email_error = $error;

    } elseif (
        str_starts_with($error, "Password")
    ) {

        $password_error = $error;

    } elseif (
        str_starts_with($error, "Please confirm") ||
        $error === "Passwords do not match."
    ) {

        $confirm_password_error = $error;

    } else {

        $general_errors[] = $error;
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

    <title>Register - JAC</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<section class="register-section">

    <div class="register-container">

        <h1>REGISTER</h1>

        <p class="register-intro">
            Create your account and stay connected.
        </p>


        <?php if (!empty($general_errors)): ?>

            <div class="register-errors">

                <?php foreach ($general_errors as $error): ?>

                    <p>
                        <?= htmlspecialchars($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="register.php"
        >


            <!-- FIRST NAME -->

            <label for="first_name">
                First Name
            </label>

            <input
                type="text"
                id="first_name"
                name="first_name"
                placeholder="Enter your first name"
                value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                minlength="2"
                required
            >

            <?php if ($first_name_error): ?>

                <p class="register-field-error">
                    ⚠ <?= htmlspecialchars($first_name_error) ?>
                </p>

            <?php endif; ?>


            <!-- LAST NAME -->

            <label for="last_name">
                Last Name
            </label>

            <input
                type="text"
                id="last_name"
                name="last_name"
                placeholder="Enter your last name"
                value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                minlength="2"
                required
            >

            <?php if ($last_name_error): ?>

                <p class="register-field-error">
                    ⚠ <?= htmlspecialchars($last_name_error) ?>
                </p>

            <?php endif; ?>


            <!-- EMAIL -->

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your e-mail"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
            >

            <?php if ($email_error): ?>

                <p class="register-field-error">
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

            <p class="password-hint">
                At least 8 characters
            </p>

            <?php if ($password_error): ?>

                <p class="register-field-error">
                    ⚠ <?= htmlspecialchars($password_error) ?>
                </p>

            <?php endif; ?>


            <!-- CONFIRM PASSWORD -->

            <label for="confirm_password">
                Confirm Password
            </label>

            <div class="password-wrapper">

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    onclick="togglePassword('confirm_password', this)"
                    aria-label="Show password"
                >
                    👁
                </button>

            </div>

            <?php if ($confirm_password_error): ?>

                <p class="register-field-error">
                    ⚠ <?= htmlspecialchars($confirm_password_error) ?>
                </p>

            <?php endif; ?>


            <!-- REGISTER BUTTON -->

            <button type="submit">
                REGISTER
            </button>


        </form>


        <p class="login-link">

            Already have an account?

            <a href="login.php">
                LOGIN
            </a>

        </p>

    </div>

</section>


<script src="script.js"></script>

</body>

</html>