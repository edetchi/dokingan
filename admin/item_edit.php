<?php require_once("../system/admin_common.php"); ?>
<?php $page_title = "フレーム編集";?>
<?php require("header.php"); ?>
	<form action="item_edit.php" method="post">
		<div>
			<label for="hure-mumei">フレーム名<span class="attention">【必須】</span></label>
			<input type="text" name="item_name" id="hure-mumei" size="30" value="">
		</div>
		<div>
			<label for="komento">コメント</label>
			<textarea name="item_comment" id="komento" rows="5" cols="20"></textarea>
		</div>
		<div>
			<input type="submit" name="send" value="送信する">
		</div>
	</form>
<?php require("footer.php"); ?>