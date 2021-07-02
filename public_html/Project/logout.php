<?php
session_start();
session_unset();
session_destroy();
//setcookie("PHPSESSID", "", time()-3600);
require_once(__DIR__ . "/../../partials/nav.php");
flash("Logged out", "success");
die(header("Location: login.php"));