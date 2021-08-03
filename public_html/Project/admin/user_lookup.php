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
    "SELECT Users.id, email, created, username, fname, lname, privacy, is_active FROM Users
     WHERE CONCAT(fname, ' ', lname) LIKE :q OR CONCAT(fname,  ' ', lname) LIKE :q LIMIT 10"
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
    <input name="query" placeholder="Search Name" value="<?php safer_echo($query); ?>"/>

    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
<?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php foreach ($results as $r): ?>
            <div class="list-group-item">
                <div>
                    <div>Email: 
                    <?php safer_echo($r["email"]); ?></div>
                </div>
                <div>
                    <div>Username: 
                    <?php safer_echo($r["username"]); ?></div>
                </div>
                <div>
                    <div>Created: 
                    <?php safer_echo($r["created"]); ?></div>
                </div>
                <div>
                    <div>First Name: 
                    <?php safer_echo($r["fname"]); ?></div>
                </div>
                <div>
                    <div>Last Name: 
                    <?php safer_echo($r["lname"]); ?></div>
                </div>
                <div>
                    <div>Is Active: 
                    <?php safer_echo($r["is_active"]); ?></div>
                </div>
                <div>
                    <a type="button" href="user_edit.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
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