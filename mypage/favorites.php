<?php
//ライブラリの読み込み
require_once("../system/common.php");
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
  $pdo->commit();
  $stmt = null;
/*-----------------------------------------------------------------------------
    フレームデータ取得
-----------------------------------------------------------------------------*/
  $pdo->beginTransaction();
  $sql = "select * from favorites left join frames on favorites.frame_id = frames.frame_id order by favorites.favorite_updated desc";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $pdo->commit();
} catch (PDOException $e) {
  $pdo->rollBack();
  die("エラー: " . $e->getMessage());
}
/*=============================================================================
    フレーム一覧用データ取得>>
=============================================================================*/
$page_title = "トップページ";
require("header.php");
?>
    <p>
      閲覧ありがとうございます。<br>
      こちらはフレームリフュジーページです。
    </p>
    <h2>メニュー</h2>
    <ul>
      <li>
        <a href="blog.php">ブログ</a>
      </li>
      <li>
        <a href="inquiry.php">お問い合わせ</a>
      </li>
    </ul>
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
    $thick= (pow($max_edge, 2)*abs($user_sph) / (2000*($index - 1))) + $center_thick;
    ?>
    <article>
      <?php if ($_SESSION["user_id"]):?>
      <p><?= he($thick) ?></p>
      <p><?= he($max_edge) ?></p>
      <p><?= he($min_edge) ?></p>
      <?php endif; ?>
      <p><?= he($row_frame["user_loginid"]) ?></p>
      <p><?= he($row_frame["frame_title"]) ?></p>
      <p><?= he(nl2br($row_frame["frame_content"])) ?></p>
      <p><?= he($row_frame["frame_pricee"]) ?></p>
      <p><img src='<?= "../images/frames/" . he($row_frame["frame_image"]) ?>'></p>
      <p><?= he($row_frame["frame_link"]) ?></p>
      <p><?= he($row_frame["frame_lens_width"]) ?></p>
      <p><?= he($row_frame["frame_lens_height"]) ?></p>
      <p><?= he($row_frame["frame_bridge_width"]) ?></p>
      <p><?= he($row_frame["frame_temple_length"]) ?></p>
      <p><?= he($row_frame["frame_frame_width"]) ?></p>
      <time><?= he($row_frame["frame_created"]) ?></time>
      <time><?= he($row_frame["frame_updated"]) ?></time>
    </article>
    <hr>
    <?php endwhile; ?>
<?php require("footer.php"); ?>