<?php require_once("system/common.php"); ?>
<?php
$msg_user_loginid = "";
$msg_user_email = "";
try {
  $sql = "select * from users where user_loginid = :user_loginid";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_loginid", $_POST["user_loginid"], PDO::PARAM_STR);
  $stmt->execute();
  $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
  if ($row_user["user_loginid"]) {
    $msg_user_loginid = "そのユーザーIDは使用されています";
    $_SESSION["msg_user_loginid"] = 1;
  } else {
    $msg_user_loginid = "OK";
    $_SESSION["msg_user_loginid"] = 0;
  }
  $sql = "select * from users where user_email = :user_email";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_email", $_POST["user_email"], PDO::PARAM_STR);
  $stmt->execute();
  $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
  if ($row_user["user_email"]) {
    $msg_user_email = "そのメールアドレスは登録済みです";
    $_SESSION["msg_user_email"] = 1;
  } else {
    $msg_user_email = "OK";
    $_SESSION["msg_user_email"] = 0;
  }
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
$result = array(
  "user_loginid" => $msg_user_loginid,
  "user_email" => $msg_user_email
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>