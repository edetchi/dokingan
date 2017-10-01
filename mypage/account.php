<?php
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("user_id", "user_loginid", "user_password", "user_email", "user_sph", "user_pd", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
$form = array();
$form["user_loginid"] = $request["user_loginid"];
$form["user_password"] = $request["user_password"];
$form["user_email"] = $request["user_email"];
$form["user_sph"] = $request["user_sph"];
$form["user_pd"] = $request["user_pd"];
/*-----------------------------------------------------------------------------
    修正モード時の処理
-----------------------------------------------------------------------------*/
if (!isset($request["send"])) {
	try {
		$sql = "select * from users where user_id = :user_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
		$stmt->execute();
		$row_user = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row_user) {
				$form["user_loginid"] = $row_user["user_loginid"];
        $form["user_password"] = $row_user["user_password"];
        $form["user_email"] = $row_user["user_email"];
        $form["user_sph"] = $row_user["user_sph"];
        $form["user_pd"] = $row_user["user_pd"];
			} else {
				die("異常なアクセスです");
			}
	} catch (PDOException $e) {
		die("エラー: " . $e->getMessage());
	}
}
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
    //空欄チェック
    if ($request["user_loginid"] == "") $error_message .= "ユーザーIDを入力してください\n";
    if ($request["user_password"] == "") $error_message .= "パスワードを入力してください\n";
    if ($request["user_email"] == "") $error_message .= "メールアドレスを入力してください\n";
    if ($request["user_sph"] == "") $error_message .= "SPH（度数）を入力してください\n";
    if ($request["user_pd"] == "") $error_message .= "PD（瞳孔間距離）を入力してください\n";
}
/*=============================================================================
    送信ボタンが押されて、エラーメッセージがない時、修正開始
=============================================================================*/
if (isset($request["send"]) && $error_message == "") {
  try {
    $pdo->beginTransaction();
    $sql = "update users set user_loginid = :user_loginid, user_password = :user_password, user_email = :user_email, user_sph = :user_sph, user_pd = :user_pd where user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":user_loginid", $request["user_loginid"], PDO::PARAM_STR);
    $stmt->bindValue(":user_password", $request["user_password"], PDO::PARAM_STR);
    $stmt->bindValue(":user_email", $request["user_email"], PDO::PARAM_STR);
    $stmt->bindValue(":user_sph", $request["user_sph"], PDO::PARAM_INT);
    $stmt->bindValue(":user_pd", $request["user_pd"], PDO::PARAM_INT);
    $stmt->execute();
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    die("エラー: " . $e->getMessage());
  }
  $page_message = "修正しました";
}
/*=============================================================================
送信ボタンが押されて、エラーメッセージがない時、修正終了
=============================================================================*/
?>
<?php $page_title = "アカウント設定";?>
<?php require("header.php"); ?>
	<p>
		<a href="item_list.php">一覧へ戻る</a>
	</p>
	<p>
		<?= he($page_message) ?>
	</p>
	<p class="attention">
		<?= nl2br(he($error_message)) ?>
	</p>
	<form action="account.php" method="post">
		<div>
			<label for="yu-za-mei">ユーザー名<span class="attention">【必須】</span></label>
			<input type="text" name="user_loginid" id="yu-za-mei" size="30" value="<?= he($form["user_loginid"]); ?>">
		</div>
    <div>
    <label for="pasuwa-do">パスワード<span class="attention">【必須】</span></label>
			<input type="password" name="user_password" id="pasuwa-do" size="30" value="<?= he($form["user_password"]); ?>">
    </div>
    <div>
    <label for="meado">メールアドレス<span class="attention">【必須】</span></label>
			<input type="text" name="user_email" id="meado" size="30" value="<?= he($form["user_email"]); ?>">
    </div>
    <div>
    <label for="esupieichi">SPH（度数）<span class="attention">【必須】</span></label>
			<input type="number" name="user_sph" id="esupieichi" size="30" value="<?= he($form["user_sph"]); ?>">
    </div>
    <div>
    <label for="doukoukankyori">瞳孔間距離<span class="attention">【必須】</span></label>
			<input type="number" name="user_pd" id="doukoukankyori" size="30" value="<?= he($form["user_pd"]); ?>">
    </div>
		<div>
			<input type="submit" name="send" value="更新">
		</div>
	</form>
<?php require("footer.php"); ?>