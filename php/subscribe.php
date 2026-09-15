<?php

session_start();

require_once "db.php";

header("Content-Type: application/json");

 $email = trim($_POST["email"] ?? "");

if ($email === "") {
    echo json_encode(["success" => false, "message" => "Please enter your e-mail."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid e-mail address."]);
    exit;
}


/*
|--------------------------------------------------------------------------
| DUPLICATE CHECK
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1");
 $stmt->bind_param("s", $email);
 $stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    echo json_encode(["success" => true, "already" => true, "email" => $email, "message" => "This email is already subscribed"]);
    exit;
}
 $stmt->close();


/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
 $stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "already" => false, "email" => $email, "message" => "Subscribed"]);
} else {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again."]);
}

 $stmt->close();