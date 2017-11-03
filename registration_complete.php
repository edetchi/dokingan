<?php
require_once("system/common.php");
//$_GETのパラメータがない時、登録ページに飛ばす
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";

if (empty($_GET)) {
  header("Location: registration.php");
  exit();
} else {
  $token = $_GET["urltoken"];
  try {
    $pdo->beginTransaction();
    //トークンが有効なものかチェック
    $sql = "select * from pre_users where pre_urltoken = :pre_urltoken and pre_flag = 0 and pre_date > now() - interval 24 hour";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":pre_urltoken", $token, PDO::PARAM_STR);
    $stmt->execute();
    $row_pre_users = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    //トークンが有効であれば、フラグを無効なものにして、ユーザーを登録
    if ($row_pre_users) {
      $sql = "update pre_users set pre_flag = :pre_flag where pre_urltoken = :pre_urltoken";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":pre_flag", 1, PDO::PARAM_INT);
      $stmt->bindValue(":pre_urltoken", $token, PDO::PARAM_STR);
      $stmt->execute();
      $stmt = null;
      //user_loginidが被ってないかチェック
      $sql = "select * from users where user_loginid = :user_loginid";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":user_loginid", $row_pre_users["pre_userid"], PDO::PARAM_STR);
      $stmt->execute();
      $row_user_loginid = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = null;
      if ($row_user_loginid["user_loginid"]) $error_message .= "ご希望のユーザーIDは既に取得されています。他のユーザーIDをご使用ください。\n";
      //user_emailが被ってないかチェック
      $sql = "select * from users where user_email = :user_email";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":user_email", $row_pre_users["pre_email"], PDO::PARAM_STR);
      $stmt->execute();
      $row_user_email = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = null;
      if ($row_user_email["user_email"]) $error_message .= "ご希望のメールアドレスは既に取得されています。他のメールアドレスをご使用ください。\n";
      //被りがなかったら登録
      if (empty($row_user_loginid["user_loginid"]) && empty($row_user_email["user_email"])) {
        $sql = "insert into users (user_loginid, user_password, user_email) values(:user_loginid, :user_password, :user_email)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":user_loginid", $row_pre_users["pre_userid"], PDO::PARAM_STR);
        $stmt->bindValue(":user_password", $row_pre_users["pre_password"], PDO::PARAM_STR);
        $stmt->bindValue(":user_email", $row_pre_users["pre_email"], PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
        //登録が完了すれば、ログイン状態にしてマイページに飛ばす
        $_SESSION["user_id"] = $pdo->lastInsertId("user_id");
        $pdo->commit();
        $_SESSION["page_message"] = "登録が完了しました。";
        header("Location: mypage");
      }
    } else {
    $page_title = "登録エラー";
    $error_message = "このURLはご利用になれません。有効期限が切れた等の問題がありません。登録をもう一度やり直して下さい。";
    }
    require("header.php");?>
      <div class="main-wrap">
        <main>
          <p>
            <?= he($page_message) ?>
          </p>
          <p class="attention">
            <?= nl2br(he($error_message)) ?>
          </p>
        </main>
        <aside>
        </aside>
      </div><!--.main-wrap-->
    <?php require("footer.php");
  } catch (PDOException $e) {
    die("エラー: " . $e->getMessage());
  }
}
