<?php
//var_export(str_replace($_SERVER["HTTP_HOST"], "", $_SERVER["REQUEST_URI"]));
//ライブラリの読み込み
require_once("system/common.php");
/*=============================================================================
    <<フレーム一覧用データ取得
=============================================================================*/
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("sort", "order", "page", "per_page");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    並び替え用処理
-----------------------------------------------------------------------------*/
//並び替えのパラメータがあれば取得
$sort = (!empty($request["sort"])) ? ($request["sort"]) : "frame_updated";
$order = (!empty($request["order"])) ? ($request["order"]) : "desc";
//$orderの逆を変数に格納
$reverse_order = ($order === "asc") ? "desc" : "asc";
//ページ番号を変数に格納、もし空白、数字以外1をセット
$page = (!empty($request["page"]) && (preg_match("/^[1-9][0-9]*/", $request["page"]))) ? intval($request["page"]) : 1;
//上と同値だが$_GET["page"]がない場合エラーとなるのでemtpyを使った判定の方が便利
//$page =  ($_GET["page"]) ? intval($_GET["page"]) : 1;
//デフォルトのページ数をセット
$default_per_page = 6;
//表示件数が空白じゃないand整数であれば値セット、そうでなければ$default_per_page
$per_page = (!empty($request["per_page"]) && (preg_match("/^[1-9][0-9]*/", $request["per_page"]))) ? intval($request["per_page"]) : $default_per_page;
//urlを変数に格納
$host = /*$_SERVER["HTTP_HOST"] . */ "/";
//var_export($host);
//$_GET["sort"]の値とリンク名の配列
//$sort_sets = array("frame_updated"=>"最終更新日", "frame_price"=>"価格", "frame_lens_width"=>"レンズ幅", "frame_bridge_width"=>"ブリッジ幅", "frame_temple_length"=>"テンプル長", "frame_lens_height"=>"レンズ高", "frame_frame_width"=>"フレーム幅", "favorite_cnt"=>"お気に入り数");
$sort_sets = array(
  array("get"=>"frame_updated", "name"=>"最終更新日", "default-order"=>"desc"),
  array("get"=>"frame_price", "name"=>"価格", "default-order"=>"asc"),
  array("get"=>"frame_lens_width", "name"=>"レンズ幅", "default-order"=>"asc"),
  array("get"=>"frame_bridge_width", "name"=>"ブリッジ幅", "default-order"=>"asc"),
  array("get"=>"frame_temple_length", "name"=>"テンプル長", "default-order"=>"asc"),
  array("get"=>"frame_lens_height", "name"=>"レンズ高", "default-order"=>"asc"),
  array("get"=>"frame_frame_width", "name"=>"フレーム幅", "default-order"=>"asc"),
  array("get"=>"favorite_cnt", "name"=>"お気に入り数", "default-order"=>"desc"),
);
//ログイン時にレンズの厚み用の値とリンク名を追加
if (!empty($_SESSION["user_id"])) $sort_sets[] = array("get"=>"frame_thickness", "name"=>"レンズ厚み", "default-order"=>"asc");
//$_GET["sort"]の値とリンク名の配列($sort_sets)にソート用のgetリンクを追加した配列を作成
$sort_links = array();
foreach ($sort_sets as $sort_set) {
  //ソート値が与えられている時は、並び順を逆にする
  if (!empty($order) && $sort_set["get"] == $sort) {
    $sort_link = $host . "?sort={$sort_set["get"]}&order={$reverse_order}&page=1&per_page={$per_page}";
  } else {
    $sort_link = $host . "?sort={$sort_set["get"]}&order={$sort_set["default-order"]}&page=1&per_page={$per_page}";
  }
  $sort_links[] = array("key"=>$sort_set["get"], "field"=>$sort_set["name"], "sort_link"=>$sort_link);
}
//var_export($sort_links);
/*-----------------------------------------------------------------------------
    ログインユーザーデーター取得
-----------------------------------------------------------------------------*/
try {
  $sql = "select * from users where user_id = :user_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
  $user_pd = $row_user["user_pd"];
  $user_sph = $row_user["user_sph"];
  $stmt = null;
/*-----------------------------------------------------------------------------
    フレームデータ取得
-----------------------------------------------------------------------------*/
  $sql = "select * from frames left join users on frames.frame_poster_id = users.user_id left join (select frame_id as betsu_frame_id, count(removed_flag) as favorite_cnt from favorites where removed_flag = 0 group by frame_id) as t_favorite_cnt on t_favorite_cnt.betsu_frame_id = frames.frame_id where 1";
  //ソートがフレームの厚みの時はsql文を追加せず、下で配列を並び替える
  if ($sort == "frame_thickness") {
  } else {
    $sql .= " order by $sort $order";
  }
  //ページング用にレコード総数をゲット
  $csql = preg_replace("/^select \*/", "select count(*)", $sql);
  //ページング用に表示できる件数を限定
  if ($page != 1 && !empty($per_page)) {
    $sql .= " limit {$per_page} offset " . $per_page * ($page - 1);
  } else if ($page == 1 && !empty($per_page)) {
    $sql .= " limit {$per_page}";
  }
  $stmt = $pdo->query($sql);
  $cstmt = $pdo->query($csql);
  $total_count = $cstmt->fetchColumn();
  //var_export($total_count);
  $frames = array();
  while($row_frame = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //お気に入り数がnullの場合0を代入
    $row_frame["favorite_cnt"] = (!empty($row_frame["favorite_cnt"])) ? $row_frame["favorite_cnt"] : 0;
    //レンズ端と中央の色を分けるためのクラスを計算
    if (edgeThickness()["edge1_thick"] > edgeThickness()["edge2_thick"]) {
      $edge1_thick_class = "frame-list__max";
      $edge2_thick_class = "frame-list__min";
      //レンズ厚みの大きい方を変数に格納
      $max_thickness = edgeThickness()["edge1_thick"];
    } else {
      $edge1_thick_class = "frame-list__min";
      $edge2_thick_class = "frame-list__max";
      $max_thickness = edgeThickness()["edge2_thick"];
    }
    //価格をカンマ区切り
    $row_frame["frame_price"] = number_format($row_frame["frame_price"]);
    $frames[] = array(
      "frame_id" => $row_frame["frame_id"],
      "frame_image" => $row_frame["frame_image"],
      "frame_price" => $row_frame["frame_price"],
      "frame_lens_width" => $row_frame["frame_lens_width"],
      "frame_bridge_width" => $row_frame["frame_bridge_width"],
      "frame_temple_length" => $row_frame["frame_temple_length"],
      "edge1_thick" => edgeThickness()["edge1_thick"],
      "edge2_thick" => edgeThickness()["edge2_thick"],
      "max_thick" => $max_thickness,
      "edge1_thick_class" => $edge1_thick_class,
      "edge2_thick_class" => $edge2_thick_class,
      "user_loginid" => $row_frame["user_loginid"],
      "favorite_cnt" => $row_frame["favorite_cnt"],
    );
  }
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
//ソートがフレームの厚みの時、格納した配列を並び替える
if ($sort == "frame_thickness") {
  $max_thickness_sort = array();
  foreach ($frames as $frame) $max_thickness_sort[] = $frame['max_thick'];
  if ($order == "asc") {
    array_multisort($max_thickness_sort, SORT_ASC, SORT_NUMERIC, $frames);
  } else if ($order == "desc") {
    array_multisort($max_thickness_sort, SORT_DESC, SORT_NUMERIC, $frames);
  }
}
//var_export($frames);
/*=============================================================================
    フレーム一覧用データ取得>>
=============================================================================*/
$page_title = "トップ";
require("header.php");
?>
  <div class="main-wrap">
    <main>
      <?php foreach($frames as $frame): ?>
      <div class="frame-list__layout">
        <div class="frame-list">
          <a href="detail.php?frame_id=<?= he($frame["frame_id"]) ?>">
            <img class="frame-list__image" src='<?= "./images/frames/" . he(getMainImage($frame["frame_image"])) ?>'>
          </a>
          <ul class="frame-list__info">
            <li class="frame-list__price">
              <span><i class="fa fa-jpy" aria-hidden="true"></i><?= he($frame["frame_price"]) ?></span>
              <span><i class="fa fa-star-o" aria-hidden="true"></i><?= he($frame["favorite_cnt"]) ?></span>
            </li>
            <li class="frame-list__size">
              <?= he($frame["frame_lens_width"]) ?>□<?= he($frame["frame_bridge_width"]) ?>-<?= he($frame["frame_temple_length"]) ?>
            </li>
            <?php if (empty($_SESSION["user_id"])):?>
            <li class="frame-list__userid">
              <i class="fa fa-user-o" aria-hidden="true"></i><?= he($frame["user_loginid"]) ?>
            </li>
            <?php else: ?>
            <li class="frame-list__thickness">
              中心: <span class="<?= $frame["edge1_thick_class"] ?>"><?= $frame["edge1_thick"] ?></span>端: <span class="<?= $frame["edge2_thick_class"] ?>"><?= $frame["edge2_thick"] ?></span>
            </li><!--.frame-list__thickness-->
            <?php endif; ?>
          </ul><!--.frame-list__info-->
        </div><!--.frame-list-->
      </div><!--.frame-list__layout-->
      <?php endforeach; ?>
      <?php mobilepager(); ?>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php
/*=============================================================================
    body部>>
=============================================================================*/
require("footer.php"); ?>