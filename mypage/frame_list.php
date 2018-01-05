<?php require_once("../system/common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    フレーム一覧用データ取得
-----------------------------------------------------------------------------*/
try {
    $sql = "select * from frames where frame_poster_id = :frame_poster_id order by frame_created desc";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":frame_poster_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
		die("エラー: " . $e->getMessage());
}
?>
<?php $page_title = "フレーム管理";?>
<?php require("header.php"); ?>
<div class="main-wrap">
  <main>
    <a class="frame-list__add-btn" href="frame_edit.php">フレームを追加する</a>
    <?php while ($row_frame = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="frame-list__layout">
      <div class="frame-list frame-list_desc_true">
        <a href="../detail.php?frame_id=<?= he($row_frame["frame_id"]) ?>">
          <img class="frame-list__image" src='<?= "../images/frames/" . he(getMainImage($row_frame["frame_image"])) ?>'>
        </a>
        <ul class="frame-list__info">
          <li class="frame-list__price">
            <span><i class="fa fa-jpy" aria-hidden="true"></i><?= he(number_format($row_frame["frame_price"])) ?></span>
          </li>
          <li class="frame-list__size">
            <?= he($row_frame["frame_lens_width"]) ?>□<?= he($row_frame["frame_bridge_width"]) ?>-<?= he($row_frame["frame_temple_length"]) ?>
          </li>
          <?php if($row_frame["frame_frame_width"]): ?>
          <li class="frame-list_desc_true__optional">
            フレーム幅<?= he($row_frame["frame_frame_width"]) ?>
          </li>
          <?php endif; ?>
          <?php if($row_frame["frame_lens_height"]): ?>
          <li class="frame-list_desc_true__optional">
            レンズ高<?= he($row_frame["frame_lens_height"]) ?>
          </li>
          <?php endif; ?>
          <li class="frame-list__seller">
            <a class="frame-list__seller-link" href="<?= he($row_frame['frame_link']) ?>" target="_blank">
              <i class="fa fa-external-link" aria-hidden="true"></i>Buy
            </a>
          </li>
        </ul><!--.frame-list__info-->
      </div><!--.frame-list-->
    </div><!--.frame-list__layout-->
    <ul class="frame-list__admin-action">
      <li class="frame-list__admin-action__edit">
        <a href="frame_edit.php?mode=change&frame_id=<?= he($row_frame["frame_id"]) ?>">
          編集
        </a>
      </li>
      <li class="frame-list__admin-action__delete">
        <a href="frame_edit.php?mode=delete&frame_id=<?= he($row_frame["frame_id"]) ?>">
          削除
        </a>
      </li>
    </ul><!--.frame-list__admin-action-->
    <!--<div id="result"></div>-->
  <?php endwhile; ?>
  </main>
</div>
<?php require("footer.php"); ?>