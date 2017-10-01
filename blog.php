<?php
/*=============================================================================
    データベースの接続開始
=============================================================================*/
//ライブラリの読み込み
require_once("system/common.php");
/*-----------------------------------------------------------------------------
    フレーム一覧用データ取得
-----------------------------------------------------------------------------*/
try {
	//ロールバックようにトランザクション開始を設定
	$pdo->beginTransaction();
	$sql = "select * from frames order by frame_created desc";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	//処理を確定
	$pdo->commit();
	$row_count = $stmt->rowCount();
	print "フレームが、{$row_count}件ございます";
	//$row = $stmt->fetch(PDO::FETCH_ASSOC);

	//デバッグ用: fetchAllとfetchの違い
	/*
	$fetch_all = $stmt->fetchAll();
	$fetch = $stmt->fetch();
	var_export($fetch_all);
	var_export($fetch);
	*/
} catch (PDOException $e) {
	//エラー時のロールバック
	$pdo->rollBack();
	die("エラー: " . $e->getMessage());
}
/*=============================================================================
    データベースの接続終了
=============================================================================*/
?>
<?php $page_title = "フレーム一覧"; ?>
<?php require("header.php"); ?>
<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
	<article>
		<h2><?= he($row["frame_poster_id"]) ?></h2>
		<time><?= he($row["frame_title"]) ?></time>
		<p><?= he(nl2br($row["frame_content"])) ?></p>
		<p><?= he($row["frame_pricee"]) ?></p>
		<p><?= he($row["frame_image"]) ?></p>
		<p><?= he($row["frame_link"]) ?></p>
		<p><?= he($row["frame_updated"]) ?></p>
	</article>
<?php endwhile; ?>
<?php require("footer.php"); ?>