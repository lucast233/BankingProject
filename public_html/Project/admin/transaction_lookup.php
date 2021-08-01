<?php
require_once __DIR__ . "/../partials/nav.php";
if (!has_role("Admin")) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: ../login.php"));
}

$query = "";
$results = [];
if (isset($_POST["query"]) && isset($_POST["account_type"])) {
    $query = $_POST["query"];
    $account_type = $_POST["account_type"];
  }
  if (isset($_POST["search"]) && !empty($query) && !empty($account_type)) {
    $db = getDB();
    $column = $account_type == 'dest' ? 'account_src' : 'account_dest';
    $stmt = $db->prepare(
      "SELECT * from Transactions WHERE " . $column . " = :q LIMIT 10"
    );
    $r = $stmt->execute([":q" => $query]);
    if ($r) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      flash("There was a problem fetching the results");
    }
  }
  ?>
  <br>
  <br>
  <form method="POST">
    <select name="account_type">
  <option value="src">Account Source ID</option>
  <option value="dest">Account Dest ID</option>
  </select>
  <input name="query" placeholder="Account ID" value="<?php se($query); ?>"/>
  <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
<?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php foreach ($results as $r): ?>
            <div class="list-group-item">
                <div>
                    <div>Account Source ID: 
                    <?php se($r["account_src"]); ?></>
                </div>
                <div>
                    <div>Account Destination ID: 
                    <?php se($r["account_dest"]); ?></div>
                </div>
                <div>
                    <div>Amount: 
                     $<?php se($r["balance_change"]); ?></div>
                </div>
                <div>
                    <div>Type: 
                     <?php se($r["transaction_type"]); ?></div>
                </div>
                <div>
                    <div>Memo: 
                     <?php se($r["memo"]); ?></div>
                </div>
                <div>
                    <div>Expected Total: 
                     $<?php se($r["expected_total"]); ?></div>
                </div>
                <div>
                    <div>Created: 
                     <?php se($r["created"]); ?></div>
                </div>
                <div>
                    <a type="button" href="transaction_edit.php?id=<?php se($r['id']); ?>">Edit</a>
                    <a type="button" href="transaction_view.php?id=<?php se($r['id']); ?>">View</a>
                </div>
            </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No results</p>
<?php endif; ?>
</div>

<?php require __DIR__ . "/../partials/flash.php"; ?>