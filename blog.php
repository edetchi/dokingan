<?php
/*=============================================================================
    データベースの接続開始
=============================================================================*/
/*----------------------------------------------------------------------------
    環境設定
----------------------------------------------------------------------------*/
$db_type = "mysql";
$db_host = "localhost";
$db_name = "framerefugee";
$db_user = "root";
$db_pass = "root";
$dsn = "{$db_type}:host={$db_host};dbname={$db_name};charset=utf8";
/*----------------------------------------------------------------------------
    データベースへの接続確立
----------------------------------------------------------------------------*/
try {
	$pdo = new PDO($dsn, $db_user, $db_pass);
	//エラーモード設定
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//プリペアドステートメント用意
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	//デバッグ用
	print "接続完了<br>";
} catch(PDOException $e){
	//エラー発生時処理停止してエラー表示
	die("エラー: " . $e->getMessage());
}
/*----------------------------------------------------------------------------
    記事一覧用データ取得
----------------------------------------------------------------------------*/
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
} catch(PDOException $e){
	//エラー時のロールバック
	$pdo->rollBack();
	print "エラー: " . $e->getMessage();
}
/*=============================================================================
    データベースの接続終了
=============================================================================*/
?>
<?php $page_title = "ブログ"; ?>
<?php require "header.php"; ?>
<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
	<article>
		<h2><?= he($row["post_title"]) ?></h2>
		<time><?= he($row["post_created"]) ?></time>
		<p><?= he(nl2br($row["post_content"])) ?></p>
	</article>
<?php endwhile; ?>
<?php require "footer.php"; ?>
<?php
/*=============================================================================
    関数コーナー
=============================================================================*/
//サニタイズを多用するのでhtmlentities()を簡略化
function he($str){
  return htmlentities($str, ENT_QUOTES, "utf-8");
}
?>