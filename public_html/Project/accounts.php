<?php
ob_start();
require_once __DIR__ . "/../../partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$db = getDB();
$user = get_user_id();
$stmt = $db->prepare(
  "SELECT Accounts.id, account_number, account_type, balance, modified, APY
  FROM Accounts
  JOIN Users ON Accounts.user_id = Users.id
  WHERE Users.id = :q AND active = 1
  ORDER BY Accounts.id"
);
$r = $stmt->execute([":q" => $user]);
if ($r) {
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $results = [];
  flash("There was a problem fetching the results");
}
ob_end_flush();
?>
    <br>
    <h3 class="text-center mt-4 mb-4">Accounts</h3>

    <?php if (count($results) > 0): ?>
      <table class="table table-striped">
        <thead class="thead-dark">
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
            <th scope="row"><?php se($r["account_number"]); ?></th>
            <td><?php se(ucfirst($r["account_type"])); ?>
            <?php if ($r["APY"] != 0): ?>
              <br><small>APY: <?php se($r["APY"]); ?>%</small>
            <?php endif; ?>
            </td>
            <td>$<?php se(abs($r["balance"])); ?><br><small>As of <?php se($r["modified"]); ?></small></td>
            <td><a href="transactions_history.php?id=<?php se($r["id"]); ?>" class="btn btn-success">Transactions</a></td>
          </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You don't have any accounts.</p>
    <?php endif; ?>

<?php require __DIR__ . "/../../partials/flash.php"; ?>