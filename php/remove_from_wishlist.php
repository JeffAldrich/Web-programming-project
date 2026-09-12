<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?required=wishlist");
    exit;
}

 $user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| REQUEST CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user%20details/wishlist.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET WISHLIST ITEM ID
|--------------------------------------------------------------------------
*/

 $wishlist_id = isset($_POST["wishlist_id"]) ? (int) $_POST["wishlist_id"] : 0;

if ($wishlist_id <= 0) {
    header("Location: ../user%20details/wishlist.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE (only if it belongs to THIS user — security!)
|--------------------------------------------------------------------------
*/

 $stmt = $conn->prepare("
    DELETE FROM wishlist
    WHERE id = ?
    AND user_id = ?
");

 $stmt->bind_param("ii", $wishlist_id, $user_id);
 $stmt->execute();
 $stmt->close();


/*
|--------------------------------------------------------------------------
| BACK TO WISHLIST
|--------------------------------------------------------------------------
*/

header("Location: ../user%20details/wishlist.php");
exit;

?>