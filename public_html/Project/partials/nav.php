<?php
//Note: this is to resolve cookie issues with port numbers
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
session_set_cookie_params([
    "lifetime" => 60 * 60,
    "path" => "/Project",
    //"domain" => $_SERVER["HTTP_HOST"] || "localhost",
    "domain" => $domain,
    "secure" => true,
    "httponly" => true,
    "samesite" => "lax"
]);
session_start();
require_once(__DIR__ . "/../lib/functions.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link href="//cdn.jsdelivr.net" rel="preconnect">
    <title>Lucas' Bank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

	<!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                <?php if (!is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="/Project/home.php">Home</a></li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="accounts.php" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Accounts
                        </a>
                        <div class="dropdown-menu" aria-labelledby="accountsDropdown">
                        <a class="dropdown-item" href="/Project/accounts.php">View Accounts</a>
                            <a class="dropdown-item" href="/Project/account_create.php">Create Account</a>
                            <a class="dropdown-item" href="/Project/loan_create.php">Take out Loan</a>
                            <a class="dropdown-item" href="/Project/account_close.php">Close Account</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="transactions.php" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Transaction
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="/Project/transactions.php?type=deposit">Deposit</a>
                            <a class="dropdown-item" href="/Project/transactions.php?type=withdraw">Withdraw</a>
                            <a class="dropdown-item" href="/Project/transactions.php?type=transfer">Transfer</a>
                            <a class="dropdown-item" href="/Project/transaction_ext.php">External Transaction</a>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="/Project/profile.php">Profile</a></li>
                    <?php if (has_role("Admin")): ?>
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="transactions.php" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Admin
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                <a class="dropdown-item" href="/Project/admin/user_lookup.php">View User</a>
                                <a class="dropdown-item" href="/Project/admin/ad_account_create.php">Create Account</a>
                                <a class="dropdown-item" href="/Project/admin/ad_account_lookup.php">View Account</a>
                                <a class="dropdown-item" href="/Project/admin/transaction_create.php">Create Transaction</a>
                                <a class="dropdown-item" href="/Project/admin/transaction_lookup.php">View Transactions</a>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link"  href="/Project/logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">