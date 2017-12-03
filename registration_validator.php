<?php require_once("system/common.php"); ?>
<?php
$msg_user_loginid = array();
$msg_user_email = array();
$msg_user_password = array();
try {
  //ユーザー名の一覧を配列に格納
  $sql = "select * from users where user_loginid = :user_loginid";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_loginid", $_POST["user_loginid"], PDO::PARAM_STR);
  $stmt->execute();
  $row_users = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
  //登録済みメールアドレスの一覧を配列に格納
  $sql = "select * from users where user_email = :user_email";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_email", $_POST["user_email"], PDO::PARAM_STR);
  $stmt->execute();
  $user_email = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
//ユーザー名のバリデーション
if (!empty($_POST["user_loginid"])) {
  $_SESSION["msg_user_loginid"] = 1;
  if ($row_users["user_loginid"]) {
    $msg_user_loginid[] = "そのユーザーIDは使用されています";
  }
  if (!preg_match("/^[a-zA-Z0-9]{1,10}$/", $_POST["user_loginid"])) $msg_user_loginid[] = "ユーザーIDは英数字のみ、10文字以内にしてください";
} else if (empty($_POST["user_loginid"])) {
  $msg_user_loginid[] = "未入力です";
}
if (empty($msg_user_loginid)) {
  $msg_user_loginid[] = "OK";
  $_SESSION["msg_user_loginid"] = 0;
}
//メールアドレスのバリデーション
if (!empty($_POST["user_email"])) {
  $_SESSION["msg_user_email"] = 1;
  if ($user_email["user_email"]) {
    $msg_user_email[] = "登録済みです、他のメールアドレスを使用してください";
  }
  if (strlen($_POST["user_email"]) > 50) $msg_user_email[] = "メールアドレスは50文字以内にしてください";
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\?\*\[|\]%'=~^\{\}\/\+!#&\$\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/", $_POST["user_email"])) $msg_user_email[] = "メールアドレスの形式を確認してください";
} else if (empty($_POST["user_email"])) {
  $msg_user_email[] = "未入力です";
}
if (empty($msg_user_email)) {
  $msg_user_email[] = "OK";
  $_SESSION["msg_user_email"] = 0;
}
//パスワードのバリデーション
if (!empty($_POST["user_password"])) {
  if (!preg_match("/^[a-zA-Z0-9!@#$%^&*]{6,32}$/", $_POST["user_password"])) $msg_user_password[] = "パスワードは半角英数で6文字以上32文字以下にしてください";
} else {
  $msg_user_password[] = "未入力です";
}
if (empty($msg_user_password)) {
  $msg_user_password[] = "OK";
  $_SESSION["msg_user_password"] = 0;
}
//ajaxで渡すdataを配列に格納
$result = array(
  "user_loginid" => $msg_user_loginid,
  "user_email" => $msg_user_email,
  "user_password" => $msg_user_password
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>