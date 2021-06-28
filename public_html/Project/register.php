<?php
require_once(__DIR__ . "/../../partials/nav.php");
if (isset($_POST["submit"])) {
    $email = se($_POST, "email", null, false);
    $username = trim(se($_POST, "username", null, false));
    $password = trim(se($_POST, "password", null, false));
    $confirm = trim(se($_POST, "confirm", null, false));

    $isValid = true;
    if (!isset($email) || !isset($username) || !isset($password) || !isset($confirm)) {
        flash("Must provide email, username, password, and confirm password", "warning");
        $isValid = false;
    }

    if ($password !== $confirm) {
        flash("Passwords don't match", "warning");
        $isValid = false;
    }
    if (strlen($password) < 3) {
        flash("Password must be 3 or more characters", "warning");
        $isValid = false;
    }
    $email = sanitize_email($email);
    if (!is_valid_email($email)) {
        flash("Invalid email", "warning");
        $isValid = false;
    }
    if ($isValid) {
        //do our registration
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, username, password) VALUES (:email, :username, :password)");
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
        } catch (PDOException $e) {
            $code = se($e->errorInfo, 0, "00000", false);
            if ($code === "23000") {
                flash("An account with this email already exists", "danger");
            } else {
                echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
            }
        }
    }
}
?>

<style>
input[type=text], select, textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  resize: vertical;
}
label {
  padding: 12px 12px 12px 0;
  display: inline-block;
}
input[type=submit] {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  float: right;
}
input[type=submit]:hover {
  background-color: #45a049;
}
.container {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}
.col-25 {
  float: left;
  width: 25%;
  margin-top: 6px;
}
.col-75 {
  float: left;
  width: 35%;
  margin-top: 6px;
}
.row:after {
  content: "";
  display: table;
  clear: both;
}
@media screen and (max-width: 600px) {
  .col-25, .col-75, input[type=submit] {
    width: 100%;
    margin-top: 0;
  }
}
</style>
<div class="container">
    <h1>Register</h1>
    <form method="POST" onsubmit="return validate(this);">
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
require_once(__DIR__ . "/../../partials/flash.php");
?>