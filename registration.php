<?php require_once("system/common.php");
if ($login_flag == true) {
  header("Location: ./mypage/");
  exit;
}
$_SESSION["token"] = md5(session_id());
//var_export($_SERVER['HTTPS']);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
/*-----------------------------------------------------------------------------
    未定義インデックスのエラー避け
-----------------------------------------------------------------------------*/
//セッション初期化でエラーが入力欄に表示されるのでそれ避け
$_POST["user_loginid"] = (!empty($_POST["user_loginid"])) ? $_POST["user_loginid"] : "";
$_POST["user_email"] = (!empty($_POST["user_email"])) ? $_POST["user_email"] : "";
$_POST["user_password"] = (!empty($_POST["user_password"])) ? md5($_POST["user_password"]) : "";
/*-----------------------------------------------------------------------------
    エラー時の入力項目のセッションに保持する
-----------------------------------------------------------------------------*/
$_SESSION["user_loginid"] = (!empty($_POST["user_loginid"])) ? $_POST["user_loginid"] : "";
$_SESSION["user_email"] = (!empty($_POST["user_email"])) ? $_POST["user_email"] : "";
$_SESSION["user_password"] = (!empty($_POST["user_password"])) ? $_POST["user_password"] : "";
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
if (!empty($_POST["send"])) {
  //ユーザー名
  if (empty($_POST["user_loginid"])) {
     $error_msgs[] =  "ユーザー名を入力してください";
  } else {
    if (!preg_match("/^[a-zA-Z0-9]{1,10}$/", $_POST["user_loginid"])) $error_msgs[] = "ユーザーIDは英数字のみ、10文字以内にしてください";
  }
  //メールアドレス
  if (empty($_POST["user_email"])) {
    $error_msgs[] =  "メールアドレスを入力してください";
  } else {
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST["user_email"])) $error_msgs[] =  "メールアドレスの形式が正しくありません。";
    if (strlen($_POST["user_email"]) > 50) $error_msgs[] = "メールアドレスは50文字以内にしてください";
  }
  //パスワード
  if (empty($_POST["user_password"])) {
    $error_msgs[] =  "パスワードを入力してください";
  } else {
    if (!preg_match("/^[a-zA-Z0-9\\\*\+\.\?\{\}\(\)\[\]\^\$\-\|\/!\"#%&'=~@;:,`_]{6,32}$/", $_POST["user_password"])) $error_msgs[] = "パスワードは半角英数記号で6文字以上32文字以下にしてください";
  }
  //ユーザー名、メアドの重複チェック
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
  if ($row_users["user_loginid"]) $error_msgs[] = "そのユーザーIDは使用されています";
  if ($user_email["user_email"]) $error_msgs[] = "登録済みです、他のメールアドレスを使用してください";
  //var_dump($error_msgs);
}
/*-----------------------------------------------------------------------------
    エラーなしでメール送信、
-----------------------------------------------------------------------------*/
if (!empty($_POST["send"]) && empty($error_msgs)) {
  $https = (!empty($_SERVER['HTTPS'])) ? "https://" : "http://";
  $url = "{$https}{$_SERVER["HTTP_HOST"]}/registration_complete.php?urltoken=" . $_SESSION["token"];
  try {
    $pdo->beginTransaction();
    $sql = "insert into pre_users (pre_urltoken, pre_userid, pre_email, pre_password) values (:pre_urltoken, :pre_userid, :pre_email, :pre_password)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":pre_urltoken", $_SESSION["token"], PDO::PARAM_STR);
    $stmt->bindValue(":pre_userid", $_POST["user_loginid"], PDO::PARAM_STR);
    $stmt->bindValue(":pre_email", $_POST["user_email"], PDO::PARAM_STR);
    $stmt->bindValue(":pre_password", $_POST["user_password"], PDO::PARAM_STR);
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
本メールは、{$_(SITE_NAME)}に登録ご希望のユーザーに確認のためにお送りしています。
24時間以内に下記のURLにアクセスしてご登録を完了して下さい。
$url

━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─
  {$_(SITE_NAME)}
  URL: {$_SERVER["HTTP_HOST"]}
  E-MAIL: {$_(EMAIL_CONTACT_SENDER)}
━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─
EOM;
  //送信者に確認メールをに送信
  $subject = "会員登録を完了してください";
  $add_header = "From: {$_(SITE_NAME)} <{$_(EMAIL_NOREPLY_SENDER)}>";
  $mail_to = $_POST["user_email"];//メアドのバリデーションがないと脆弱性が発生する
  //メールの送信&送信が成功した時の処理
  if ($result = mb_send_mail($mail_to, $subject, $mail_body, $add_header)) {
    //セッション系削除
    $_SESSION = array();
    if (!empty($_COOKIE["PHPSESSID"])) {
      setcookie("PHPSESSID", '', time() - 1800, '/');
    }
    session_destroy();
    //入力欄に表示する値を削除
    $_POST["user_loginid"] = "";
    $_POST["user_email"] = "";
    $_POST["user_password"] = "";
    //メッセージ作成
    $page_msgs[] = "メールを送信致しました。24時間以内にメールに記載されたURLから登録を完了させて下さい。";
  } else {
    $error_msgs[] =  "メールの送信に失敗しました。お手数ですが、再度新規登録を開始して下さい。";
  }
}
?>
<?php $page_title = "ユーザー登録";?>
<?php require("header.php"); ?>
  <div class="main-wrap">
    <main>
      <div class="registration-message">
        <p>
          <?php foreach ($page_msgs as $page_msg): ?>
          <p><?= he($page_msg) ?></p>
          <?php endforeach; ?>
        </p>
        <p class="attention">
          <?php foreach ($error_msgs as $error_msg): ?>
          <p><?= he($error_msg) ?></p>
          <?php endforeach; ?>
        </p>
      </div>
      <form class="registration-form" action="registration.php" method="post">
        <div class="registration-form__user_loginid">
          <label for="yu-za-mei">ユーザー名<span class="attention">*</span></label>
          <span class="registration-form__loginid_result"></span>
          <input type="text" name="user_loginid" id="yu-za-mei" size="10" value="<?= he($_POST["user_loginid"]) ?>">
        </div>
        <div class="registration-form__user_email">
          <label for="me-ruadoresu">メールアドレス<span class="attention">*</span></label>
          <span class="registration-form__email_result"></span>
          <input type="text" name="user_email" id="me-ruadoresu" size="50" value="<?= he($_POST["user_email"]) ?>">
        </div>
        <div class="registration-form__user_password">
          <label for="pasuwa-do">パスワード<span class="attention">*</span></label>
          <span class="registration-form__password_result"></span>
          <input type="password" name="user_password" id="pasuwa-do" size="32" value="<?= he($_POST["user_password"]) ?>">
        </div>
        <div>
          <input type="hidden" name="token" value="<?= he($_SESSION['token']) ?>">
          <input class="registration-form__submit-btn" type="submit" name="send" value="送信する">
        </div>
      </form>
    </main>
  </div>
<?php require("footer.php"); ?>