<?php
require_once __DIR__ . "/partials/nav.php";
if (isset($_POST["submit"])) {
  $email = null;
  $password = null;
  $confirm = null;
  $username = null;
  $fname = null;
  $lname = null;
  if (isset($_POST["email"])) {
    $email = $_POST["email"];
  }
  if (isset($_POST["password"])) {
    $password = $_POST["password"];
  }
  if (isset($_POST["confirm"])) {
    $confirm = $_POST["confirm"];
  }
  if (isset($_POST["username"])) {
    $username = $_POST["username"];
  }
  if (isset($_POST["fname"])) {
    $fname = $_POST["fname"];
  }
  if (isset($_POST["lname"])) {
    $lname = $_POST["lname"];
  }
  $isValid = true;
  //check if passwords match on the server side
  if ($password != $confirm) {
    flash("Passwords don't match!");
    $isValid = false;
  }
  if (!isset($email) || !isset($password) || !isset($confirm)) {
    $isValid = false;
  }
  //TODO other validation as desired, remember this is the last line of defense
  if ($isValid) {
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $db = getDB();
    if (isset($db)) {
      //here we'll use placeholders to let PDO map and sanitize our data
      $stmt = $db->prepare(
        "INSERT INTO Users(email, username, password, fname, lname) VALUES(:email,:username, :password, :fname, :lname)"
      );
      //here's the data map for the parameter to data
      $params = [
        ":email" => $email,
        ":username" => $username,
        ":password" => $hash,
        ":fname" => $fname,
        ":lname" => $lname
      ];
      $r = $stmt->execute($params);
      $e = $stmt->errorInfo();
      if ($e[0] == "00000") {
        flash("Successfully registered! Please login.");
      } else {
        if ($e[0] == "23000") {
          //code for duplicate entry
          flash("Username or email already exists!");
        } else {
          flash("An error occurred, please try again.");
        }
      }
    }
  } else {
    flash("There was a validation issue.");
  }
}
//safety measure to prevent php warnings
if (!isset($email)) {
  $email = "";
}
if (!isset($username)) {
  $username = "";
}
?>
<?php
require_once(__DIR__ . "/partials/formstyles.php");
?>
<div> <br>
    <h1>Register</h1>
    <form method="POST" onsubmit="return validate(this);">
        <div class="row">
            <div class="col-25">
                <label for="fname">First Name: </label>
            </div>
            <div class="col-25">
            <input type="fname" name="fname" id="fname" required />
            </div>
        </div>
        <div class="row">
            <div class="col-25">
            <label for="lname">Last Name: </label>
            </div>
            <div class="col-25">
                <input type="lname" name="lname" id="lname" required />
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="email">Email: </label>
            </div>
            <div class="col-75">
                <input type="email" id="email" name="email" required />
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="username">Username: </label>
            </div>
            <div class="col-75">
                <input type="username" id="username" name="username" required />
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="pw">Password: </label>
            </div>
            <div class="col-75">
                <input type="password" id="pw" name="password" required />
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="cpw">Confirm Password: </label>
            </div>
            <div class="col-75">
                <input type="password" id="cpw" name="confirm" required />
            </div>
        </div>
        <div class="row">
            <input type="submit" name="submit" value="Register" />
        </div>
    </form>
</div>
<script>
    function validate(form) {
        let email = form.email.value;
        let username = form.username.value;
        let password = form.password.value;
        let confirm = form.confirm.value;
        let isValid = true;
        if (email) {
            email = email.trim();
        }
        if (username) {
            username = username.trim();
        }
        if (password) {
            password = password.trim();
        }
        if (confirm) {
            confirm = confirm.trim();
        }
        if (!username || username.length === 0) {
            isValid = false;
            alert("Must provide a username");
        }
        if (email.indexOf("@") === -1) {
            isValid = false;
            alert("Invalid email");
        }
        if (password !== confirm) {
            isValid = false;
            alert("Passwords don't match");
        }
        if (password.length < 3) {
            isValid = false;
            alert("Password must be 3 or more characters");
        }
        return isValid;
    }
</script>
<?php
require_once(__DIR__ . "/partials/flash.php");
?>