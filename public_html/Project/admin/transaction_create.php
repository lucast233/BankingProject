<?php
require_once __DIR__ . "/../partials/nav.php";
if (!has_role("Admin")) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: ../login.php"));
}

// init db
$user = get_user_id();
$db = getDB();

// Get user accounts
$stmt = $db->prepare('SELECT * FROM Accounts WHERE user_id = :id ORDER BY id ASC');
$stmt->execute([':id' => $user]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  //TODO add proper validation/checks
  $account_src = $_POST["account_src"];
  $account_dest = $_POST["account_dest"];
  $transaction_type = $_POST["transaction_type"];
  $balance = $_POST["balance"];
  $memo = $_POST["memo"];
  
  $r = changeBalance($db, $account_src, $account_dest, $transaction_type, $balance, $memo);

  if ($r) {
    flash("Created successfully with id: " . $db->lastInsertId());
  } else {
    $e = $r->errorInfo();
    flash("Error creating: " . var_export($e, true));
  }
}
?>
<br>
<div class="container">
<form method="POST">
    <div class="form-grooup">
	<label>Account Src ID</label>
<?php if (count($results) > 0): ?>
	<select class="form-control" name="account_src">
    <option value="1">000000000000 | world</option>
  <?php foreach ($results as $r): ?>
    <option value="<?php safer_echo($r["id"]); ?>">
      <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
    </option>
  <?php endforeach; ?>
  </select>
    </div>
<?php endif; ?>
<div class="form-group">
	<label>Account Dest ID</label>
<?php if (count($results) > 0): ?>
	<select class="form-control" name="account_dest">
    <option value="1">000000000000 | world</option>
  <?php foreach ($results as $r): ?>
    <option value="<?php safer_echo($r["id"]); ?>">
      <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
    </option>
  <?php endforeach; ?>
  </select>
</div>
<?php endif; ?>
<div class="form-group">
	<label>Transaction Type</label>
	<select class="form-control" name="transaction_type">
		<option value="deposit">Deposit</option>
		<option value="withdraw">Withdraw</option>
		<option value="transfer">Transfer</option>
	</select>
</div>
<div class="form-group">
	<label>Balance Change</label>
	<input class="form-control" type="number" min="0.00" name="balance" step="0.01"/>
</div>
<div class="form-group">
<label>Memo</label>
	<input class="form-control" type="text" name="memo"/>
</div>
<div class="form-group">
    <br>
    <input class="btn btn-primary mb-3" type="submit" name="save" value="Create"/>
</div>
</form>
</div>
<?php require __DIR__ . "/../partials/flash.php"; ?>