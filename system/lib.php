<?php
/*=============================================================================
    <<ユーザー定義関数
=============================================================================*/
/*-----------------------------------------------------------------------------
    サニタイズを多用するのでhtmlentities()を簡略化
-----------------------------------------------------------------------------*/
function he($str) {
 	return htmlentities($str, ENT_QUOTES, "utf-8");
}
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
function whitelist($whitelists) {
  $request = array();
  //入力欄が空欄なら連想配列$requestにnull、入力値があるならその値を格納
  foreach($whitelists as $whitelist){
      $request[$whitelist] = null;
      if(isset($_REQUEST[$whitelist])){
        //keyからヌルバイト除去
        $whitelist = str_replace("\0", "", $whitelist);
        $request[$whitelist] = $_REQUEST[$whitelist];
      }
  }
  return $request;
}
/*-----------------------------------------------------------------------------
    フレームidからフレーム情報を配列$form[]で返す
-----------------------------------------------------------------------------*/

function frame_id($frame_id) {
  try {
    $db_type = "mysql";
    $db_host = "localhost";
    $db_name = "framerefugee";
    $db_user = "root";
    $db_pass = "root";
    $dsn = "{$db_type}:host={$db_host};dbname={$db_name};charset=utf8";
    //$pdo = null;
  	$pdo = new PDO($dsn, $db_user, $db_pass);
  	//エラーモード設定
  	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  	//プリペアドステートメント用意
  	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  	//デバッグ用
  	//print "接続完了<br>";
    $stmt = null;
    $sql = "select * from frames where :frame_id = $frame_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":frame_id", $frame_id, PDO::PARAM_INT);
    $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    return $row_frame;
  } catch (PDOException $e) {
  	//エラー発生時処理停止してエラー表示
  	die("エラー: " . $e->getMessage());
  }

}
/*-----------------------------------------------------------------------------
    レンズの厚み計算、while文でフレームデータを取得している時にレンズの厚みを計算する
-----------------------------------------------------------------------------*/
function edgeThickness() {
  global $row_frame;
  global $user_pd;
  global $user_sph;
  //瞳孔から目元までの距離(mm)
  $edge1 = ($user_pd - $row_frame["frame_bridge_width"]) / 2;
  //瞳孔から目尻までの距離(mm)
  $edge2 = $row_frame["frame_lens_width"] - $edge1;
  $max_edge = $edge1 > $edge2 ? $edge1 : $edge2;
  $min_edge = $edge1 < $edge2 ? $edge1 : $edge2;
  //レンズ中央の厚み
  $center_thick = 1.0;
  //レンズ屈折率
  $index = 1.74;
  //minimum blank sizeとは$max_edge*2の値のこと
  $thick= round((pow($max_edge, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 2);
  //中心（目元より）の厚さ
  $edge1_thick = round((pow($edge1, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 1);
  //端の厚さ
  $edge2_thick = round((pow($edge2, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 1);
  return array(
    "edge1_thick" => $edge1_thick,
    "edge2_thick" => $edge2_thick,
  );
}
/*=============================================================================
    ユーザー定義関数>>
=============================================================================*/
/*=============================================================================
    特定のURLを含むかどうかのフラグ変数まとめ開始
=============================================================================*/
$url = $_SERVER['REQUEST_URI'];
/*-----------------------------------------------------------------------------
    マイページのディレクトリの中かどうかのチェック
-----------------------------------------------------------------------------*/
$is_mypage = strpos($url,'/mypage/');
/*-----------------------------------------------------------------------------
    マイページのトップ画面かどうかのチェック
-----------------------------------------------------------------------------*/
$is_mypage_top = preg_match("/mypage\/$/", $url);
/*-----------------------------------------------------------------------------
    フレーム詳細ページかどうかのチェック
-----------------------------------------------------------------------------*/
$is_detail_page = preg_match("/detail\.php\?frame_id=/", $url);
/*=============================================================================
    特定のURLを含むかどうかのフラグ変数まとめ終了
=============================================================================*/

?>