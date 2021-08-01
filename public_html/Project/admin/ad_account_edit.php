<?php
require_once __DIR__ . "/../partials/nav.php";
if (!has_role("Admin")) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: ../login.php"));
}

//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
  $id = $_GET["id"];
}

//saving
if (isset($_POST["save"])) {
  //TODO add proper validation/checks
  $account_number = $_POST["account_number"];
  $account_type = $_POST["account_type"];
  $frozen = $_POST["frozen"];
  $balance = $_POST["balance"];
  //calc
  $user = get_user_id();
  $db = getDB();
  if (isset($id)) {
		$stmt = $db->prepare(
			"UPDATE Accounts SET account_number=:account_number, account_type=:account_type, frozen=:frozen, balance=:balance WHERE id=:id"
		);
		$r = $stmt->execute([
			":account_number" => $account_number,
			":account_type" => $account_type,
      ":frozen" => $frozen,
			":balance" => $balance,
			":id" => $id,
		]);
    if ($r) {
      flash("Updated successfully with id: " . $id);
    } else {
      $e = $stmt->errorInfo();
      flash("Error updating: " . var_export($e, true));
    }
  } else {
    flash("ID isn't set, we need an ID in order to update");
  }
}

//fetching
$result = [];
if (isset($id)) {
  $id = $_GET["id"];
  $db = getDB();
  $stmt = $db->prepare("SELECT * FROM Accounts WHERE id = :id");
  $r = $stmt->execute([":id" => $id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<br>
<br>
<div class="card">
  <div class="card-title">
   <h1 class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-secondary"><?php safer_echo($result["id"]); ?></h1>
  </div>
  <div class="card-body">
<form method="POST">
    <div class="form-group">
	<label>Account Number: </label>
	<input class="form-control" name="account_number" type="number" max="999999999999" min="100000000000" value="<?php safer_echo($result["account_number"]); ?>"/> 
    </div>
    <div class="form-group">
    <label>Account Type: </label>
	<select class="form-control" name="account_type">
		<option value="checking" <?php echo $result["account_type"] == 'checking' ? 'selected' : ''; ?>>Checking</option>
		<option value="savings" <?php echo $result["account_type"] == 'savings' ? 'selected' : ''; ?>>Savings</option>
		<option value="loan" <?php echo $result["account_type"] == 'loan' ? 'selected' : ''; ?>>Loan</option>
	</select>
    </div>
    <div class="form-group">
    <label>Frozen: </label>
	<select class="form-control" name="frozen">
    <option value="false" <?php echo $result["frozen"] == 'false' ? 'selected' : ''; ?>>False</option>
    <option value="true" <?php echo $result["frozen"] == 'true' ? 'selected' : ''; ?>>True</option>
	</select>
    </div>
    <div class="form-group">
    <label>Balance: </label>
	<input class="form-control" type="number" min="0.00" name="balance" step="0.01" value="<?php safer_echo($result["balance"]); ?>"/>
    </div> <br>
    <input class="btn btn-primary mb-3" type="submit" name="save" value="Create"/>
</form>
  </div>
</div>
<?php require __DIR__ . "/../partials/flash.php"; ?>