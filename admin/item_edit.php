<?php require_once("../system/admin_common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("mode", "item_name", "item_comment", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
    //空欄チェック
    if ($request["item_name"] == "") $error_message .= "フレーム名を入力してください\n";
    if ($request["item_comment"] == "") $error_message .= "コメントを入力してください\n";
}
/*=============================================================================
    送信ボタンが押されて、エラーメッセージがない時、修正開始
=============================================================================*/
if (isset($request["send"]) && $error_message == "") {
    try {
				$pdo->beginTransaction();
        $sql = "insert into posts (post_title, post_content, post_created) VALUES (:item_name, :item_comment, NOW())";
        $stmt = $pdo->prepare($sql);
				$stmt->bindValue(":item_name", $request["item_name"], PDO::PARAM_STR);
				$stmt->bindValue(":item_comment", $request["item_comment"], PDO::PARAM_STR);
        $stmt->execute();
				$pdo->commit();
    } catch (PDOException $e) {
        // エラー発生時
				$pdo->rollBack();
        die("エラー: " . $e->getMessage());
    }
		$page_message = "登録が完了しました";
}
/*=============================================================================
送信ボタンが押されて、エラーメッセージがない時、修正終了
=============================================================================*/
?>
<?php $page_title = "フレーム編集";?>
<?php require("header.php"); ?>
	<p>
		<?= he($page_message) ?>
	</p>
	<p class="attention">
		<?= nl2br(he($error_message)) ?>
	</p>
	<form action="item_edit.php" method="post">
		<div>
			<label for="hure-mumei">フレーム名<span class="attention">【必須】</span></label>
			<input type="text" name="item_name" id="hure-mumei" size="30" value="<?= he($request["item_name"]); ?>">
		</div>
		<div>
			<label for="komento">コメント<span class="attention">【必須】</span></label>
			<textarea name="item_comment" id="komento" rows="5" cols="20"><?= he($request["item_comment"]); ?></textarea>
		</div>
		<div>
			<input type="submit" name="send" value="送信する">
		</div>
	</form>
<?php require("footer.php"); ?>