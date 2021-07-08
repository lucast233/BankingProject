<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$results = [];

if (isset($_GET["id"])) {
  $id = $_GET["id"];
  $user = get_user_id();
  if(isset($_GET["page"])){
    $page = (int)$_GET["page"];
  } else {
    $page = 1;
  }
  $db = getDB();

  $stmt = $db->prepare(
    "SELECT count(*) as total
    FROM Transactions
    JOIN Accounts AS Src ON Transactions.account_src = Src.id
    WHERE Transactions.account_src = :acct_id AND Src.user_id = :user
    ORDER BY Transactions.id DESC LIMIT 10"
  );
  $r = $stmt->execute([
    ":acct_id" => $id,
    ":user" => $user
  ]);
  $results = $stmt->fetch(PDO::FETCH_ASSOC);
  if($results){
    $total = (int)$results["total"];
  } else {
    $total = 0;
  }

  $per_page = 10;
  $total_pages = ceil($total / $per_page);
  $offset = ($page - 1) * $per_page;

  $stmt = $db->prepare(
    "SELECT balance_change, transaction_type, memo, expected_total, Transactions.created, 
    Dest.account_number AS dest, 
    Src.account_number AS src
    FROM Transactions
    JOIN Accounts AS Src ON Transactions.account_src = Src.id
    JOIN Accounts AS Dest ON Transactions.account_dest = Dest.id
    WHERE Transactions.account_src = :acct_id AND Src.user_id = :user
    ORDER BY Transactions.id DESC LIMIT :offset, :count"
  );
  $stmt->bindValue(':acct_id', $id);
  $stmt->bindValue(':user', $user);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':count', $per_page, PDO::PARAM_INT);
  $r = $stmt->execute();
  if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $results = [];
    flash("There was a problem fetching the results");
  }
}
?>
<style>
  table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    padding: 10px;

  }
  .pagination {text-align: center;}
  li {
    list-style: none;
    display: inline-block;
    text-align: center;
    margin: 0;
    padding: 15px;
    display: inline;
  }
  tr:nth-child(even) {
  background-color: #dddddd;
  }
</style>
    <br>
    <br>

<?php if (count($results) > 0): ?>
  
  <h3>Transaction History for <?php se(ucfirst(get_username())); ?></h3>
  <a href="accounts.php"> Go Back</a>  <br> <br>
  <table class="table table-striped" style="width: 100%">
    <thead class="thead-dark">
      <tr>  
        <th scope="col">Account Number (Source)</th>
        <th scope="col">Account Number (Destination)</th>
        <th scope="col">Type</th>
        <th scope="col">Balance Change</th>
        <th scope="col">Memo</th>
        <th scope="col">Balance</th>
      </tr>
    </thead>
    <tbody>
  <?php foreach ($results as $r): ?>
      <tr>
        <th><?php se($r["src"]); ?></th>
        <th scope="row"><?php se($r["dest"]); ?></th>
        <td><?php se(ucfirst($r["transaction_type"])); ?></td>
        <td>$<?php se($r["balance_change"]); ?></td>
        <td><?php se($r["memo"]); ?></td>
        <td>$<?php se($r["expected_total"]); ?></td>
      </tr>
  <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <a href="accounts.php"> Go Back</a>  <br> <br>

  <p>You don't have any transactions.</p>
<?php endif; ?>

<?php require __DIR__ . "/partials/flash.php"; ?>