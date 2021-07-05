<?php
    ob_start();
    require_once __DIR__ . "/../../partials/nav.php";
    if (!is_logged_in()) {
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
    }

    if (isset($_POST["save"])) {
    $db = getDB();
    $check = $db->prepare('SELECT account_number FROM Accounts WHERE account_number = :q AND active = 1');
    do {
        $account_number = rand(100000000000, 999999999999);
        $check->execute([':q' => $account_number]);
    } while ( $check->rowCount() > 0 );

    $account_type = $_POST["account_type"];

    $balance = $_POST["balance"];
    if($balance < 5) {
        flash("Minimum balance not deposited.");
        die(header("Location: account_create.php"));
    }

    if($account_type == "savings"){
        $apy = $balance / 10000;
    } else {
        $apy = 0;
    }

    $user = get_user_id();
    $stmt = $db->prepare(
        "INSERT INTO Accounts (account_number, user_id, account_type, balance, APY) VALUES (:account_number, :user, :account_type, :balance, :apy)"
    );
    $r = $stmt->execute([
        ":account_number" => $account_number,
        ":user" => $user,
        ":account_type" => $account_type,
        ":balance" => 0,
        ":apy" => $apy
    ]);
    if ($r) {
        changeBalance($db, 1, $db->lastInsertId(), 'deposit', $balance, 'New account deposit');
        flash("Account created successfully with Number: " . $account_number);
        die(header("Location: accounts.php"));
    } else {
        flash("Error creating account!");
    }
    }
    ob_end_flush();
?>
<style>
    form {
        text-indent: 12;
    }

</style>
<br>
<br>

<h3>&nbsp; Create New Account</h3>

<form method="POST">
    <div>
        <label for="account_type">Account Type</label>
        <select id="account_type" name="account_type">
            <option value="checking">Checking</option>
            <option value="savings">Savings</option>
        </select>
    </div>
    <div>
        <label for="deposit">Deposit</label>
        <div>
            <span>$</span>
            <input type="number" id="deposit" min="5.00" name="balance" step="0.01" placeholder="5.00" aria-describedby="depositHelp"/>
        </div>
        <div>
        <small id="depositHelp">Minimum $5.00 deposit required.</small>
        </div>
    </div>
    <div>
        <button type="submit" name="save" value="create">Create</button>
    </div>
</form>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>