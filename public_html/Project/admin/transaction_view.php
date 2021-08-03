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
    "SELECT * FROM Transactions WHERE id = :id"
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
                <p>Transaction Info: </p>
                <div>Account Source: <?php safer_echo($result["account_src"]); ?></div>
                <div>Account Destination: <?php safer_echo($result["account_dest"]); ?></div>
                <div>Amount: $<?php safer_echo($result["balance_change"]); ?></div>
                <div>Type: <?php safer_echo(ucfirst($result["transaction_type"])); ?></div>
                <div>Memo: <?php safer_echo($result["memo"]); ?></div>
                <div>Expected Total: $<?php safer_echo($result["expected_total"]); ?></div>
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