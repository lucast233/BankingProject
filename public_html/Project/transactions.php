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
  "SELECT id, account_number, account_type, balance, frozen
  FROM Accounts
  WHERE user_id = :id AND active = 1 AND frozen = 'false'
  ORDER BY id ASC
");
$stmt->execute([':id' => $user]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  $balance = $_POST["balance"];
  $memo = $_POST["memo"];
  $r = false;
  $frozen = trim(se($_POST, "frozen", null, false));

  if($type == 'deposit') {
    $account = $_POST["account"];
    $r = changeBalance($db, 1, $account, 'deposit', $balance, $memo);
  }
  if($type == 'withdraw')  {
    $account = $_POST["account"];
    $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
    $stmt->execute([':id' => $account]);
    $acct = $stmt->fetch(PDO::FETCH_ASSOC);
    if($acct["balance"] < $balance) {
      flash("Not enough funds to withdraw!");
      die(header("Location: transaction.php?type=withdraw"));
    }
    $r = changeBalance($db, $account, 1, 'withdraw', $balance, $memo);
    }
    if($type == 'transfer')  {
      $account_src = $_POST["account_src"];
      $account_dest = $_POST["account_dest"];
      if($account_src == $account_dest){
        flash("Cannot transfer to same account!");
        die(header("Location: transaction.php?type=transfer"));
      }
      $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
      $stmt->execute([':id' => $account_src]);
      $acct = $stmt->fetch(PDO::FETCH_ASSOC);
      if($acct["balance"] < $balance) {
        flash("Not enough funds to transfer!");
        die(header("Location: transaction.php?type=transfer"));
      }
      $r = changeBalance($db, $account_src, $account_dest, 'transfer', $balance, $memo);
    }
    if ($r) {
      flash("Successfully executed transaction.");
    }
    else {
      flash("Error doing transaction!");
  }
}



ob_end_flush();
require_once(__DIR__ . "/partials/formstyles.php");
?>

<h3 class="text-center mt-4"><?php se(ucfirst($type)) ?></h3>


<div class="container">
<form method="POST">
  <?php if (count($results) > 0): ?>
  <div class="form-group">
    <label for="account"><?php echo $type == 'transfer' ? 'Account Source' : 'Account'; ?></label>
    <select class="form-control" id="account" name="<?php echo $type == 'transfer' ? 'account_src' : 'account'; ?>">
      <?php foreach ($results as $r): ?>
      <?php if ($r["account_type"] != "loan"): ?>
      <option value="<?php se($r["id"]); ?>">
        <?php se($r["account_number"]); ?> | <?php se($r["account_type"]); ?> | $<?php se($r["balance"]); ?>
      </option>
      <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (count($results) > 0 && $type == 'transfer'): ?>
  <div class="form-group">
    <label for="account">Account Destination</label>
    <select class="form-control" id="account" name="account_dest">
      <?php foreach ($results as $r): ?>
      <option value="<?php se($r["id"]); ?>">
        <?php se($r["account_number"]); ?> | <?php se($r["account_type"]); ?> | $<?php se($r["balance"]); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="form-group">
    <label for="deposit">Amount</label>
    <div class="input-group">
        <span class="input-group-text">$</span>
      <input type="number" class="form-control" id="deposit" min="0.00" name="balance" step="0.01" placeholder="0.00"/>
    </div>
  </div>
  <div class="form-group">
    <div?>
    <label for="memo">Memo</label>
      </div>
    <textarea class="form-control" id="memo" name="memo" maxlength="250"></textarea>
  </div> <br>
  <div>
  <input type="submit" name="save" value="Complete Transaction"></input>
  </div>
</form>
</div>
<?php require __DIR__ . "/partials/flash.php"; ?>