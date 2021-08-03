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
  $email = $_POST["email"];
  $username = $_POST["username"];
  $fname = $_POST["fname"];
  $lname = $_POST["lname"];
  $is_active = $_POST["is_active"];

  //calc
  $user = get_user_id();
  $db = getDB();
  if (isset($id)) {
		$stmt = $db->prepare(
			"UPDATE Users SET email=:email, username=:username, fname=:fname, lname=:lname, is_active=:is_active WHERE id=:id"
		);
		$r = $stmt->execute([
			":email" => $email,
			":username" => $username,
      ":fname" => $fname,
			":lname" => $lname,
			":is_active" => $is_active,
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
  $stmt = $db->prepare("SELECT * FROM Users WHERE id = :id");
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
	<label>Email: </label>
	<input class="form-control" name="email" value="<?php safer_echo($result["email"]); ?>"/> 
    </div>
    <div class="form-group">
    <label>Username: </label>
	<input class="form-control" name="username" value="<?php safer_echo($result["username"]); ?>"/> 
    </div>
    <div class="form-group">
    <label>First Name: </label>
	<input class="form-control" name="fname" value="<?php safer_echo($result["fname"]); ?>"/> 
    </div>
    <div class="form-group">
    <label>Last Name: </label>
	<input class="form-control" name="lname" value="<?php safer_echo($result["lname"]); ?>"/> 
    </div>
    <div class="form-group">
    <label>Is Active: </label>
	<select class="form-control" name="is_active">
    <option value="1" <?php echo $result["is_active"] == '1' ? 'selected' : ''; ?>>Active</option>
    <option value="0" <?php echo $result["is_active"] == '0' ? 'selected' : ''; ?>>Not Active</option>
	</select>    
    </div>
    <br>
    <input class="btn btn-primary mb-3" type="submit" name="save" value="Update"/>
</form>
  </div>
</div>
<?php require __DIR__ . "/../partials/flash.php"; ?>