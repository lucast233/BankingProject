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
<style>
    body {
        margin: 0;
        background-color: #EEEEEE;
        }
    nav ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #292F33;
        position: fixed;
        top: 0;
        width: 100%;
    }
    nav ul li {
        float: left;
    }
    nav ul li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }
    nav ul li a:hover:not(.active) {
        background-color: #111;
    }
    .active {
        background-color: Crimson;
    }
</style>
<head><br></head>
<nav>
    <ul>
        <?php if (is_logged_in()) : ?>
            <li><a href="home.php">Home</a></li>
        <?php endif; ?>
        <?php if (!is_logged_in()) : ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <li><a href="#">Admin Stuff</a></li>
        <?php endif; ?>
        <?php if (is_logged_in()) : ?>
            <li><a href="profile.php">Profile</a></li>
            <li style="float:right"><a href="logout.php">Logout</a></>
        <?php endif; ?>
    </ul>
</nav>
