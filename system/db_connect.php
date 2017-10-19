<?php
/*-----------------------------------------------------------------------------
    データベースへの接続
-----------------------------------------------------------------------------*/
//MySQL
try {
  $pdo = new PDO($dsn, $db_user, $db_pass);
  //SQLite
  //$pdo = new PDO('sqlite:../sqlite3/dokingan.sqlite3');
	//エラーモード設定
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//プリペアドステートメント用意
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	//デバッグ用
	//print "接続完了<br>";
} catch (PDOException $e) {
	//エラー発生時処理停止してエラー表示
	die("エラー: " . $e->getMessage());
}
?>