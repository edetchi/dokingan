<?php require_once("../system/common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    フレーム一覧用データ取得
-----------------------------------------------------------------------------*/
try {
    $sql = "SELECT * FROM posts ORDER BY post_created DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} catch (PDOException $e) {
		die("エラー: " . $e->getMessage());
}
?>
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
<?php while ($row_post = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
		<tr>
			<td><a href="item_edit.php?mode=change&post_id=<?= he($row_post["post_id"]) ?>">編集</a></td>
			<td><?= he($row_post["post_title"]) ?></td>
			<td><?= nl2br(he($row_post["post_content"]));?></td>
			<td><?= he($row_post["post_updated"]) ?></td>
			<td><?= he($row_post["post_created"]) ?></td>
			<td><a href="item_edit.php?mode=delete&post_id=<?= he($row_post["post_id"]) ?>">削除</a></td>
		</tr>
<?php endwhile; ?>
	</table>
<?php require("footer.php"); ?>