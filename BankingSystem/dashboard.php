<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['Username'];
$role = $_SESSION['Role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Dashboard | Banking System</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ccf0f7;
        margin: 0;
        padding: 0;
        color: #0a3d62;
    }
    header {
        background-color: #ca7feb;
        padding: 20px;
        color: white;
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .container {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(10, 61, 98, 0.15);
    }
    h2 {
        margin-top: 0;
        color: #0a3d62;
        font-weight: 700;
    }
    .welcome {
        margin-bottom: 30px;
        font-size: 18px;
    }
    .nav-links {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .nav-links a {
        display: inline-block;
        background-color: #ca7feb;
        color: white;
        text-decoration: none;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(202,127,235,0.4);
        transition: background-color 0.3s ease-in-out;
        min-width: 160px;
        text-align: center;
    }
    .nav-links a:hover {
        background-color: #b46ee1;
    }
    footer {
        text-align: center;
        margin: 40px 0 20px;
        color: #6c7a89;
        font-size: 14px;
    }
</style>
</head>
<body>

<header><h2> Banking System </h2></header>

<div class="container">
    <p class="welcome"><center><h2> Welcome</h2></center></p>
    
    <div class="nav-links">
        <a href="branches.php">Manage Branches</a>
        <a href="customers.php">Manage Customers</a>
        <a href="accounts.php">Manage Accounts</a>
        <a href="transactions.php">View Transactions</a>
        <a href="logout.php" style="background-color: #d32f2f; box-shadow: 0 4px 6px rgba(211,47,47,0.5);">Logout</a>
    </div>
</div>


</body>
</html>
