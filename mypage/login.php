<?php
/*-----------------------------------------------------------------------------
    ログインページ判別変数をtrueに（login.phpならture、それ以外ならfalse）
-----------------------------------------------------------------------------*/
//ログイン判別を行うadmin_common.phpより先に読み込む
$is_login_page = true;
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("user_loginid", "user_password", "send");
$request = whitelist($whitelists);
//ページメッセージ初期化
$page_msgs = array();
//エラーメッセージの初期化
$error_msgs = array();
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
    //空欄チェック
    if ($request["user_loginid"] == "") $error_msgs[] = "ログインIDを入力してください";
    if ($request["user_password"] == "") $error_msgs[] = "パスワードを入力してください";
}
/*=============================================================================
    送信ボタンが押されて、エラーメッセージがない時、ログイン実行開始
=============================================================================*/
if (isset($request["send"]) && empty($error_msgs)) {
/*-----------------------------------------------------------------------------
    ログインIDとパスが一致したら、セッション名user_idにデーターベースの一意なuser_idの値を代入する（ログイン実行）
-----------------------------------------------------------------------------*/
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
            if (md5($request["user_password"]) == $row_user["user_password"]) {
                $_SESSION["user_id"] = $row_user["user_id"];
                header("Location: index.php");
                exit;
            }
        }
        $error_msgs[] = "入力内容をご確認ください";
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
    <div class="message">
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
    <form class="login-page" action="login.php" method="post">
      <div>
        <label for="roguin">ログインID<span class="attention">*</span></label>
        <input type="text" name="user_loginid" id="roguin" size="30" value="">
      </div>
      <div>
        <label for="pasuwa-do">パスワード<span class="attention">*</span></label>
        <input type="password" name="user_password" id="pasuwa-do" size="30" value="">
      </div>
      <div>
        <input type="submit" name="send" value="ログインする">
      </div>
    </form>
<?php require("footer.php"); ?>
