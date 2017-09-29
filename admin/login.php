<?php
//ログインページ判別変数をtrueに
$ignore_login = true;
?>
<?php require_once("../system/admin_common.php"); ?>
<?php
/*----------------------------------------------------------------------------
    変数をホワイトリスト化
----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("user_loginid", "user_password", "send");
$request = array();
//入力欄が空欄なら連想配列$requestにnull、入力値があるならその値を格納
foreach($whitelists as $whitelist){
    $request[$whitelist] = null;
    if(isset($_REQUEST[$whitelist])){
      //keyからヌルバイト除去
      $whitelist = str_replace("\0", "", $whitelist);
      $request[$whitelist] = $_REQUEST[$whitelist];
    }
}
//サンキューメッセージ初期化
$page_message = "";
//エラーメッセージの初期化
$error_message = "";
/*----------------------------------------------------------------------------
    フォーム項目のエラーチェック
----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
    //空欄チェック
    if ($request["user_loginid"] == "") $error_message .= "ログインIDを入力してください\n";
    if ($request["user_password"] == "") $error_message .= "パスワードを入力してください\n";
}
//送信ボタンが押されて、エラーメッセージがない時、ログイン実行
if (isset($request["send"]) && $error_message == "") {
    try {
        // まずはログインIDでSELECTする
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_loginid = ? LIMIT 1");
        $stmt->execute(array($request["user_loginid"])); // クエリの実行
        $row_user = $stmt->fetch(PDO::FETCH_ASSOC); // SELECT結果を配列に格納
        if ($row_user) {
            // 該当のuserレコードがあったら、パスワードを照合する
            if (sha1($request["user_password"]) == $row_user["user_password"]) {
                $_SESSION["user_id"] = $row_user["user_id"];
                header("Location: index.php");
                exit;
            }
        }
        $error_message .= "入力内容をご確認ください\n";
    } catch (PDOException $e) {
        // エラー発生時
        exit("ログイン処理に失敗しました");
    }
}
?>
<?php $page_title = "ログイン";?>
<?php require("header.php"); ?>
    <!-- サンキューメッセージ表示 -->
    <!--サニタイズ化-->
    <p>
      <?= he($page_message) ?>
    </p>
    <!--エラーメッセージの表示-->
    <p class="attention">
      <!--$error_messageはユーザーから受け取る値は入っていないが、変数を表示するときはサニタイズするのがベター-->
      <?= nl2br(he($error_message)) ?>
    </p>
    <form action="login.php" method="post">
      <div>
	<label for="roguin">ログインID<span class="attention">【必須】</span></label>
        <input type="text" name="user_loginid" id="roguin" size="30" value="">
      </div>
      <div>
	<label for="pasuwa-do">パスワード<span class="attention">【必須】</span></label>
        <input type="password" name="user_password" id="pasuwa-do" size="30" value="">
      </div>
      <div>
        <input type="submit" name="send" value="ログインする">
      </div>
    </form>
<?php require("footer.php"); ?>