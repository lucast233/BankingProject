<?php require_once __DIR__ . "/../partials/nav.php";
if (!has_role("Admin")) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: ../login.php"));
}
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
  $id = $_GET["id"];
}
//fetching
$result = [];
if (isset($id)) {
  $db = getDB();
  $stmt = $db->prepare(
    "SELECT Accounts.id, account_number, account_type, Accounts.created, modified, balance, username, email FROM Accounts
     JOIN Users ON Accounts.user_id = Users.id WHERE Accounts.id = :id"
  );
  $r = $stmt->execute([":id" => $id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$result) {
    $e = $stmt->errorInfo();
    flash($e[2]);
  }
}
?>
<br>
<br>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <h1 class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-secondary"><?php safer_echo($result["id"]); ?></h1>
        </div>
        <div class="card-body">
            <div>
                <p>Account Info: </p>
                <div>Account Number: <?php safer_echo($result["account_number"]); ?></div>
                <div>Account Type: <?php safer_echo($result["account_type"]); ?></div>
                <div>Balance: $<?php safer_echo($result["balance"]); ?></div>
                <div>Last Modified: <?php safer_echo($result["modified"]); ?></div>
                <div>Owner: <?php safer_echo($result["username"]); ?> (<?php safer_echo($result["email"]); ?>)</div>
                <div>Created: <?php safer_echo($result["created"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php
endif;
require __DIR__ . "/../partials/flash.php";
?>