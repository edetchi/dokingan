<?php require_once("system/common.php");
$_SESSION["token"] = md5(session_id());
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    $_POSTの値を格納
-----------------------------------------------------------------------------*/
$user_loginid = isset($_POST["user_loginid"]) ? $_POST["user_loginid"] : NULL;
$user_email = isset($_POST["user_email"]) ? $_POST["user_email"] : NULL;
$user_password = isset($_POST["user_password"]) ? md5($_POST["user_password"]) : NULL;
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
if ($user_loginid === "") $error_message .= "ユーザー名を入力してください\n";
if ($user_email === "") $error_message .= "メールアドレスを入力してください\n";
if($user_email !== NULL && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $user_email)) $error_message .= "メールアドレスの形式が正しくありません。\n";
if ($user_password === "") $error_message .= "パスワードを入力してください\n";
if ($_SESSION["msg_user_loginid"] == 1) $error_message .= "そのユーザーIDは使用されています\n";
if ($_SESSION["msg_user_email"] == 1) $error_message .= "そのメールアドレスは登録済みです\n";
//var_dump($_SESSION["msg_user_loginid"]);
//var_dump($error_message);
/*-----------------------------------------------------------------------------
    エラーなしでメール送信、
-----------------------------------------------------------------------------*/
if (isset($_POST["send"]) && $error_message == "") {
  $url = "http://192.168.33.10/framerefugee/registration_complete.php?urltoken=" . $_SESSION["token"];
  try {
    $pdo->beginTransaction();
    $sql = "insert into pre_users (pre_urltoken, pre_userid, pre_email, pre_password) values (:pre_urltoken, :pre_userid, :pre_email, :pre_password)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":pre_urltoken", $_SESSION["token"], PDO::PARAM_STR);
    $stmt->bindValue(":pre_userid", $user_loginid, PDO::PARAM_STR);
    $stmt->bindValue(":pre_email", $user_email, PDO::PARAM_STR);
    $stmt->bindValue(":pre_password", $user_password, PDO::PARAM_STR);
    $stmt->execute();
    //$row_pre_users = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    die("エラー: " . $e->getMessage());
  }
/*-----------------------------------------------------------------------------
    管理者向けメールの送信、mb_send_mail(送信先, 件名, 本文, ヘッダ);
-----------------------------------------------------------------------------*/
  //メール送信用に、言語と文字コードの指定
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");
  //本文用の変数を初期化して、条件に合致するたびに本文を追加していく
  $mail_body = <<<EOM
24時間以内に下記のURLからご登録下さい。
$url
EOM;
  //送信者に確認メールをに送信
  $subject = "会員登録を完了してください";
  $add_header = "From:" . EMAIL_NOREPLY_SENDER;
  $mail_to = $_POST["user_email"];//メアドのバリデーションがないと脆弱性が発生する
  //メールの送信&送信が成功した時の処理
  if ($result = mb_send_mail($mail_to, $subject, $mail_body, $add_header)) {
    //セッション系削除
    $_SESSION = array();
    if (isset($_COOKIE["PHPSESSID"])) {
      setcookie("PHPSESSID", '', time() - 1800, '/');
    }
    session_destroy();
    //メッセージ作成
    $page_message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録を完了させてください。";
  } else {
    $error_message .= "メールの送信に失敗しました。";
  }
}
?>
<?php $page_title = "ユーザー登録";?>
<?php require("header.php"); ?>
  <div class="main-wrap">
    <main>
      <p>
        <?= he($page_message) ?>
      </p>
      <p class="attention">
        <?= nl2br(he($error_message)) ?>
      </p>
      <form class="frame-edit" action="registration.php" method="post">
        <div>
          <label for="yu-za-mei">ユーザー名<span class="attention">【必須】</span></label>
          <div class="user_loginid_result"></div>
          <input type="text" name="user_loginid" id="yu-za-mei" size="30" value="">
        </div>
        <div>
          <label for="me-ruadoresu">メールアドレス<span class="attention">【必須】</span></label>
          <div class="user_email_result"></div>
          <input type="text" name="user_email" id="me-ruadoresu" size="30" value="">
        </div>
        <div>
          <label for="pasuwa-do">パスワード<span class="attention">【必須】</span></label>
          <input type="password" name="user_password" id="pasuwa-do" size="30" >
        </div>
        <div>
          <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
          <input class="registration-btn" type="submit" name="send" value="送信する">
        </div>
      </form>
    </main>
  </div>
<?php require("footer.php"); ?>