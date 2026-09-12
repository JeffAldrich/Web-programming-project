<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

 $redirect = "../user%20details/my%20account.php?tab=security";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $redirect);
    exit;
}

 $user_id = (int) $_SESSION["user_id"];

 $current = $_POST["current_password"] ?? "";
 $new     = $_POST["new_password"] ?? "";
 $confirm = $_POST["confirm_password"] ?? "";


/* VERIFY CURRENT PASSWORD */

 $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$user || !password_verify($current, $user["password"])) {
    header("Location: " . $redirect . "&error=wrongpass");
    exit;
}


/* VALIDATE NEW PASSWORD */

if (strlen($new) < 8) {
    header("Location: " . $redirect . "&error=shortpass");
    exit;
}

if ($new !== $confirm) {
    header("Location: " . $redirect . "&error=mismatch");
    exit;
}


/* SAVE */

 $hash = password_hash($new, PASSWORD_DEFAULT);

 $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
 $stmt->bind_param("si", $hash, $user_id);
 $stmt->execute();
 $stmt->close();

header("Location: " . $redirect . "&updated=password");
exit;
