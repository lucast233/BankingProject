<?php
require_once(__DIR__ . "/../../partials/nav.php");
if (isset($_POST["submit"])) {
    $email = se($_POST, "email", null, false);
    $password = trim(se($_POST, "password", null, false));

    $isValid = true;
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
        //do our registration
        $db = getDB();
        //$stmt = $db->prepare("INSERT INTO Users (email, password) VALUES (:email, :password)");
        //$hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("SELECT id, email, IFNULL(username, email) as 'username', password from Users where email = :email or username = :email LIMIT 1");
        try {
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $upass = $user["password"];
                if (password_verify($password, $upass)) {
                    flash("Login successful", "success");
                    unset($user["password"]);
                    //save user info
                    $_SESSION["user"] = $user;
                    //lookup roles assigned to this user
                    $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                    JOIN UserRoles on Roles.id = UserRoles.role_id 
                    where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                    $stmt->execute([":user_id" => $user["id"]]);
                    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    //save roles or empty array
                    if ($roles) {
                        $_SESSION["user"]["roles"] = $roles;
                    } else {
                        $_SESSION["user"]["roles"] = [];
                    }
                    //echo "<pre>" . var_export($_SESSION, true) . "</pre>";
                    die(header("Location: home.php"));
                } else {
                    se("Passwords don't match");
                }
            } else {
                se("User doesn't exist");
            }
        } catch (Exception $e) {
            echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
        }
    }
}
?>
<?php
require_once(__DIR__ . "/../../partials/formstyles.php");
?>
<div class="container">
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
require_once(__DIR__ . "/../../partials/flash.php");
?>