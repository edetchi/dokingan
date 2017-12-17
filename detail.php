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
/*-----------------------------------------------------------------------------
    非ログイン時にお気に入りボタンを押せなくする変数を用意
-----------------------------------------------------------------------------*/
$disabled = (!empty($_SESSION["user_id"])) ? "" : "disabled";
//スパム報告は実装前
$report_removed_flag = "";
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
  $sql = "select * from frames left join users on frames.frame_poster_id = users.user_id left join (select frame_id, count(removed_flag) as favorite_cnt from favorites where removed_flag = 0 group by frame_id) as t_favorite_cnt on t_favorite_cnt.frame_id = frames.frame_id where frames.frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $_REQUEST["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $pdo->commit();
  $stmt = null;
  //フレームのお気に入りがない時、数を0にセットする
  $row_frame["favorite_cnt"] = empty($row_frame["favorite_cnt"]) ? 0 : $row_frame["favorite_cnt"];
  //価格にカンマ追加
  $row_frame["frame_price"] = number_format($row_frame["frame_price"]);
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
//removed_flagフィールドが0ならお気に入り済み、それ以外のnullもしくは1ならお気に入りなし
$removed_flag = ($row_favorite["removed_flag"] === 0) ? 0 : 1;
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
$thick= round((pow($max_edge, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 2);
//中心（目元より）の厚さ
$edge1_thick = round((pow($edge1, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 2);
//端の厚さ
$edge2_thick = round((pow($edge2, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick, 2);
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
  <div class="main-wrap">
    <main>
      <p>
        <?= he($page_message) ?>
      </p>
      <p class="attention">
        <?= nl2br(he($error_message)) ?>
      </p>
      <div class="frame-detail__layout frame-detail__layout_desc_true">
        <div class="frame-detail frame-detail_desc_true">
          <img class="frame-detail__image" src='<?= "./images/frames/" . he($row_frame["frame_image"]) ?>'>
          <ul class="frame-detail__info">
            <li class="frame-detail__userid"><i class="fa fa-user-o" aria-hidden="true"></i><?= he($row_frame["user_loginid"]) ?></li>
            <li class="frame-detail__size">
              <?= he($row_frame["frame_lens_width"]) ?>□<?= he($row_frame["frame_bridge_width"]) ?>-<?= he($row_frame["frame_temple_length"]) ?>
            </li>
            <li class="frame-detail_desc_true__optional">
            <?php if($row_frame["frame_frame_width"]) echo "フレーム幅" . he($row_frame["frame_frame_width"]); ?>
            </li>
            <li class="frame-detail_desc_true__optional">
            <?php if($row_frame["frame_lens_height"]) echo "レンズ高" . he($row_frame["frame_lens_height"]); ?>
            </li>
            <?php if ($_SESSION["user_id"]):?>
              <?php if($edge1_thick == $max_edge): ?>
            <li class="frame-detail__thickness frame-detail_desc_true__thickness">中心: <span class="frame-detail__max"><?= round($edge1_thick, 1); ?></span>端: <span class="frame-detail__min"><?= round($edge2_thick, 1); ?></span>
              <?php else: ?>
            <li class="frame-detail__thickness frame-detail_desc_true__thickness">中心: <span class="frame-detail__min"><?= round($edge1_thick, 1); ?></span>端: <span class="frame-detail__max"><?= round($edge2_thick, 1); ?></span>
              <?php endif; ?>
            </li><!--.frame-detail__thickness-->
            <?php endif; ?>
          </ul><!--.frame-detail__info-->
        </div><!--.frame-detail-->
      </div><!--.frame-detail__layout-->
      <ul class="frame-detail__action">
        <li class="frame-detail__action__price">
          <span><i class="fa fa-jpy" aria-hidden="true"></i><?= he($row_frame["frame_price"]) ?></span>
        </li>
        <li class="frame-detail__action__seller">
          <a class="frame-detail__action__seller-link" href="<?= he($row_frame['frame_link']) ?>" target="_blank">
            <i class="fa fa-external-link" aria-hidden="true"></i>Buy
          </a>
        </li>
        <li class="frame-detail__action__report">
          <button data-report=<?= $report_removed_flag ?>>
            <i class="fa fa-flag-o frame-detail__action__report-icon" aria-hidden="true"></i>
          </button>
        </li>
        <li class="frame-detail__action__favorite">
          <button data-favorite=<?= $removed_flag ?> <?= $disabled ?>>
            <i class="fa fa-star frame-detail__action__favorite-icon" aria-hidden="true"></i><span class="frame-detail__action__favorite-cnt"><?= he($row_frame["favorite_cnt"]) ?></span>
          </button>
        </li>
      </ul><!--.frame-detail__action-->
      <!--<div id="result"></div>-->
    </main>
  </div>
<?php require("footer.php"); ?>