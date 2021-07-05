<?php
require_once(__DIR__ . "/../../partials/nav.php");
if (!is_logged_in()) {
    die(header("Location: login.php"));
}
?>
<style>
    .button {
        font: bold 13px Arial;
        text-decoration: none;
        background-color: lightgrey;
        color: black;
        padding: 2px 6px 2px 6px;
        border-top: 1px solid #CCCCCC;
        border-right: 1px solid #333333;
        border-bottom: 1px solid #333333;
        border-left: 1px solid #CCCCCC;
    }
    h3 {
        text-indent: 4;
    }
    p {
        text-indent: 12;
    }
</style>

<div>
    <br>
    <h1>Home</h1>
    <h2>Welcome, <?php se(get_username()); ?>!</h2>
    <hr color="#292F33">
    <h3>Your dashboard, <?php se(get_username()); ?>!</h3>
    <br>
    <p>
        <a class="button" href="account_create.php"> Create Account</a> &nbsp;&nbsp;
        <a class="button" href="accounts.php"> My Accounts</a> &nbsp;&nbsp;
        <a class="button" href="#"> Deposit</a> &nbsp;&nbsp;
        <a class="button" href="#"> Withdraw</a> &nbsp;&nbsp;
        <a class="button" href="#"> Transfer</a> &nbsp;&nbsp;
    </p>
</div>


<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>