<?php require_once("./system/common.php");
//var_export($_SERVER['REQUEST_METHOD']);
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("frame_id", "comment_frame_id", "comment_poster_id", "comment_content", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
/*-----------------------------------------------------------------------------
    お気に入り用
-----------------------------------------------------------------------------*/
$_SESSION["frame_id"] = $request["frame_id"];
/*-----------------------------------------------------------------------------
    非ログイン時にお気に入りボタンを押せなくする変数を用意
-----------------------------------------------------------------------------*/
$disabled = (!empty($_SESSION["user_id"])) ? "" : "disabled";
//スパム報告は実装前
$report_removed_flag = "";
/*-----------------------------------------------------------------------------
    エラー避け
-----------------------------------------------------------------------------*/
$request['comment_content'] = (!empty($request['comment_content'])) ? $request['comment_content'] : "";
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (!empty($request["send"])) {
  //空欄チェック
  if (empty($request["comment_content"])) {
    $error_msgs[] = "コメントを入力してください";
  } else {
    if (mb_strlen($request["comment_content"]) > 100) $error_msgs[] = "コメントは100文字以内にしてください";
  }
}
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
  $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
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
/*-----------------------------------------------------------------------------
    コメントを投稿
-----------------------------------------------------------------------------*/
  if (!empty($request["send"]) && empty($error_msgs)){
  //if (!empty($request["send"]) && empty($error_msgs) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "insert into comments (comment_frame_id, comment_poster_id, comment_content) values (:comment_frame_id, :comment_poster_id, :comment_content)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":comment_frame_id", $request["comment_frame_id"], PDO::PARAM_INT);
    $stmt->bindValue(":comment_poster_id", $request["comment_poster_id"], PDO::PARAM_INT);
    $stmt->bindValue(":comment_content", $request["comment_content"], PDO::PARAM_STR);
    $stmt->execute();
    //$page_msgs[] = "コメントを投稿しました";
    //$request["comment_content"] = "";
    header("Location: {$_SERVER['PHP_SELF']}?frame_id={$request['frame_id']}", true, 303);
    exit;
  }
/*-----------------------------------------------------------------------------
    コメントを取得
-----------------------------------------------------------------------------*/
  $sql = "select * from comments left join users on comments.comment_poster_id = users.user_id where comment_frame_id = :frame_id order by comment_created asc";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $comments = array();
  while($row_comment = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row_comment["comment_created"] = str_replace("-", "/", substr($row_comment["comment_created"], 0, 10));
    $comments[] = array(
      "comment_loginid" => $row_comment["user_loginid"],
      "comment_icon" => "./images/users/" . $row_comment["user_icon"],
      "comment_content" => $row_comment["comment_content"],
      "comment_created" => $row_comment["comment_created"],
    );
  }
  //$stmt = null;
  //var_export($comments);
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
?>
<?php $page_title = "フレーム詳細";?>
<?php require("header.php"); ?>
  <div class="main-wrap">
    <main>
      <div class="message">
        <p>
          <?php foreach ($page_msgs as $page_msg): ?>
          <p><?= he($page_msg) ?></p>
          <?php endforeach; ?>
        </p>
        <p class="attention">
          <?php foreach ($error_msgs as $error_msg): ?>
          <p><?= he($error_msg) ?></p>
          <?php endforeach; ?>
        </p>
      </div>
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
      <div class="frame-detail__action__layout">
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
      </div>
      <!--<div id="result"></div>-->
      <div class=".frame-detail__comment__layout">
        <div class="frame-detail__comment">
          <h2 class="frame-detail__comment-title"><i class="fa fa-commenting frame-detail__comment-icon" aria-hidden="true"></i>コメント</h2>
          <ul class="frame-detail__comment-section">
            <?php foreach($comments as $comment): ?>
            <li class="frame-detail__each-comment">
              <div class="frame-detail__each-comment-image__layout">
                <img class="frame-detail__each-comment-image" src="<?= $comment["comment_icon"] ?>">
              </div>
              <div class="frame-detail__each-comment-text">
                <p class="frame-detail__each-comment-user-id"><?= he($comment["comment_loginid"]) ?></p>
                <p class="frame-detail__each-comment-comment frame-detail__each-comment-balloon-left"><?= he($comment["comment_content"]) ?></p>
              </div>
              <div class="frame-detail__each-comment-right">
                <p class="frame-detail__each-comment-right-close"><i class="fa fa-times" aria-hidden="true"></i></p>
                <p class="frame-detail__each-comment-date"><?= he($comment["comment_created"]) ?></p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul><!-- .frame-detail__comment-section -->
          <form class="frame-detail__comment-form" action="detail.php?frame_id=<?= $request["frame_id"] ?>" method="post">
            <div>
              <input class="frame-detail__comment-form__input" type="text" name="comment_content" max="100" placeholder="コメントを入力してください" value="<?= he($request['comment_content']); ?>" <?= $disabled ?>>
              <input class="frame-detail__comment-form__submit" type="submit" name="send" value="送信" <?= $disabled ?>>
              <input type="hidden" name="comment_frame_id" value="<?= he($request["frame_id"]); ?>">
              <input type="hidden" name="comment_poster_id" value="<?= he($_SESSION["user_id"]); ?>">
            </div>
          </form>
        </div><!-- .frame-detail__comment -->
      </div>
    </main>
  </div>
<?php require("footer.php"); ?>