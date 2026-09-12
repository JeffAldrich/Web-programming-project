<?php

session_start();

/*
    REMOVE ALL SESSION DATA
*/

$_SESSION = [];


/*
    REMOVE THE SESSION COOKIE
*/

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


/*
    DESTROY THE SESSION
*/

session_destroy();


/*
    REDIRECT TO HOME PAGE
*/

header("Location: index.php");
exit;

?>