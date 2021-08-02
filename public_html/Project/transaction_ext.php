<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

if (isset($_GET["type"])) {
  $type = $_GET["type"];
} else {
  $type = 'deposit';
}

// init db
$user = get_user_id();
$db = getDB();

// Get user accounts
$stmt = $db->prepare(
  "SELECT id, account_number, account_type, balance
  FROM Accounts
  WHERE user_id = :id AND account_type NOT LIKE 'loan' AND active = 1 AND frozen = 'false'
  ORDER BY id ASC
");
$stmt->execute([':id' => $user]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  $account_src = $_POST["account_src"];
  $balance = $_POST["balance"];
  $memo = $_POST["memo"];

  $lname = $_POST["lname"];
  $last_four = $_POST["last_four"];

  if(strlen($last_four) != 4){
    flash("Please enter last 4 digits of the destination account.");
    die(header("Location: transaction_out.php"));
  }

  $stmt = $db->prepare(
    "SELECT Accounts.id, Users.username, account_type, frozen
    FROM Accounts
    JOIN Users ON Accounts.user_id = Users.id
    WHERE Users.lname = :lname
    AND Accounts.account_number LIKE :last_four
    AND active = 1 AND frozen = 'false'
  ");
  $stmt->execute([
    ':lname' => $lname,
    ':last_four' => "%$last_four"
  ]);
  $account_dest = $stmt->fetch(PDO::FETCH_ASSOC);

  if($account_src == $account_dest["id"] || $account_dest["username"] == get_username()) {
    flash("Cannot transfer to the same user!");
    die(header("Location: transaction_out.php"));
  }
  if($account_dest["account_type"] == "loan") {
    flash("Cannot transfer to a loan account!");
    die(header("Location: transaction_out.php"));
  }

  $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
  $stmt->execute([':id' => $account_src]);
  $acct = $stmt->fetch(PDO::FETCH_ASSOC);
  if($acct["balance"] < $balance) {
    flash("Not enough funds to transfer!");
    die(header("Location: transaction_ext.php"));
  }
  $r = changeBalance($db, $account_src, $account_dest["id"], 'ext-transfer', $balance, $memo);
  
  if ($r) {
    flash("Successfully executed transaction.");
  } else {
    flash("Error doing transaction!");
  }
}
ob_end_flush();
?>

<h3 class="text-center mt-4">Transfer to a Different User</h3>

<form method="POST">
  <?php if (count($results) > 0): ?>
  <div class="form-group">
    <label for="account">Account Source</label>
    <select class="form-control" id="account" name="account_src">
      <?php foreach ($results as $r): ?>
      <option value="<?php se($r["id"]); ?>">
        <?php se($r["account_number"]); ?> | <?php se($r["account_type"]); ?> | <?php se($r["balance"]); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-sm">
      <div class="form-group">
        <label for="lname">Last Name of Destination User</label>
        <input type="text" class="form-control" id="lname" name="lname" maxlength="60" required placeholder="Last Name">
      </div>
    </div>
    <div class="col-sm">
      <div class="form-group">
        <label for="last_four">Last 4 Digits of Destination User</label>
        <input type="number" class="form-control" id="last_four" name="last_four" min="0" max="9999" required placeholder="XXXX">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="deposit">Amount</label>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text">$</span>
      </div>
      <input type="number" class="form-control" id="deposit" min="0.00" name="balance" step="0.01" placeholder="0.00"/>
    </div>
  </div>
  <div class="form-group">
    <label for="memo">Memo</label>
    <textarea class="form-control" id="memo" name="memo" maxlength="250"></textarea>
  </div> <br>
  <button type="submit" name="save" value="Do Transaction" class="btn btn-success">Do Transaction</button>
</form>

<?php require __DIR__ . "/partials/flash.php"; ?>