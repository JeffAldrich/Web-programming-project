<?php

session_start();

require_once "../php/db.php";

 $user = null;
 $stats = ["orders" => 0, "history" => 0, "wishlist" => 0, "cart" => 0];

if (isset($_SESSION["user_id"])) {

    $user_id = (int) $_SESSION["user_id"];

    $stmt = $conn->prepare("
        SELECT id, first_name, last_name, email, phone,
               address, city, province, postal_code, created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    function count_rows($conn, $sql, $uid) {
        $s = $conn->prepare($sql);
        $s->bind_param("i", $uid);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        $s->close();
        return (int) $r["c"];
    }

    $stats["orders"]   = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status NOT IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
    $stats["history"]  = count_rows($conn, "SELECT COUNT(*) AS c FROM orders WHERE user_id = ? AND status IN ('Cancelled', 'Delivered', 'Completed')", $user_id);
    $stats["wishlist"] = count_rows($conn, "SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?", $user_id);
    $stats["cart"]     = count_rows($conn, "SELECT COUNT(*) AS c FROM cart_items INNER JOIN cart ON cart_items.cart_id = cart.id WHERE cart.user_id = ?", $user_id);
}

 $tab = $_GET["tab"] ?? "profile";
if ($tab !== "profile" && $tab !== "security") {
    $tab = "profile";
}

 $saved_profile  = isset($_GET["updated"]) && ($_GET["updated"] ?? "") !== "email" && ($_GET["updated"] ?? "") !== "password";
 $saved_email    = ($_GET["updated"] ?? "") === "email";
 $saved_password = ($_GET["updated"] ?? "") === "password";
 $err = $_GET["error"] ?? "";

 $error_messages = [
    "required"     => "Please complete all required fields.",
    "wrongpass"    => "The password you entered is incorrect.",
    "shortpass"    => "New password must be at least 8 characters.",
    "mismatch"     => "New password and confirmation do not match.",
    "invalidemail" => "Please enter a valid e-mail address.",
    "emailtaken"   => "That e-mail is already in use by another account.",
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAC - My Account</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../account.css">
</head>

<body>

<header>

    <a href="../index.php">
        <img src="../Images/Final LOGO.svg" alt="JAC Logo" class="logo">
    </a>

    <nav>
        <a href="../index.php#home">HOME</a>
        <a href="../index.php#about">ABOUT US</a>
        <a href="../index.php#collections">COLLECTION</a>
        <a href="../store.php">STORE</a>
        <a href="../index.php#contact">CONTACT</a>
    </nav>

</header>


<section class="account-page">

    <div class="account-layout">

        <!-- SIDEBAR (always visible, full menu) -->
        <aside class="account-sidebar">

            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo $user ? strtoupper(substr($user["first_name"] ?? "J", 0, 1)) : "👤"; ?>
                </div>
                <p class="sidebar-name">
                    <?php echo $user
                        ? strtoupper(htmlspecialchars(($user["first_name"] ?? "") . " " . ($user["last_name"] ?? "")))
                        : "GUEST"; ?>
                </p>
                <p class="sidebar-email">
                    <?php echo $user ? htmlspecialchars($user["email"] ?? "") : "Not logged in"; ?>
                </p>
            </div>

            <nav class="sidebar-nav">
                <a href="my%20account.php?tab=profile" class="<?php echo ($user && $tab === "profile") ? "active" : ""; ?>">
                    PROFILE
                </a>
                <a href="my%20account.php?tab=security" class="<?php echo ($user && $tab === "security") ? "active" : ""; ?>">
                    SECURITY
                </a>

                <a href="my%20order.php">
                    MY ORDERS <?php if ($user): ?><span class="sidebar-count"><?php echo $stats["orders"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20order.php?view=history">
                    MY HISTORY <?php if ($user): ?><span class="sidebar-count"><?php echo $stats["history"]; ?></span><?php endif; ?>
                </a>
                <a href="my%20cart.php">
                    MY CART <?php if ($user): ?><span class="sidebar-count"><?php echo $stats["cart"]; ?></span><?php endif; ?>
                </a>
                <a href="wishlist.php">
                    WISHLIST <?php if ($user): ?><span class="sidebar-count"><?php echo $stats["wishlist"]; ?></span><?php endif; ?>
                </a>
            </nav>

            <div class="sidebar-logout">
                <?php if ($user): ?>
                    <a href="../logout.php">LOG OUT</a>
                <?php else: ?>
                    <a href="../login.php">LOG IN</a>
                <?php endif; ?>
            </div>

        </aside>


        <!-- CONTENT -->
        <div class="account-content">

            <?php if ($user): ?>

                <?php if ($err !== "" && isset($error_messages[$err])) { ?>
                    <div class="account-error"><?php echo $error_messages[$err]; ?></div>
                <?php } ?>

                <?php if ($saved_profile) { ?>
                    <div class="account-success">Your profile has been updated successfully.</div>
                <?php } ?>

                <?php if ($saved_email) { ?>
                    <div class="account-success">Your e-mail has been updated successfully.</div>
                <?php } ?>

                <?php if ($saved_password) { ?>
                    <div class="account-success">Your password has been changed successfully.</div>
                <?php } ?>


                <?php if ($tab === "profile"): ?>

                    <!-- STATS -->
                    <div class="account-stats">
                        <div class="account-stat">
                            <strong><?php echo $stats["orders"]; ?></strong>
                            <span>ORDERS</span>
                        </div>
                        <div class="account-stat">
                            <strong><?php echo $stats["history"]; ?></strong>
                            <span>HISTORY</span>
                        </div>
                        <div class="account-stat">
                            <strong><?php echo $stats["wishlist"]; ?></strong>
                            <span>WISHLIST</span>
                        </div>
                        <div class="account-stat">
                            <strong><?php echo $stats["cart"]; ?></strong>
                            <span>IN CART</span>
                        </div>
                    </div>

                    <!-- PROFILE FORM -->
                    <div class="account-section-card">

                        <div class="account-section-head">
                            <h3>PERSONAL INFORMATION</h3>
                            <p>Keep your details up to date for a smooth checkout.</p>
                        </div>

                        <form action="../php/update_profile.php" method="POST" class="account-form">

                            <div class="account-field">
                                <label for="first_name">FIRST NAME</label>
                                <input type="text" id="first_name" name="first_name"
                                    value="<?= htmlspecialchars($user["first_name"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field">
                                <label for="last_name">LAST NAME</label>
                                <input type="text" id="last_name" name="last_name"
                                    value="<?= htmlspecialchars($user["last_name"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field">
                                <label for="email">E-MAIL</label>
                                <input type="email" id="email"
                                    value="<?= htmlspecialchars($user["email"] ?? ""); ?>" readonly>
                            </div>

                            <div class="account-field">
                                <label for="phone">PHONE</label>
                                <input type="text" id="phone" name="phone"
                                    value="<?= htmlspecialchars($user["phone"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field account-field-full">
                                <label for="address">ADDRESS</label>
                                <input type="text" id="address" name="address"
                                    value="<?= htmlspecialchars($user["address"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field">
                                <label for="city">CITY</label>
                                <input type="text" id="city" name="city"
                                    value="<?= htmlspecialchars($user["city"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field">
                                <label for="province">PROVINCE</label>
                                <input type="text" id="province" name="province"
                                    value="<?= htmlspecialchars($user["province"] ?? ""); ?>" required>
                            </div>

                            <div class="account-field">
                                <label for="postal_code">POSTAL CODE</label>
                                <input type="text" id="postal_code" name="postal_code"
                                    value="<?= htmlspecialchars($user["postal_code"] ?? ""); ?>" required>
                            </div>

                            <button type="submit" class="save-account-button">SAVE CHANGES</button>

                        </form>

                    </div>

                <?php else: ?>

                    <!-- CHANGE EMAIL -->
                    <div class="account-section-card security-card">

                        <div class="account-section-head">
                            <h3>CHANGE E-MAIL</h3>
                            <p>Confirm your password to change your e-mail address.</p>
                        </div>

                        <form action="../php/update_email.php" method="POST" class="account-form-plain">

                            <div class="account-field">
                                <label for="new_email">NEW E-MAIL</label>
                                <input type="email" id="new_email" name="new_email"
                                    placeholder="new@email.com" required>
                            </div>

                            <div class="account-field">
                                <label for="email_pass">CURRENT PASSWORD</label>
                                <input type="password" id="email_pass" name="password"
                                    autocomplete="current-password" required>
                            </div>

                            <button type="submit" class="save-account-button">UPDATE E-MAIL</button>

                        </form>

                    </div>

                    <!-- CHANGE PASSWORD -->
                    <div class="account-section-card security-card" style="margin-top: 30px;">

                        <div class="account-section-head">
                            <h3>CHANGE PASSWORD</h3>
                            <p>Use at least 8 characters for your new password.</p>
                        </div>

                        <form action="../php/change_password.php" method="POST" class="account-form-plain">

                            <div class="account-field">
                                <label for="current_password">CURRENT PASSWORD</label>
                                <input type="password" id="current_password" name="current_password"
                                    autocomplete="current-password" required>
                            </div>

                            <div class="account-field">
                                <label for="new_password">NEW PASSWORD</label>
                                <input type="password" id="new_password" name="new_password"
                                    autocomplete="new-password" minlength="8" required>
                            </div>

                            <div class="account-field">
                                <label for="confirm_password">CONFIRM NEW PASSWORD</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                    autocomplete="new-password" minlength="8" required>
                            </div>

                            <button type="submit" class="save-account-button">UPDATE PASSWORD</button>

                        </form>

                    </div>

                <?php endif; ?>

            <?php else: ?>

                <!-- LOGGED OUT -->
                <div class="logged-out-card">

                    <div class="logged-out-avatar">👤</div>

                    <h2>NOT LOGGED IN</h2>

                    <p>
                        Log in to manage your account, orders, cart, and wishlist.
                        Don't have an account yet? Create one for free.
                    </p>

                    <div class="logged-out-actions">

                        <a href="../login.php">LOGIN</a>

                        <a href="../register.php" class="secondary">REGISTER</a>

                    </div>

                </div>

            <?php endif; ?>

        </div><!-- /account-content -->

    </div>

</section>


<footer>
    <div class="footer-bottom">
        <p>© 2026 All Rights Reserved.</p>
        <div class="footer-right">
            <a href="../legal/privacy.html">PRIVACY POLICY</a>
            <a href="../legal/terms.html">TERMS OF USE</a>
            <div class="footer-social">
                <a href="#"><img src="../images/instagram.png" alt="Instagram"></a>
                <a href="#"><img src="../images/twitter.png" alt="Twitter"></a>
                <a href="#"><img src="../images/facebook.png" alt="Facebook"></a>
            </div>
        </div>
    </div>
</footer>


<script src="../script.js?v=3"></script>

</body>

</html>