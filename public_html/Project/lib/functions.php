<?php
require_once(__DIR__ . "/db.php");
function se($v, $k = null, $default = "", $isEcho = true) {
    if (is_array($v) && isset($k) && isset($v[$k])) {
        $returnValue = $v[$k];
    } else if (is_object($v) && isset($k) && isset($v->$k)) {
        $returnValue = $v->$k;
    } else {
        $returnValue = $v;
        //added 07-05-2021 to fix case where $k of $v isn't set
        //this is to kep htmlspecialchars happy
        if (is_array($returnValue) || is_object($returnValue)) {
            $returnValue = $default;
        }
    }
    if (!isset($returnValue)) {
        $returnValue = $default;
    }
    if ($isEcho) {
        //https://www.php.net/manual/en/function.htmlspecialchars.php
        echo htmlspecialchars($returnValue, ENT_QUOTES);
    } else {
        //https://www.php.net/manual/en/function.htmlspecialchars.php
        return htmlspecialchars($returnValue, ENT_QUOTES);
    }
}
function sanitize_email($email = "") {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
function is_valid_email($email = "") {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

//User Helpers
function is_logged_in() {
      return isset($_SESSION["user"]);
}
function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] === $role) {
                return true;
            }
        }
    }
    return false;
}

function safer_echo($var)
{
  if (!isset($var)) {
    echo "";
    return;
  }
  echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}
function get_username() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "username", "", false);
    }
    return "";
}
function get_user_email() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "email", "", false);
    }
    return "";
}
function get_user_id() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "id", false, false);
    }
    return false;
}
function get_fname() {
    if (is_logged_in() && isset($_SESSION["user"]["fname"])) { //we need to check for login first because "user" key may not exist
        return $_SESSION["user"]["fname"];
    }
    return "";
    }
function get_lname(){
    if (is_logged_in() && isset($_SESSION["user"]["lname"])) {
        return $_SESSION["user"]["lname"];
  }
  return "";
}
function get_privacy() {
  if (is_logged_in()) {
    return $_SESSION["user"]["privacy"] ?? null;
  }
  return -1;
}
function getURL($path) {
    if(substr($path, 0, 1) == '/') {
      return $path;
    }
    return $_SERVER['CONTEXT_PREFIX'] . "/IT202450/Project/$path";
  }
//flash message system
function flash($msg = "", $color = "info") {
    $message = ["text" => $msg, "color" => $color];
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $message);
    } else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $message);
    }
}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}
//end flash message system

function changeBalance($db, $src, $dest, $type, $balChange, $memo = '') {
    $stmt = $db->prepare("SELECT balance from Accounts WHERE id = :id");
    $stmt->execute([":id" => $src]);
    $srcAcct = $stmt->fetch(PDO::FETCH_ASSOC);
  
    $stmt->execute([":id" => $dest]);
    $destAcct = $stmt->fetch(PDO::FETCH_ASSOC);
  
    $transactions = $db->prepare(
      "INSERT INTO Transactions (account_src, account_dest, balance_change, transaction_type, memo, expected_total)
      VALUES (:account_src, :account_dest, :balance_change, :transaction_type, :memo, :expected_total)"
    );
    $accounts = $db->prepare(
      "UPDATE Accounts SET balance = :balance WHERE id = :id"
    );
  
    $balChange = abs($balChange);
    $finalSrcBalace = $srcAcct['balance'] - $balChange;
    $finalDestBalace = $destAcct['balance'] + $balChange;
  
    $transactions->execute([
      ":account_src" => $src,
      ":account_dest" => $dest,
      ":balance_change" => -$balChange,
      ":transaction_type" => $type,
      ":memo" => $memo,
      ":expected_total" => $finalSrcBalace
    ]);
  
    $transactions->execute([
      ":account_src" => $dest,
      ":account_dest" => $src,
      ":balance_change" => $balChange,
      ":transaction_type" => $type,
      ":memo" => $memo,
      ":expected_total" => $finalDestBalace
    ]);
  
    $accounts->execute([":balance" => $finalSrcBalace, ":id" => $src]);
    $accounts->execute([":balance" => $finalDestBalace, ":id" => $dest]);
  
    return $transactions;
  }
  function pagination_filter($newPage) {
    $_GET["page"] = $newPage;
    return se(http_build_query($_GET));
    }
  function paginate($query, $params = [], $per_page = 5) {
    global $total_records;
    global $page;
    $page = se($_GET, "page", 1, false);
    if ($page < 1) {
        $page = 1;
    }
    $db = getDB();
    $t_query = "SELECT count(1) as `total` FROM " . explode(" FROM ", $query)[1];
    $stmt = $db->prepare($t_query);
    try {
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $total_records = (int)se($result, "total", 0, false);
        }
    } catch (PDOException $e) {
        error_log("Error getting total records: " . var_export($e->errorInfo, true));
    }
    $offset = ($page - 1) * $per_page;
    $query .= " LIMIT :offset, :limit";
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $stmt = $db->prepare($query);
    $results = [];
    try {
        $params[":offset"] = $offset;
        $params[":limit"] = $per_page;
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {

        error_log("Error getting records: " . var_export($e->errorInfo, true));
        flash("There was a problem with your request, please try again", "warning");
    }
    return $results;
}  
?>
  