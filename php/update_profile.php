<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.php?required=account");
    exit;

}

$user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| ONLY ACCEPT POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../user%20details/my%20account.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$first_name = trim($_POST["first_name"] ?? "");
$last_name = trim($_POST["last_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$city = trim($_POST["city"] ?? "");
$province = trim($_POST["province"] ?? "");
$postal_code = trim($_POST["postal_code"] ?? "");


/*
|--------------------------------------------------------------------------
| CHECK REQUIRED FIELDS
|--------------------------------------------------------------------------
*/

if (
    $first_name === "" ||
    $last_name === "" ||
    $phone === "" ||
    $address === "" ||
    $city === "" ||
    $province === "" ||
    $postal_code === ""
) {

    header(
        "Location: ../user%20details/my%20account.php?error=required"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| UPDATE USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE users
    SET
        first_name = ?,
        last_name = ?,
        phone = ?,
        address = ?,
        city = ?,
        province = ?,
        postal_code = ?
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    die("Database prepare error: " . $conn->error);

}


$stmt->bind_param(
    "sssssssi",
    $first_name,
    $last_name,
    $phone,
    $address,
    $city,
    $province,
    $postal_code,
    $user_id
);


if (!$stmt->execute()) {

    die("Database update error: " . $stmt->error);

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| UPDATE SESSION NAME
|--------------------------------------------------------------------------
*/

$_SESSION["first_name"] = $first_name;
$_SESSION["last_name"] = $last_name;


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

header(
    "Location: ../user%20details/my%20account.php?updated=1"
);

exit;

?>