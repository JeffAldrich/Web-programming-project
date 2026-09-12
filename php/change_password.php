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

/* ACCOUNT DASHBOARD */
.account-layout{
    display: flex;
    gap: 30px;
    max-width: 1100px;
    margin: 0 auto;
    align-items:
    flex-start;
    }

.account-sidebar {
    width: 270px;
    flex-shrink: 0;
    background-color: black;
    color: white;
    }

.sidebar-user {
    padding: 35px 25px;
    text-align: center;
    border-bottom: 1px solid #333;
    }

.sidebar-avatar {
    width: 70px;
    height: 70px;
    margin: 0 auto 15px;
    border: 1px solid #666;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    }

.sidebar-name {
    font-size: 14px;
    letter-spacing: 1px;
    margin-bottom: 5px;
    }

.sidebar-email {
    font-size: 12px;
    color: #999;
    margin: 0;
    word-break: break-all;
    }

.sidebar-nav {
    display: flex;
    flex-direction: column;
    padding: 15px 0;
    }

.sidebar-nav a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 25px;
    color: #ccc;
    text-decoration: none;
    font-size: 13px;
    letter-spacing: 1px;
    border-left: 3px solid transparent;
    }

.sidebar-nav a:hover {
    color: white;
    background-color: #1a1a1a;
    }

.sidebar-nav a.active {
    color: white;
    background-color:#1a1a1a;
    border-left-color: white;
    }

.sidebar-count {
    min-width: 22px;
    padding: 2px 7px;
    border: 1px solid #555;
    border-radius: 20px;
    font-size: 11px;
    text-align: center;
    }

.sidebar-logout {
    border-top: 1px solid #333;
    padding: 10px 0;
    }

.sidebar-logout a {
    display: block;
    padding: 14px 25px;
    color: #888;
    text-decoration: none;
    font-size: 13px;
    letter-spacing: 1px;
    }

.sidebar-logout a:hover{
    color: white;
    }

.account-content {
    flex: 1;
    min-width: 0;
    }

.account-content
.account-error,
.account-content
.account-success{
    max-width: none;
    margin: 0 0 20px;
    }

.account-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    }

.account-stat {
    flex: 1;
    padding:25px;
    background: white; 
    order: 1px solid #ddd;
    text-align: center;
    }

.account-stat strong {
    display: block;
    font-size: 30px;
    margin-bottom: 5px;
    }

.account-stat span {
    font-size: 11px;
    letter-spacing: 2px;
    color: #777;
    }
.account-section-card {
    width: 100%;
    background-color: white;
    border: 1px solid #ddd;
    }

.account-section-head {
    padding: 25px 40px;
    border-bottom: 1px solid #ddd;
    }

.account-section-head h3 {
    font-size: 15px;
    letter-spacing: 2px;
    margin-bottom: 5px;
    }

.account-section-head p {
    margin: 0;
    font-size: 13px;
    color: #777;
    }

.account-section-card .account-form {
    max-width: none;
    margin: 0;
    border: none;
    }

.account-field-full {
    grid-column: 1 / -1;
    }

.account-form-plain {
    padding: 40px;
    display: flex;
    flex-direction:column;
    gap: 20px;
    max-width: 420px;
    }
    
.account-form-plain .save-account-button {
    width: 100%;
    margin-top: 5px;
    grid-column: auto;
    }

@media (max-width: 900px) {
    .account-layout
    { flex-direction: column; }

    .account-sidebar
    { width: 100%; }

    .account-stats
    { flex-direction: column; }

    .account-section-head,
    .account-form-plain
    { padding: 25px; }
}