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

nav {
    margin: 0;
    background-color: #292929;
    overflow: hidden;
}

nav {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    width: 100%;
    display: table;
    margin: 0 auto;
}

nav a {
    position: relative;
    width: 33.333%;
    display: table-cell;
    text-align: center;
    color: #949494;
    text-decoration: none;
    font-family: Verdana, Geneva, Tahoma, sans-serif;
    font-weight: bold;
    padding: 10px 20px;
    transition: 0.2s ease color;
}

nav a:before, nav a:after {
    content: "";
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    transition: 0.2s ease transform;
}

nav a:before {
    top: 0;
    left: 10px;
    width: 6px;
    height: 6px;
}

nav a:after {
    top: 5px;
    left: 18px;
    width: 4px;
    height: 4px
}

nav a:nth-child(1):before {
    background-color: yellow;
}

nav a:nth-child(1):after {
    background-color: red;
}

nav a:nth-child(2):before {
    background-color: #00e2ff;
}

nav a:nth-child(2):after {
    background-color: #89ff00;
}

nav a:nth-child(3):before {
    background-color: purple;
}

nav a:nth-child(3):after {
    background-color: palevioletred;
}

#indicator {
    position: absolute;
    left: 5%;
    bottom: 0;
    width: 30px;
    height: 3px;
    background-color: #fff;
    border-radius: 5px;
    transition: 0.2s ease left;
}

nav a:hover {
    color: #fff;
}

nav a:hover:before, nav a:hover:after {
    transform: scale(1);
}

nav a:nth-child(1):hover ~ #indicator {
    background: linear-gradient(130deg, yellow, red);
}

nav a:nth-child(2):hover ~ #indicator {
    left: 34%;
    background: linear-gradient(130deg, #00e2ff, #89ff00);
}

nav a:nth-child(3):hover ~ #indicator {
    left: 70%;
    background: linear-gradient(130deg, purple, palevioletred);
}

#ytd-url {
  display: block;
  position: fixed;
  right: 0;
  bottom: 0;
  padding: 10px 14px;
  margin: 20px;
  color: #fff;
  font-family: Arial;
  font-size: 14px;
  text-decoration: none;
  background-color: #000;
  border-radius: 4px;
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
  z-index: 125;
}
</style>
        <nav>
            <?php if (is_logged_in()) : ?>
                <a href="home.php">Home</a>
            <?php endif; ?>
            <?php if (!is_logged_in()) : ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <?php if (has_role("Admin")) : ?>
                <a href="#">Admin Stuff</a>
            <?php endif; ?>
            <?php if (is_logged_in()) : ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
            <div id="indicator"></div>
        </nav>
