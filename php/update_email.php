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

 $user_id   = (int) $_SESSION["user_id"];
 $new_email = trim($_POST["new_email"] ?? "");
 $password  = $_POST["password"] ?? "";


/* VALIDATE EMAIL */

if ($new_email === "" || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . $redirect . "&error=invalidemail");
    exit;
}


/* VERIFY CURRENT PASSWORD */

 $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$user || !password_verify($password, $user["password"])) {
    header("Location: " . $redirect . "&error=wrongpass");
    exit;
}


/* CHECK EMAIL NOT TAKEN */

 $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
 $stmt->bind_param("si", $new_email, $user_id);
 $stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    header("Location: " . $redirect . "&error=emailtaken");
    exit;
}
 $stmt->close();


/* SAVE + UPDATE SESSION */

 $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
 $stmt->bind_param("si", $new_email, $user_id);
 $stmt->execute();
 $stmt->close();

 $_SESSION["email"] = $new_email;

header("Location: " . $redirect . "&updated=email");
exit;