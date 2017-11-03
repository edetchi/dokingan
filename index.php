<?php
//ライブラリの読み込み
require_once("system/common.php");
/*=============================================================================
    <<フレーム一覧用データ取得
=============================================================================*/
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
  $sql = "select * from frames left join users on frames.frame_poster_id = users.user_id order by frame_updated desc";
/*=============================================================================
    フレーム一覧用データ取得>>
=============================================================================*/
$page_title = "トップ";
require("header.php");
?>
  <div class="main-wrap">
    <main>
<?php
/*=============================================================================
    <<フレームデータ表示用ループ
=============================================================================*/
?>
    <?php while($row_frame = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <?php
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
/*=============================================================================
    <<body部
=============================================================================*/
    ?>
      <div class="frame-list__layout">
      <div class="frame-list">
        <a href="detail.php?frame_id=<?= he($row_frame["frame_id"]) ?>">
          <img class="frame-list__image" src='<?= "./images/frames/" . he($row_frame["frame_image"]) ?>'>
        </a>
        <ul class="frame-list__info">
          <?php if (!$_SESSION["user_id"]):?>
          <li class="frame-list__userid"><i class="fa fa-user-o" aria-hidden="true"></i><?= he($row_frame["user_loginid"]) ?></li>
          <?php else: ?>
            <?php if($edge1_thick == $max_edge): ?>
          <li class="frame-list__thickness">中心: <span class="frame-list__max"><?= round($edge1_thick, 1); ?></span>端: <span class="frame-list__min"><?= round($edge2_thick, 1); ?></span>
            <?php else: ?>
          <li class="frame-list__thickness">中心: <span class="frame-list__min"><?= round($edge1_thick, 1); ?></span>端: <span class="frame-list__max"><?= round($edge2_thick, 1); ?></span>
            <?php endif; ?>
          </li><!--.frame-list__thickness-->
          <?php endif; ?>
          <li class="frame-list__size">
            <?= he($row_frame["frame_lens_width"]) ?>□<?= he($row_frame["frame_bridge_width"]) ?>-<?= he($row_frame["frame_temple_length"]) ?>
          </li>
          <li class="frame-list__price">
            <span><?= he($row_frame["frame_price"]) ?></span>
            <span><i class="fa fa-star-o" aria-hidden="true"></i><?= he("64") ?></span>
          </li>
        </ul><!--.frame-list__info-->
      </div><!--.frame-list-->
    </div><!--.frame-list__layout-->
  <?php endwhile;
/*=============================================================================
      <<フレームデータ表示用ループ
=============================================================================*/
  ?>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php
/*=============================================================================
    body部>>
=============================================================================*/
require("footer.php"); ?>
