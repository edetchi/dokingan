<?php require_once("../system/admin_common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("mode", "post_id", "item_name", "item_comment", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    動作モードを$modeに格納（空白→新規、chnage→修正）
-----------------------------------------------------------------------------*/
$mode = $request["mode"];
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
$form = array();
$form["post_id"] = $request["post_id"];
$form["item_name"] = $request["item_name"];
$form["item_comment"] = $request["item_comment"];
/*-----------------------------------------------------------------------------
    修正モードor削除モード時の処理
-----------------------------------------------------------------------------*/
if (!isset($request["send"]) && $mode == "change" || $mode == "delete") {
	try {
		$sql = "select * from posts where post_id = :post_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(":post_id", $request["post_id"], PDO::PARAM_INT);
		$stmt->execute();
		$row_post = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row_post) {
				$form["item_name"] = $row_post["post_title"];
				$form["item_comment"] = $row_post["post_content"];
			} else {
				die("異常なアクセスです");
			}
	} catch (PDOException $e) {
		die("エラー: " . $e->getMessage());
	}
}
/*-----------------------------------------------------------------------------
    削除モード時の処理
-----------------------------------------------------------------------------*/
if ($mode == "delete") {
	try {
		$pdo->beginTransaction();
		$sql = "delete from posts where post_id = :post_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(":post_id", $request["post_id"], PDO::PARAM_INT);
		$stmt->execute();
		$pdo->commit();
	} catch (PDOException $e) {
		$pdo->rollBack();
		die("エラー; " . $e->getMessage());
	}
	header("Location: item_list.php");
	exit;
}
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
    送信ボタンが押されて、エラーメッセージがない時、新規登録or修正開始
=============================================================================*/
if (isset($request["send"]) && $error_message == "") {
    try {
				$pdo->beginTransaction();
/*-----------------------------------------------------------------------------
    修正モード
-----------------------------------------------------------------------------*/
				if ($mode == "change") {
					$sql = "update posts set post_title = :item_name, post_content = :item_comment where post_id = :post_id";
					$stmt = $pdo->prepare($sql);
					$stmt->bindValue(":item_name", $request["item_name"], PDO::PARAM_STR);
					$stmt->bindValue(":item_comment", $request["item_comment"], PDO::PARAM_STR);
					$stmt->bindValue(":post_id", $request["post_id"], PDO::PARAM_INT);
/*-----------------------------------------------------------------------------
    新規登録モード
-----------------------------------------------------------------------------*/
					} else {
					$sql = "insert into posts (post_title, post_content, post_created) VALUES (:item_name, :item_comment, NOW())";
					$stmt = $pdo->prepare($sql);
					$stmt->bindValue(":item_name", $request["item_name"], PDO::PARAM_STR);
					$stmt->bindValue(":item_comment", $request["item_comment"], PDO::PARAM_STR);
					// 新規作成が成功したら、修正モードにして直近のデータを修正できるようにする
					$mode = "change";
					$form["post_id"] = $pdo->lastInsertId("post_id");
				}
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
送信ボタンが押されて、エラーメッセージがない時、新規登録or修正終了
=============================================================================*/
?>
<?php $page_title = "フレーム編集";?>
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
	<?php if ($mode == "change"): ?>
    <p>
      記事ID[<?= he($form["post_id"]); ?>]を修正しています
    </p>
	<?php endif; ?>
	<form action="item_edit.php" method="post">
		<div>
			<label for="hure-mumei">フレーム名<span class="attention">【必須】</span></label>
			<input type="text" name="item_name" id="hure-mumei" size="30" value="<?= he($form["item_name"]); ?>">
		</div>
		<div>
			<label for="komento">コメント<span class="attention">【必須】</span></label>
			<textarea name="item_comment" id="komento" rows="5" cols="20"><?= he($form["item_comment"]); ?></textarea>
		</div>
		<div>
			<input type="submit" name="send" value="送信する">
			<input type="hidden" name="mode" value="<?= he($mode); ?>">
			<input type="hidden" name="post_id" value="<?= he($form["post_id"]); ?>">
		</div>
	</form>
<?php require("footer.php"); ?>