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
  $stmt = $pdo->query($sql);
  $frames = array();
  while($row_frame = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $frames[] = array(
      "frame_id" => $row_frame["frame_id"],
      "frame_image" => $row_frame["frame_image"],
      "frame_price" => $row_frame["frame_price"],
      "frame_lens_width" => $row_frame["frame_lens_width"],
      "frame_bridge_width" => $row_frame["frame_bridge_width"],
      "frame_temple_length" => $row_frame["frame_temple_length"],
      "user_loginid" => $row_frame["user_loginid"],
      "edge1_thick" => edgeThickness()["edge1_thick"],
      "edge2_thick" => edgeThickness()["edge2_thick"],
    );
  }
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
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
            <img class="frame-list__image" src='<?= "./images/frames/" . he($frame["frame_image"]) ?>'>
          </a>
          <ul class="frame-list__info">
            <li class="frame-list__price">
              <span><?= he($frame["frame_price"]) ?></span>
              <span><i class="fa fa-star-o" aria-hidden="true"></i><?= he("64") ?></span>
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
              中心: <span class="frame-list__max"><?= $frame["edge1_thick"] ?></span>端: <span class="frame-list__min"><?= $frame["edge2_thick"] ?></span>
            </li><!--.frame-list__thickness-->
            <?php endif; ?>
          </ul><!--.frame-list__info-->
        </div><!--.frame-list-->
      </div><!--.frame-list__layout-->
      <?php endforeach; ?>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php
/*=============================================================================
    body部>>
=============================================================================*/
require("footer.php"); ?>
