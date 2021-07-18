<?php

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

if (isset($_POST["submit"])) {
  $from=date('Y-m-d',strtotime($_POST['from']));
  $to=date('Y-m-d',strtotime($_POST['to']));
  
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
  <br>
<form method="POST">
<div class="container overflow-hidden">
  <div class="row gy-5">
            <div class="col-6">
                <label for="from">Start Date: </label>
            </div>
            <div class="col-6">
                <label for="to">End Date: </label>
            </div>
        </div>
  <div class="row gy-5">
    <div class="col-6">
      <input type="date" id="start" name="from">
    </div>
    <div class="col-6">
      <input type="date" id="end" name="to">
    </div>
  </div>
  <div class="row gy-5">
    <div class=col-6>
    <label for="type">Filter by Transaction type: </label>
      <select name="type" onchange="showUser(this.value)">
        <option value="">--Please choose an option--</option>
        <option value="Withdraw">Withdraw</option>
        <option value="Deposit">Deposit</option>
        <option value="Transfer">Transfer</option>
        <option value="ext-transfer">Ext Transfer</option>
      </select>
    </div>
  </div>
</div>
<div>
  <input type="submit" name="submit" value="Filter" />
</div>
</form>
<br> <br> 
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
  <nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page - 1) < 1 ? "disabled" : ""; ?>">
            <a class="page-link" href="?id=<?php se($id); ?>&page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
        </li>
        <?php for($i = 0; $i < $total_pages; $i++): ?>
          <li class="page-item <?php echo ($page-1) == $i ? "active" : ""; ?>"><a class="page-link" href="?id=<?php se($id); ?>&page=<?php echo ($i + 1); ?>"><?php echo ($i + 1); ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page) >= $total_pages ? "disabled" : ""; ?>">
            <a class="page-link" href="?id=<?php se($id); ?>&page=<?php echo $page + 1; ?>">Next</a>
        </li>
    </ul>
  </nav>
<?php else: ?>
  <p>You don't have any accounts.</p>
<?php endif; ?>

<?php require __DIR__ . "/partials/flash.php"; ?>