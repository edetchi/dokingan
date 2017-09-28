<?php require_once("../system/admin_common.php"); ?>
<?php $page_title = "フレーム管理";?>
<?php require("header.php"); ?>
	<a href="item_edit.php">フレームを追加する</a>
	<hr>
	<table border="1" width="100%">
		<tr>
			<th></th>
			<th>フレーム名</th>
			<th>コメント</th>
			<th>更新日時</th>
			<th>作成日時</th>
			<th></th>
		</tr>
		<tr>
			<td><a href="item_edit.php?mode=change">編集</a></td>
			<td>記事タイトル（ダミー）</td>
			<td>記事本文（ダミー）</td>
			<td>2000-01-01 00:00:00</td>
			<td>2000-01-01 00:00:00</td>
			<td><a href="item_edit.php?mode=delete">削除</a></td>
		</tr>
		<tr>
			<td><a href="item_edit.php?mode=change">編集</a></td>
			<td>記事タイトル（ダミー）</td>
			<td>記事本文（ダミー）</td>
			<td>2000-01-01 00:00:00</td>
			<td>2000-01-01 00:00:00</td>
			<td><a href="item_edit.php?mode=delete">削除</a></td>
		</tr>
	</table>
<?php require("footer.php"); ?>