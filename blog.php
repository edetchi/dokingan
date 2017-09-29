<?php
/*=============================================================================
    データベースの接続開始
=============================================================================*/
//ライブラリの読み込み
require_once("system/common.php");
/*----------------------------------------------------------------------------------------------------------------------------------------------------------
    記事一覧用データ取得
----------------------------------------------------------------------------------------------------------------------------------------------------------*/
try {
	//ロールバックようにトランザクション開始を設定
	$pdo->beginTransaction();
	$sql = "select * from posts order by post_created desc";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	//処理を確定
	$pdo->commit();
	$row_count = $stmt->rowCount();
	print "記事が、{$row_count}件ございます";
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
<?php $page_title = "ブログ"; ?>
<?php require("header.php"); ?>
<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
	<article>
		<h2><?= he($row["post_title"]) ?></h2>
		<time><?= he($row["post_created"]) ?></time>
		<p><?= he(nl2br($row["post_content"])) ?></p>
	</article>
<?php endwhile; ?>
<?php require("footer.php"); ?>