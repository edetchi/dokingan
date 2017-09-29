<?php
//ログインページ判別変数をtrueに
$ignore_login = true;
?>
<?php require_once("../system/admin_common.php"); ?>
<?php
/*----------------------------------------------------------------------------------------------------------------------------------------------------------
    変数をホワイトリスト化
----------------------------------------------------------------------------------------------------------------------------------------------------------*/
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
/*----------------------------------------------------------------------------------------------------------------------------------------------------------
    フォーム項目のエラーチェック
----------------------------------------------------------------------------------------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
    //空欄チェック
    if ($request["user_loginid"] == "") $error_message .= "ログインIDを入力してください\n";
    if ($request["user_password"] == "") $error_message .= "パスワードを入力してください\n";
}
/*=============================================================================
    送信ボタンが押されて、エラーメッセージがない時、ログイン実行開始
=============================================================================*/
if (isset($request["send"]) && $error_message == "") {
/*----------------------------------------------------------------------------------------------------------------------------------------------------------
    ログインIDとパスが一致したら、セッション名user_idにデーターベースの一意なuser_idの値を代入する（ログイン実行）
----------------------------------------------------------------------------------------------------------------------------------------------------------*/
    try {
        // ユーザーの入力したログインIDでセレクト実行、ユーザーの入力したユーザーIDはプレースホルダーとして:user_idに定義
        $sql = "SELECT * FROM users WHERE user_loginid = :user_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        //プレースホルダー:user_idとユーザーの入力したユーザーIDを結びつけ、データ型を指定
        $stmt->bindValue(":user_id", $request["user_loginid"], PDO::PARAM_INT);
        $stmt->execute();
        $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row_user) {
            // 該当のユーザーIDレコードがあったら、パスワードを照合し、セッション名user_idにデーターベースの一意なuser_idの値を代入
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
/*=============================================================================
    送信ボタンが押されて、エラーメッセージがない時、ログイン実行終了
=============================================================================*/
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