<?php require_once("../system/common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    フレーム一覧用データ取得
-----------------------------------------------------------------------------*/
try {
    $sql = "select * from frames where frame_poster_id = :frame_poster_id order by frame_created desc";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":frame_poster_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
		die("エラー: " . $e->getMessage());
}
?>
<?php $page_title = "フレーム管理";?>
<?php require("header.php"); ?>
	<a href="frame_edit.php">フレームを追加する</a>
	<hr>
	<table border="1" width="100%">
		<tr>
			<th></th>
			<th>フレーム名</th>
      <th>コメント</th>
      <th>価格(円)</th>
      <th>画像</th>
      <th>商品リンク</th>
      <th>レンズ幅(mm)</th>
      <th>レンズの高さ(mm)</th>
      <th>ブリッジ幅(mm)</th>
      <th>テンプルの長さ(mm)</th>
      <th>フレーム幅(mm)</th>
			<th>更新日時</th>
			<th>作成日時</th>
			<th></th>
		</tr>
<?php while ($row_frame = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
		<tr>
			<td><a href="frame_edit.php?mode=change&frame_id=<?= he($row_frame["frame_id"]) ?>">編集</a></td>
			<td><?= he($row_frame["frame_title"]) ?></td>
			<td><?= nl2br(he($row_frame["frame_content"]));?></td>
      <td><?= he($row_frame["frame_price"]) ?></td>
      <td><?= he($row_frame["frame_image"]) ?></td>
      <td><?= he($row_frame["frame_link"]) ?></td>
      <td><?= he($row_frame["frame_lens_width"]) ?></td>
      <td><?= he($row_frame["frame_lens_height"]) ?></td>
      <td><?= he($row_frame["frame_bridge_width"]) ?></td>
      <td><?= he($row_frame["frame_temple_length"]) ?></td>
      <td><?= he($row_frame["frame_frame_width"]) ?></td>
      <td><?= he($row_frame["frame_updated"]) ?></td>
			<td><?= he($row_frame["frame_created"]) ?></td>
			<td><a href="frame_edit.php?mode=delete&frame_id=<?= he($row_frame["frame_id"]) ?>">削除</a></td>
		</tr>
<?php endwhile; ?>
	</table>
<?php require("footer.php"); ?>