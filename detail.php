<?php require_once("./system/common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    お気に入り用
-----------------------------------------------------------------------------*/
$_SESSION["frame_id"] = $_REQUEST["frame_id"];
/*=============================================================================
    <<フレーム一覧用データ取得
=============================================================================*/
/*-----------------------------------------------------------------------------
    ログインユーザーデーター取得
-----------------------------------------------------------------------------*/
try {
  $pdo->beginTransaction();
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
  $sql = "select * from frames left join users on frames.frame_poster_id = users.user_id where frames.frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $_REQUEST["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $pdo->commit();
  $stmt = null;
/*=============================================================================
    フレーム一覧用データ取得>>
=============================================================================*/
/*-----------------------------------------------------------------------------
    お気に入りチェック
-----------------------------------------------------------------------------*/
  $sql = "select * from favorites where user_id = :user_id and frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
  $stmt->bindValue(":frame_id", $_SESSION["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_favorite = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
$removed_flag = ($row_favorite["removed_flag"] == null or $row_favorite["removed_flag"] == 1) ? 1 : 0;
var_export($removed_flag);
/*-----------------------------------------------------------------------------
    レンズの厚み計算
-----------------------------------------------------------------------------*/
//瞳孔から目元までの距離(mm)
$edge1 = ($user_pd - $row_frame["frame_bridge_width"])/2;
//瞳孔から目尻までの距離(mm)
$edge2 = $row_frame["frame_lens_width"] - $edge1;
$max_edge = $edge1 > $edge2 ? $edge1 : $edge2;
$min_edge = $edge1 < $edge2 ? $edge1 : $edge2;
//レンズ中央の厚み
$center_thick = 1.0;
//レンズ屈折率
$index = 1.74;
//minimum blank sizeとは$max_edge*2の値のこと
$thick= (pow($max_edge, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick;
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($_REQUEST["send"])) {
  //空欄チェック
  if ($_REQUEST["frame_comment"] == "") $error_message .= "コメントを入力してください\n";
}
/*=============================================================================
    <<お気に入り登録機能
=============================================================================*/
/*
try {
  $pdo->beginTransaction();
  $sql = "insert into favorites (user_id, frame_id, removed_flag) values(:user_id, :frame_id, :removed_flag)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
  $stmt->bindValue(":frame_id", $_REQUEST["frame_id"], PDO::PARAM_INT);
  $stmt->bindValue(":frame_id", , PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $pdo->commit();
  $stmt = null;
} catch (PDOException $e) {
  $pdo->rollBack();
  die("エラー: " . $e->getMessage());
}
*/

?>
<?php $page_title = "フレーム詳細";?>
<?php require("header.php"); ?>
  <p>
    <?= he($page_message) ?>
  </p>
  <p class="attention">
    <?= nl2br(he($error_message)) ?>
  </p>
  <?php if ($_SESSION["user_id"]):?>
  <p><?= he($thick) ?></p>
  <p><?= he($max_edge) ?></p>
  <p><?= he($min_edge) ?></p>
  <?php endif; ?>
  <p><?= he($row_frame["user_loginid"]) ?></p>
  <p><?= he($row_frame["frame_title"]) ?></p>
  <p><?= he(nl2br($row_frame["frame_content"])) ?></p>
  <p><?= he($row_frame["frame_pricee"]) ?></p>
  <p>
    <img src='<?= "./images/frames/" . he($row_frame["frame_image"]) ?>'>
  </p>
  <p><?= he($row_frame["frame_link"]) ?></p>
  <p><?= he($row_frame["frame_lens_width"]) ?></p>
  <p><?= he($row_frame["frame_lens_height"]) ?></p>
  <p><?= he($row_frame["frame_bridge_width"]) ?></p>
  <p><?= he($row_frame["frame_temple_length"]) ?></p>
  <p><?= he($row_frame["frame_frame_width"]) ?></p>
  <time><?= he($row_frame["frame_created"]) ?></time>
  <time><?= he($row_frame["frame_updated"]) ?></time>
  <p><button data-favorite=<?= $removed_flag ?>><i class="fa" aria-hidden="true"></i></button></p>
  <div id="result">

  </div>


<?php require("footer.php"); ?>