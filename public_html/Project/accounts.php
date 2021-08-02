<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$d_query = 
  "SELECT Accounts.id, account_number, account_type, balance, modified, APY, frozen
  FROM Accounts
  JOIN Users ON Accounts.user_id = Users.id
  WHERE Users.id = :q AND active = 1
  ORDER BY Users.id
  "
;
$params = [];
$params[":q"] = get_user_id();

$per_page = 5;

$results = paginate($d_query, $params, $per_page);

$total_pages = ceil($total_records / $per_page);
$options = [];
$query = "SELECT DISTINCT account_type from Accounts";
$stmt = $db->prepare($query);
try {
  $stmt->execute();
  $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if ($r) {
      $options = $r;
  }
} catch (PDOException $e) {
  error_log("Error getting unique reasons: " . var_export($e->errorInfo, true));
}

ob_end_flush();
?>

    <br>
    <div class="container-fluid">
    <div>
    <h3 class="text-center mt-3 mb-3">Accounts</h3>
    <?php if (count($results) > 0): ?>
      <table class="table table-bordered table-striped table-hover">
        <thead>
          <tr>  
            <th scope="col">Account Number</th>
            <th scope="col">Account Type</th>
            <th scope="col">Balance</th>
            <th scope="col">History</th>
          </tr>
        </thead>
        <tbody>
      <?php foreach ($results as $r): ?>        
          <tr>            
            <td scope="row"><strong><?php se($r["account_number"]); ?></td></strong>
            <td><?php se(ucfirst($r["account_type"])); ?>
            <?php if ($r["APY"] != 0): ?>
              <br><small>APY: <?php se($r["APY"]); ?>%</small>
            <?php endif; ?>
            </td>
            <td>$<?php se(($r["balance"])); ?><br><small>As of <?php se($r["modified"]); ?></small></td>
            <td><a href="transaction_history.php?id=<?php se($r["id"]); ?>" class="btn btn-success">Transactions</a></td>
          </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
<?php else: ?>
  <p>You don't have any accounts.</p>
<?php endif; ?>
  <div>
    <?php include(__DIR__ . "/partials/pagination.php"); ?>
  </div>
</div>
<?php require __DIR__ . "/partials/flash.php"; ?>