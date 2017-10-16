<?php require_once("../system/common.php"); ?>
<?php
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("mode", "frame_id","frame_poster_id", "frame_title", "frame_content","frame_price","frame_image","frame_link","frame_lens_width","frame_lens_height","frame_bridge_width","frame_temple_length","frame_frame_width", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    画像のアップロードがある時だけ変数に格納
-----------------------------------------------------------------------------*/
if ($_FILES["frame_image"]) {
  $image = $_FILES["frame_image"];
  $image_name = date("YmdHis") . $image["name"];
}
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    動作モードを$modeに格納（空白→新規、chnage→修正）
-----------------------------------------------------------------------------*/
$mode = $request["mode"];
/*=============================================================================
    <<ページ読み込み時に各値を取得
=============================================================================*/
try {
  $sql = "select * from frames where frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
if ($row_frame/* && $form["frame_poster_id"] === $_SESSION["user_id"]*/) {
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
    $form = array();
    $form["frame_id"] = $row_frame["frame_id"];
    $form["frame_poster_id"] = $row_frame["frame_poster_id"];
    $form["frame_title"] = $row_frame["frame_title"];
    $form["frame_content"] = $row_frame["frame_content"];
    $form["frame_price"] = $row_frame["frame_price"];
    $form["frame_image"] = $row_frame["frame_image"];
    $form["frame_link"] = $row_frame["frame_link"];
    $form["frame_lens_width"] = $row_frame["frame_lens_width"];
    $form["frame_lens_height"] = $row_frame["frame_lens_height"];
    $form["frame_bridge_width"] = $row_frame["frame_bridge_width"];
    $form["frame_temple_length"] = $row_frame["frame_temple_length"];
    $form["frame_frame_width"] = $row_frame["frame_frame_width"];
    //古い画像削除用にファイル名をセッションに取得しておく
    $_SESSION["old_image"] = $row_frame["frame_image"];
  }
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
/*=============================================================================
    ページ読み込み時に各値を取得>>
=============================================================================*/
/*-----------------------------------------------------------------------------
    削除モード時
-----------------------------------------------------------------------------*/
if ($mode == "delete"/* && $form["frame_poster_id"] === $_SESSION["user_id"]*/) {
  try {
    $pdo->beginTransaction();
    $sql = "delete from frames where frame_id = :frame_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
    $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stmt = null;
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    die("エラー; " . $e->getMessage());
  }
  header("Location: frame_list.php");
  exit;
}
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    <<送信ボタンが押されて、エラーメッセージがない時
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
  //空欄チェック
  if ($request["frame_title"] == "") $error_message .= "フレーム名を入力してください\n";
  //if ($request["frame_content"] == "") $error_message .= "コメントを入力してください\n";
  if ($request["frame_price"] == "") $error_message .= "価格を入力してください\n";
  //if ($request["frame_image"] == "") $error_message .= "画像をアップロードしてください\n";
  //ブラウザが判断するファイルタイプがjpegじゃなかったら、もしくは拡張子がjpegじゃなかったら
  if (!$image["error"]) {
    if (($image["type"] != "image/jpeg"  && $image["type"] != "image/pjpeg") || strtolower(mb_strrchr($image["name"], ".", false)) != ".jpg") $error_message .= "画像(jpegファイル)をアップロードして下さい\n";
    //画像サイズを制限
    if ($image["size"] > 10*1024*1024) $error_message .= "画像サイズは10MB以下にして下さい\n";
  }
  if ($request["frame_link"] == "") $error_message .= "商品リンクを入力してください\n";
  if ($request["frame_lens_width"] == "") $error_message .= "レンズ幅を入力してください\n";
  //if ($request["frame_lens_height"] == "") $error_message .= "レンズの高さを入力してください\n";
  if ($request["frame_bridge_width"] == "") $error_message .= "ブリッジ幅を入力してください\n";
  if ($request["frame_temple_length"] == "") $error_message .= "テンプルの長さを入力してください\n";
  //if ($request["frame_frame_width"] == "") $error_message .= "フレーム幅を入力してください\n";
}
/*-----------------------------------------------------------------------------
    フォーム項目が空欄の場合、NULLに設定
-----------------------------------------------------------------------------*/
if ($request["frame_content"] == "") $request["frame_content"] = null;
if ($request["frame_lens_height"] == "") $request["frame_lens_height"] = null;
if ($request["frame_frame_width"] == "") $request["frame_frame_width"] = null;
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    <<送信ボタンが押されて、エラーメッセージがない時、新規登録or修正開始
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
if (isset($request["send"]) && $error_message == "") {
/*-----------------------------------------------------------------------------
    画像の投稿処理
-----------------------------------------------------------------------------*/
  if ($image["error"] == 0 && $mode == "change"){
    //古い画像削除
    unlink("../images/frames/{$_SESSION["old_image"]}");
    unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
    move_uploaded_file($image["tmp_name"], "../images/frames/{$image_name}");
    //サムネ作成
    $original_image = imagecreatefromjpeg("../images/frames/{$image_name}");
    list($original_w, $original_h) = getimagesize("../images/frames/{$image_name}");
    //ファイルサイズがない時はエラー表示、それ以外はサムネ作成
    if ($original_w == 0 || $original_h == 0) {
      $error_message .= "画像ファイルではありません\n";
      unlink("../images/frames/{$image_name}");
    } else {
      //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
      $thumb_w = 320;
      $thumb_h = $original_h*$thumb_w/$original_w;
      $thumb_image = imagecreatetruecolor($thumb_w, $thumb_h);
      imagecopyresized($thumb_image, $original_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
      imagejpeg($thumb_image, "../images/frames/thumb_{$image_name}");
    }
  }
/*=============================================================================
    <<データベース更新
=============================================================================*/
  try {
    $pdo->beginTransaction();
/*-----------------------------------------------------------------------------
    修正モード
-----------------------------------------------------------------------------*/
    if ($mode == "change"/* && $form["frame_poster_id"] === $_SESSION["user_id"]*/) {
      $sql = "update frames set frame_poster_id = :frame_poster_id, frame_title = :frame_title, frame_content = :frame_content, frame_price = :frame_price, frame_image = :frame_image, frame_link = :frame_link, frame_lens_width = :frame_lens_width, frame_lens_height = :frame_lens_height, frame_bridge_width = :frame_bridge_width, frame_temple_length = :frame_temple_length, frame_frame_width = :frame_frame_width where frame_id = :frame_id";
      $stmt = $pdo->prepare($sql);
      $request["frame_poster_id"] = $_SESSION["user_id"];
      $stmt->bindValue(":frame_poster_id", $request["frame_poster_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_title", $request["frame_title"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_content", $request["frame_content"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_price", $request["frame_price"], PDO::PARAM_INT);
      //画像の更新があれば新しいのに、なければ古い画像のまま
      if ($image["error"] == 0) {
        $stmt->bindValue(":frame_image", $image_name, PDO::PARAM_STR);
      } else {
        $stmt->bindValue(":frame_image", $_SESSION["old_image"], PDO::PARAM_STR);
      }
      $stmt->bindValue(":frame_link", $request["frame_link"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_lens_width", $request["frame_lens_width"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_lens_height", $request["frame_lens_height"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_bridge_width", $request["frame_bridge_width"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_temple_length", $request["frame_temple_length"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_frame_width", $request["frame_frame_width"], PDO::PARAM_INT);
      $stmt->execute();
/*-----------------------------------------------------------------------------
    新規登録モード
-----------------------------------------------------------------------------*/
    } else {
      $sql = "insert into frames (frame_poster_id, frame_title, frame_content, frame_price, frame_image, frame_link, frame_lens_width, frame_lens_height, frame_bridge_width, frame_temple_length, frame_frame_width) values (:frame_poster_id, :frame_title, :frame_content, :frame_price, :frame_image, :frame_link, :frame_lens_width, :frame_lens_height, :frame_bridge_width, :frame_temple_length, :frame_frame_width)";
      $stmt = $pdo->prepare($sql);
      $request["frame_poster_id"] = $_SESSION["user_id"];
      $stmt->bindValue(":frame_poster_id", $request["frame_poster_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_title", $request["frame_title"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_content", $request["frame_content"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_price", $request["frame_price"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_image", $image_name, PDO::PARAM_STR);
      $stmt->bindValue(":frame_link", $request["frame_link"], PDO::PARAM_STR);
      $stmt->bindValue(":frame_lens_width", $request["frame_lens_width"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_lens_height", $request["frame_lens_height"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_bridge_width", $request["frame_bridge_width"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_temple_length", $request["frame_temple_length"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_frame_width", $request["frame_frame_width"], PDO::PARAM_INT);
      $stmt->execute();
      //新規登録後にframe_idをゲット
      $form["frame_id"] = $pdo->lastInsertId("frame_id");
      // 新規作成が成功したら、修正モードにして直近のデータを修正できるようにする
      $mode = "change";
    }
    $stmt = null;
    $pdo->commit();
  } catch (PDOException $e) {
    // エラー発生時
    $pdo->rollBack();
    die("エラー: " . $e->getMessage());
  }
/*=============================================================================
    データベース更新>>
=============================================================================*/
/*-----------------------------------------------------------------------------
    アップデート後の各値を取得
-----------------------------------------------------------------------------*/
//  if ($form["frame_poster_id"] === $_SESSION["user_id"]) {
    try {
      $sql = "select * from frames where frame_id = :frame_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
      $stmt->execute();
      $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = null;
      if ($row_user) {
        $form["frame_id"] = $row_frame["frame_id"];
        $form["frame_title"] = $row_frame["frame_title"];
        $form["frame_content"] = $row_frame["frame_content"];
        $form["frame_price"] = $row_frame["frame_price"];
        $form["frame_image"] = $row_frame["frame_image"];
        $form["frame_link"] = $row_frame["frame_link"];
        $form["frame_lens_width"] = $row_frame["frame_lens_width"];
        $form["frame_lens_height"] = $row_frame["frame_lens_height"];
        $form["frame_bridge_width"] = $row_frame["frame_bridge_width"];
        $form["frame_temple_length"] = $row_frame["frame_temple_length"];
        $form["frame_frame_width"] = $row_frame["frame_frame_width"];
        $_SESSION["old_image"] = $row_frame["user_icon"];
      } else {
        die("異常なアクセスです");
      }
    } catch (PDOException $e) {
      die("エラー: " . $e->getMessage());
    }
  $page_message = "登録が完了しました";
  }
//}
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    送信ボタンが押されて、エラーメッセージがない時、新規登録or修正終了>>
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
?>
<?php $page_title = "フレーム編集";?>
<?php require("header.php"); ?>
<div class="main-wrap">
  <main>
    <a class="frame-list-list-btn" href="frame_list.php">フレーム一覧へ戻る</a>
    <p>
      <?= he($page_message) ?>
    </p>
    <p class="attention">
      <?= nl2br(he($error_message)) ?>
    </p>
    <?php if ($mode == "change"): ?>
    <p>
      フレームID【<?= he($form["frame_id"]) ?>】を修正しています
    </p>
    <?php endif; ?>
    <form class="frame-edit" enctype="multipart/form-data" action="frame_edit.php" method="post">
      <div>
        <label for="hure-mumei">フレーム名<span class="attention">【必須】</span></label>
        <input type="text" name="frame_title" id="hure-mumei" size="30" value="<?= he($form['frame_title']); ?>">
      </div>
      <div>
        <label for="komento">コメント</label>
        <textarea name="frame_content" id="komento" rows="5" cols="20"><?= he($form["frame_content"]); ?></textarea>
      </div>
      <div>
        <label for="kakaku">価格(円)<span class="attention">【必須】</span></label>
        <input type="number" name="frame_price" id="kakaku" max="99999" value="<?= he($form['frame_price']); ?>">
      </div>
      <div>
        <label for="gazou">画像<span class="attention">【必須】</span></label>
        <?php if ($image["error"] === 0 && $error_message === ""): ?>
        <p><img src='<?= "../images/frames/" . he($image_name) ?>'></p>
        <input type="file" name="frame_image" id="aikon">
        <?php else : ?>
        <p><img src="<?= '../images/frames/' . he($form["frame_image"]) ?>"></p>
        <input type="file" name="frame_image" id="aikon">
        <?php endif; ?>
      </div>
      <div>
        <label for="shohinrinku">商品リンク<span class="attention">【必須】</span></label>
        <input type="text" name="frame_link" id="shohinrinku" size="100" value="<?= he($form['frame_link']); ?>">
      </div>
      <div>
        <label for="renzuhaba">レンズ幅(mm)<span class="attention">【必須】</span></label>
        <input type="number" name="frame_lens_width" id="renzuhaba" max="999" value="<?= he($form['frame_lens_width']); ?>">
      </div>
      <div>
        <label for="renzunotakasa">レンズの高さ(mm)</label>
        <input type="number" name="frame_lens_height" id="renzunotakasa" max="999" value="<?= he($form['frame_lens_height']); ?>">
      </div>
      <div>
        <label for="burijjihaba">ブリッジ幅(mm)<span class="attention">【必須】</span></label>
        <input type="number" name="frame_bridge_width" id="burijjihaba" max="999" value="<?= he($form['frame_bridge_width']); ?>">
      </div>
      <div>
        <label for="tenpurunonagasa">テンプルの長さ(mm)<span class="attention">【必須】</span></label>
        <input type="number" name="frame_temple_length" id="tenpurunonagasa" max="999" value="<?= he($form['frame_temple_length']); ?>">
      </div>
      <div>
        <label for="hure-muhaba">フレーム幅(mm)</label>
        <input type="number" name="frame_frame_width" id="hure-muhaba" max="999" value="<?= he($form['frame_frame_width']); ?>">
      </div>
      <div>
        <input type="submit" name="send" value="送信する">
        <input type="hidden" name="mode" value="<?= he($mode); ?>">
        <input type="hidden" name="frame_id" value="<?= he($form['frame_id']); ?>">
      </div>
    </form>
  </main>
</div>
<?php require("footer.php"); ?>