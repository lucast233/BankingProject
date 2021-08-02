<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  flash("You must be logged in to access this page");
  die(header("Location: login.php"));
}

$db = getDB();
$privacy = get_privacy();
//save data if we submitted the form
if (isset($_POST["saved"])) {
  $isValid = true;
  //check if our email changed
  $newEmail = get_user_email();
  $email = se($_POST,"email",null,false);
  if (get_user_email() != $email) {
    //TODO we'll need to check if the email is available
    $stmt = $db->prepare(
      "SELECT COUNT(1) as InUse from Users where email = :email"
    );
    $stmt->execute([":email" => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $inUse = 1; //default it to a failure scenario
    if ($result && isset($result["InUse"])) {
      try {
        $inUse = intval($result["InUse"]);
      } catch (Exception $e) {
      }
    }
    if ($inUse > 0) {
      flash("Email already in use");
      //for now we can just stop the rest of the update
      $isValid = false;
    } else {
      $newEmail = $email;
    }
  }
  $newUsername = get_username();
  if (get_username() != $_POST["username"]) {
    $username = $_POST["username"];
    $stmt = $db->prepare(
      "SELECT COUNT(1) as InUse from Users where username = :username"
    );
    $stmt->execute([":username" => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $inUse = 1; //default it to a failure scenario
    if ($result && isset($result["InUse"])) {
      try {
        $inUse = intval($result["InUse"]);
      } catch (Exception $e) {
      }
    }
    if ($inUse > 0) {
      flash("Username already in use!");
      //for now we can just stop the rest of the update
      $isValid = false;
    } else {
      $newUsername = $username;
    }
  }
  if ($isValid) {
    $stmt = $db->prepare(
      "UPDATE Users set email = :email, username = :username, fname = :fname, lname = :lname, privacy = :privacy where id = :id"
    );
    $r = $stmt->execute([
      ":email" => $newEmail,
      ":username" => $newUsername,
      ":id" => get_user_id(),
      ":fname" => $_POST["fname"],
      ":lname" => $_POST["lname"],
      ":privacy" => $_POST["privacy"]
    ]);
    if ($r) {
      flash("Updated profile");
    } else {
      flash("Error updating profile");
    }
    if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
      if ($_POST["password"] == $_POST["confirm"]) {
        $password = $_POST["password"];
        $hash = password_hash($password, PASSWORD_BCRYPT);
        //this one we'll do separate
        $stmt = $db->prepare(
          "UPDATE Users set password = :password where id = :id"
        );
        $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
        if ($r) {
          flash("Reset Password.");
        } else {
          flash("Error resetting password!");
        }
      }
    }
    //fetch/select fresh data in case anything changed
    $stmt = $db->prepare(
      "SELECT email, username, fname, lname, privacy from Users WHERE id = :id LIMIT 1"
    );
    $stmt->execute([":id" => get_user_id()]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
      $email = $result["email"];
      $username = $result["username"];
      //let's update our session too
      $_SESSION["user"]["email"] = $email;
      $_SESSION["user"]["username"] = $username;
      $_SESSION["user"]["fname"] = $result["fname"];
      $_SESSION["user"]["lname"] = $result["lname"];
      $_SESSION["user"]["privacy"] = $result["privacy"];
    }
  } else {
    //else for $isValid, though don't need to put anything here since the specific failure will output the message
  }
}
ob_end_flush();
?>
<div class="fcontainer">
<h3 class="text-center mt-4">Profile</h3>
<form method="POST">
<?php if (get_privacy() == "public"): ?>
  <div>
    <label for="email">Email Address</label>
    <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?php se(get_user_email()); ?>">
  </div>
  <?php endif; ?>
  <div>
    <label for="username">Username</label>
    <input type="text" class="form-control" id="username" name="username" maxlength="60" value="<?php se(get_username()); ?>">
  </div>
  <div>
    <label for="fname">First Name</label>
    <input type="fname" class="form-control" id="fname" name="fname" maxlength="60" value="<?php se(get_fname());  ?>">
  </div>
  <div>
    <label for="lname">Last Name</label>
    <input type="lname" class="form-control" id="lname" name="lname" maxlength="60" value="<?php se(get_lname());  ?>">
  </div>
<div>
    <label for="privacy">Privacy</label>
    <select class="form-control" id="privacy" name="privacy">
      <option value="public" <?php echo get_privacy() == "public" ? "selected": ""; ?>>Public</option>
      <option value="private" <?php echo get_privacy() == "private" ? "selected": ""; ?>>Private</option>
	  </select>
    <small class="form-text text-muted">Allow other users to see your profile.</small>
  </div>

  <hr>
  <h4 class="text-center">Change Password</h4>

  <!-- DO NOT PRELOAD PASSWORD-->
<div>
    <label for="password">Password</label>
    <input type="password" class="form-control" id="password" name="password" maxlength="60">
  </div>
<div>
    <label for="confirm">Confirm Password</label>
    <input type="password" class="form-control" id="confirm" name="confirm" maxlength="60">
  </div>
  <br>
  <button type="submit" name="saved" value="Save Profile" class="btn btn-primary">Save Profile</button>
</form>
</div>
<?php se(get_user_email()) ?>

<?php require __DIR__ . "/partials/flash.php"; ?>