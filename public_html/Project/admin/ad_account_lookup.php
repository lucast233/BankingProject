<?php
require_once __DIR__ . "/../partials/nav.php";
if (!has_role("Admin")) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: ../login.php"));
}

$query = "";
$results = [];
if (isset($_POST["query"])) {
  $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
  $db = getDB();
  $stmt = $db->prepare(
    "SELECT Accounts.id, account_number, user_id, account_type, Accounts.created, modified, balance, active, frozen FROM Accounts
     JOIN Users ON Accounts.user_id = Users.id 
     WHERE account_number LIKE :q LIMIT 10"
  );
  $r = $stmt->execute([":q" => "%$query%"]);
  if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    flash("There was a problem fetching the results");
  }
}
?>
<br>
<form method="POST">
    <input name="query" placeholder="Search Account Number" value="<?php safer_echo($query); ?>"/>

    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
<?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php foreach ($results as $r): ?>
            <div class="list-group-item">
                <div>
                    <div>Account Number: 
                    <?php safer_echo($r["account_number"]); ?></div>
                </div>
                <div>
                    <div>Account Type: 
                    <?php safer_echo($r["account_type"]); ?></div>
                </div>
                <div>
                    <div>Last Modified: 
                    <?php safer_echo($r["modified"]); ?></div>
                </div>
                <div>
                    <div>Balance: 
                    <?php safer_echo($r["balance"]); ?></div>
                </div>
                <div>
                    <div>Created: 
                    <?php safer_echo($r["created"]); ?></div>
                </div>
                <div>
                    <div>User ID: 
                    <?php safer_echo($r["user_id"]); ?></div>
                </div>
                <div>
                    <div>Active: 
                        <?php safer_echo($r["active"]); ?></div>
                </div>
                <div>
                    <div>Frozen: 
                    <?php safer_echo($r["frozen"]); ?></div>
                </div>
                <div>
                    <a type="button" href="ad_account_edit.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                    <a type="button" href="ad_account_view.php?id=<?php safer_echo($r['id']); ?>">View</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No results</p>
<?php endif; ?>
</div>

<?php require __DIR__ . "/../partials/flash.php"; ?>