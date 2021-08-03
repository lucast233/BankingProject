<?php
require_once(__DIR__ . "/partials/nav.php");
if (isset($_POST["submit"])) {
    $email = se($_POST, "email", null, false);
    $password = trim(se($_POST, "password", null, false));
    $username = trim(se($_POST, "username", null, false));
    $isValid = true;
    $db = getDB();

    if ($is_active === 0) {
        flash("Sorry, your account is no longer active", "danger");
        $isValid = false;
    }
    if (!isset($email) || !isset($password)) {
        flash("Must provide email and password", "warning");
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
        $stmt = $db->prepare("SELECT id, email, fname, lname, is_active, IFNULL(username, email) as `username`, password from Users where (email = :email or username = :username) AND is_active=1  LIMIT 1");
        try {
            $stmt->execute([":email" => $email, ":username" => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $upass = $user["password"];
                if (password_verify($password, $upass)) {
                    flash("Login successful", "success");
                    unset($user["password"]);
                    $_SESSION["user"] = $user;
                    $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                    JOIN UserRoles on Roles.id = UserRoles.role_id 
                    where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                    $stmt->execute([":user_id" => $user["id"]]);
                    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($roles) {
                        $_SESSION["user"]["roles"] = $roles;
                    } else {
                        $_SESSION["user"]["roles"] = [];
                    }
                    die(header("Location: home.php"));
                } else {
                    se("Passwords don't match");
                }
            } 
            elseif ($is_active == 0) {
                flash("Sorry your account is no longer active. :( ", "warning");
                die(header("Location: login.php"));
            }
            else {
                se("User doesn't exist");
            }
        } catch (Exception $e) {
            echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
        }
    }
}
?>
<style>
    text {
        color:black;
    }
</style>
<?php
require_once(__DIR__ . "/partials/formstyles.php");
?>
<div class="container"> <br>
    <h1>Login</h1>
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
                <label for="pw">Password: </label>
            </div>
            <div class="col-75">
                <input type="password" id="pw" name="password" required />
            </div>
        </div>
        <div class="row">
            <input type="submit" name="submit" value="Login" />
        </div>
    </form>
</div>
<script>
    function validate(form) {
        let email = form.email.value;
        let password = form.password.value;
        let isValid = true;
        if (email) {
            email = email.trim();
        }
        if (password) {
            password = password.trim();
        }
        if (email.indexOf("@") === -1) {
            isValid = false;
            alert("Invalid email");
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