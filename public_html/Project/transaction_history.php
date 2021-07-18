<?php

require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) 
{
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}
$results = [];
$db = getDB();

if (isset($_GET["id"])) {
  

  $id = $_GET["id"];
  $user = get_user_id();
  
  $filter= [];
  $transaction_type=$_GET['transaction_type'] ?? "";
  $from = $_GET['from'] ?? "";
  $to = $_GET['to'] ?? "";
  if($transaction_type) {
    $filter[] = 'transaction_type=:transaction_type';
  }
  if($from) {
    $filter[] = 'Transactions.created >=:from';
  }
  if($to) {
    $filter[] = 'Transactions.created <=:to';
  }
  if(sizeof($filter)) {
    $filter=' AND '.implode(' AND ',$filter);
  }
  else {
    $filter='';
  }

  if(isset($_GET["page"])) {
    $page = (int)$_GET["page"];
  } 
  else {
    $page = 1;
  }

  $stmt = $db->prepare(
    "SELECT count(*) as total
    FROM Transactions
    JOIN Accounts AS Src ON Transactions.account_src = Src.id
    WHERE Transactions.account_src = :acct_id AND Src.user_id = :user
    "
    .$filter
  
  );
  $stmt->bindValue(":acct_id", $id);
  $stmt->bindValue(":user", $user);
  if($transaction_type) $stmt->bindValue(":transaction_type", $transaction_type,PDO::PARAM_STR);
  if($from)$stmt->bindValue(":from", $from,PDO::PARAM_STR);
  if($to)$stmt->bindValue(":to", $to,PDO::PARAM_STR);
  $r = $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if($result) {
    $total = (int)$result["total"];
  } else {
    $total = 0;
  }

  $per_page = 10;
  $total_pages = ceil($total / $per_page);
  $offset = ($page - 1) * $per_page;
  

  $query= "SELECT SQL_CALC_FOUND_ROWS balance_change, transaction_type, memo, expected_total, Transactions.created, Dest.account_number
  AS dest, Src.account_number AS src
  FROM Transactions
  JOIN Accounts AS Src ON Transactions.account_src = Src.id
  JOIN Accounts AS Dest ON Transactions.account_dest = Dest.id
  WHERE Transactions.account_src = :acct_id AND Src.user_id = :user
  "
  .$filter.
  "
  ORDER BY Transactions.id DESC LIMIT :offset,:count";
  $stmt = $db->prepare($query);
  $stmt->bindValue(":acct_id", $id);
  $stmt->bindValue(":user", $user);
  $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
  $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
  if($transaction_type) $stmt->bindValue(":transaction_type", $transaction_type,PDO::PARAM_STR);
  if($from)$stmt->bindValue(":from", $from,PDO::PARAM_STR);
  if($to)$stmt->bindValue(":to", $to,PDO::PARAM_STR);
  $r = $stmt->execute();
  if ($r) 
  {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else 
  {
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
  <br>
  <form action="" method="GET">
    <div class="date">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>From date</label> 
            <input type="date" name="from" class="form-control" value="<?php echo $from?>">
          </div>
        </div> 
        <div class="col-md-4">
          <div class="form-group">
            <label>To date</label> 
            <input type="date" name="to" class="form-control" value="<?php echo $to ?>">
          </div>
        </div> 
        <div class="col-md-4"> <br>
          <div class="form-group">
            <button type="submit" class="btn btn-primary"> click to filter</button>
            <a  href="?id=<?php echo $id ?>" class="btn btn-info"> Reset</a>
          </div>
        </div> 
      </div>
  </div> <br>
  <div class="typeContainer">
    <label for="transaction_type"> Filter Transaction Type:</label>
      <select id="transaction_type" name="transaction_type">
        <option value="" >Select Type</option>
        <option value="Deposit" <?php echo ($transaction_type=='Deposit'?'SELECTED':'' ) ?>>Deposit</option>
        <option value="Withdraw" <?php echo ($transaction_type=='Withdraw'?'SELECTED':'' ) ?>>Withdraw</option>
        <option value="Ext-transfer" <?php echo ($transaction_type=='Ext-Transfer'?'SELECTED':'' ) ?>>Ext-Transfer</option>
            </select>
  </div>
  <input type="hidden" name="id" value="<?php echo $id ?>" />
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
<?php  
$filter =[
  'from ='.$from,
  'to ='.$to,
  'transaction_type ='.$transaction_type,
];
$filter ='&'.implode('&',$filter);
?>
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