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
/*-----------------------------------------------------------------------------
    フレームの画像投稿時の拡張子チェック
-----------------------------------------------------------------------------*/
function imageExtensionFlag($extension){
  $allowed_extensions = array("jpg", "jpeg", "gif", "png",);
  foreach($allowed_extensions as $allowed_extension){
    if ($allowed_extension === $extension){
      return 1;
    }
  }
  return 0;
}


/*-----------------------------------------------------------------------------
    ページャー
-----------------------------------------------------------------------------*/
function pager() {
  global $page;
  global $per_page;
  global $default_per_page;
  global $total_count;
  global $sort;
  global $order;
  //config.phpで定義されている変数を使うにはグローグル変数として宣言することを忘れずに
  global $app_dir;
  $url = $_SERVER["REQUEST_URI"];
  //GETパラメータが与えられていない時($url == アプリのルートディレクトリ)、リンクが計算されないので初期値をセット
  //var_export(preg_replace("/\/$/", "", $url));
  if ($url == "/") {
  //if (preg_replace("/\/$/", "", $url) == $app_dir) {
    $url .= "?sort={$sort}&order={$order}&page=1&per_page={$default_per_page}";
  }
  $prev = $page - 1;
  $next = $page + 1;
  //ページャーリンクの数
  $pager_count = 5;
  //前へと次へのurl作成
  $prev_link = preg_replace("/(?!_)page={$page}/", "page={$prev}", $url);
  $next_link = preg_replace("/(?!_)page={$page}/", "page={$next}", $url);
  //最大ページを計算
  $max_page = ceil($total_count / $per_page);
  //前へリンクと省略部分
  echo "<div class='pager'>";
  if ($page > 1) echo "<a href={$prev_link}>前へ</a>";
  if ($page  > 3 && $max_page > 5) echo " ... ";
  //メインの数字部分の始まりと終わりを計算
  //最大ページがページリンク数以下の時
  if ($max_page <= $pager_count) {
    $start = 1;
    $end = $max_page;
  } else {
    //始まり（現在ページが1に近い時）
    $start =  ($page > 2) ? ($page - 2) : 1;
    //始まり再計算（現在ページが最大ページに近い時）
    $start = ($page + 3 > $max_page) ? ($max_page-4) :$start;
    //終わり（現在ページが最大ページに近い時）
    $end = ($start + 4 < $max_page) ? $page + 2 : $max_page;
    //終わり再計算（現在ページが1に近い時）
    $end = ($page < 3) ? 5 : $end;
  }
  //メインの数字部分作成
  for ($i = $start; $i <= $end; ++$i) {
    $page_link = preg_replace("/[^_]page={$page}/", "&page={$i}", $url);
    //$page_link = preg_replace("/(?!_)page={$page}/", "page={$i}", $url);
    echo "<a href={$page_link}>{$i}</a>";
  }
  //次へリンクと省略部分
  if ($page + 2 < $max_page && $max_page > 5) echo " ... ";
  if ($page < $max_page) echo "<a href={$next_link}>次へ</a>";
  echo "</div>";
}
/*-----------------------------------------------------------------------------
    モバイル用ページャー
-----------------------------------------------------------------------------*/
function mobilepager() {
  global $page;
  global $per_page;
  global $default_per_page;
  global $total_count;
  global $sort;
  global $order;
  //config.phpで定義されている変数を使うにはグローグル変数として宣言することを忘れずに
  global $app_dir;
  $url = $_SERVER["REQUEST_URI"];
  //var_export($url);
  //GETパラメータが与えられていない時($url == アプリのルートディレクトリ)、リンクが計算されないので初期値をセット
  //var_export(preg_replace("/\/$/", "", $url));
  if ($url == "/") {
  //if (preg_replace("/\/$/", "", $url) == $app_dir) {
    $url .= "?sort={$sort}&order={$order}&page=1&per_page={$default_per_page}";
  }
  $prev = $page - 1;
  $next = $page + 1;
  //ページャーリンクの数
  $pager_count = 5;
  //前へと次へのurl作成
  $prev_link = preg_replace("/(?!_)page={$page}/", "page={$prev}", $url);
  $next_link = preg_replace("/(?!_)page={$page}/", "page={$next}", $url);
  //最大ページを計算
  $max_page = ceil($total_count / $per_page);
  //前へリンクと省略部分
  echo "<div class='mobile-pager'>";
  if ($page > 1) {
    echo "<a href={$prev_link}><i class='fa fa-chevron-left' aria-hidden='true'></i></a>";
  } else {
    echo "<a><i class='fa fa-minus' aria-hidden='true'></i></a>";
  }
  //メインの数字部分作成
  echo "<div class='mobile-pager__selector-info'>";
  echo "<span>{$page} OF {$max_page}</span>";
  echo "</div>";
  /*
  echo "<select class='mobile-pager__selector'>";
  for ($i = 1; $i <= $max_page; ++$i) {
    //$page_link = preg_replace("/[^_]page={$page}/", "&page={$i}", $url);
    if ($i == $page) {
      echo "<option value='{$i}' selected>{$i}</a>";
    } else {
      echo "<option value='{$i}'>{$i}</a>";
    }
  }
  echo "</select>";
  */
  //次へリンクと省略部分
  if ($page < $max_page) {
    echo "<a href={$next_link}><i class='fa fa-chevron-right' aria-hidden='true'></i></a>";
  } else {
    echo "<a><i class='fa fa-minus' aria-hidden='true'></i></a>";
  }
  echo "</div>";
  echo "<div class='dummy'>{$page} OF {$max_page}</div>";
}
/*-----------------------------------------------------------------------------
    画像データを配列or文字列にトグルする
-----------------------------------------------------------------------------*/
function toggleStrArray($target) {
  if (is_array($target)) {
    return implode(";", $target);
  } else {
    return explode(";", $target);
  }
}
/*-----------------------------------------------------------------------------
    画像データの文字列から画像を削除
-----------------------------------------------------------------------------*/
function deleteImages($str) {
  $exploded_str = explode(";", $str);
  foreach ($exploded_str as $key => $value) {
    unlink("../images/frames/{$value}");
    unlink("../images/frames/thumb_{$value}");
  }
}
/*-----------------------------------------------------------------------------
    定数を展開するラムダ関数
-----------------------------------------------------------------------------*/
$_ = function($s){return $s;};
/*=============================================================================
    ユーザー定義関数>>
=============================================================================*/
/*=============================================================================
    特定のURLを含むかどうかのフラグ変数まとめ開始
=============================================================================*/
$url = $_SERVER['REQUEST_URI'];
/*-----------------------------------------------------------------------------
    マイページのディレクトリの中かどうか(strposは検索文字列を含まなければfalseを返す)
-----------------------------------------------------------------------------*/
$is_mypage = (strpos($url,'/mypage/') !== false) ? true : false;
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