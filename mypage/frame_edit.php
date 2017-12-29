<?php
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("mode", "frame_id","frame_poster_id","frame_price","frame_image","frame_link","frame_lens_width","frame_lens_height","frame_bridge_width","frame_temple_length","frame_frame_width", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    画像のアップロードがある時だけ変数に格納
-----------------------------------------------------------------------------*/
//エラー避け
$_FILES["frame_image"] = (!empty($_FILES["frame_image"])) ? $_FILES["frame_image"] : "";
//画像アップ時に変数に格納
if ($_FILES["frame_image"]) {
  $image = $_FILES["frame_image"];
  $image_tmp = $image["tmp_name"];
  //画像の拡張子をファイル名から抽出
  $image_str_extension = substr($image["name"], strrpos($image["name"], '.') + 1);
  //画像から拡張子を除いたファイル名をゲット
  $image_basename = str_replace(".{$image_str_extension}", "", $image["name"]);
  //拡張子をjpgにして時間をプリフィックスにして画像名を作成
  $image_name = date("YmdHis") . $image_basename . ".jpg";
  //var_export($image_basename);
}
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
/*-----------------------------------------------------------------------------
    動作モードをgetで受けて$modeに格納（空白→新規、chnage→修正、delete→削除）
-----------------------------------------------------------------------------*/
$mode = $request["mode"];
/*=============================================================================
    <<getでframe_idの値が与えられている時、各値を取得
=============================================================================*/
try {
  $sql = "select * from frames where frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
if ($row_frame/* && $form["frame_poster_id"] === $_SESSION["user_id"]*/) {
  //古い画像削除用にファイル名をセッションに取得しておく
  $_SESSION["old_image"] = $row_frame["frame_image"];
  //フレーム編集の初見時のコメントセット
  if (empty($request["send"])) $page_msgs[] = "フレームID【{$request['frame_id']}】を修正しています";
}
/*-----------------------------------------------------------------------------
    投稿者以外がフレームデータを編集、削除できないようにする
-----------------------------------------------------------------------------*/
if (!empty($mode) && $row_frame["frame_poster_id"] !== $_SESSION["user_id"]) {
  header("Location: frame_list.php");
  exit;
}
/*=============================================================================
    getでframe_idの値が与えられている時、各値を取得>>
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
  //古い画像削除
  unlink("../images/frames/{$_SESSION["old_image"]}");
  unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
  header("Location: frame_list.php");
  exit;
}
/*=============================================================================
    <<フォームのバリデーション
=============================================================================*/
//送信ボタンが押された時の処理
if (isset($request["send"])) {
  //空欄チェック
  if ($request["frame_price"] == "") $error_msgs[] = "価格を入力してください";
  //if ($request["frame_image"] == "") $error_msgs[] = "画像をアップロードしてください";
/*-----------------------------------------------------------------------------
    画像のチェック
-----------------------------------------------------------------------------*/
  if (!$image["error"]) {
    //文字列から抽出した拡張子($image_str_extension)と$_FILE["type"]の拡張子は偽装できるので、getimagesizeで本当の拡張子ゲット
    $image_extension = str_replace("image/", "", getimagesize($image_tmp)["mime"]);
    //var_dump($image_extension);
    if (imageExtensionFlag($image_extension) == 0) {
      $error_msgs[] = "拡張子が、jpg, jpeg, gif, pngの画像ファイルをアップロードしてください";
      unlink($image_tmp);
    }
    //画像サイズを制限
    if ($image["size"] > 5*1024*1024) {
    $error_msgs[] = "画像サイズは5MB以下にして下さい";
    unlink($image_tmp);
    }
  }
  //新規登録時のみ画像必須
  if (empty($mode) && $image["error"] != 0) $error_msgs[] = "画像を選択してください";
  if ($request["frame_link"] == "") $error_msgs[] = "商品リンクを入力してください";
  if ($request["frame_lens_width"] == "") $error_msgs[] = "レンズ幅を入力してください";
  //if ($request["frame_lens_height"] == "") $error_msgs[] = "レンズの高さを入力してください";
  if ($request["frame_bridge_width"] == "") $error_msgs[] = "ブリッジ幅を入力してください";
  if ($request["frame_temple_length"] == "") $error_msgs[] = "テンプルの長さを入力してください";
  //if ($request["frame_frame_width"] == "") $error_msgs .= "フレーム幅を入力してください";
}
/*=============================================================================
    フォームのバリデーション>>
=============================================================================*/
/*-----------------------------------------------------------------------------
    フォーム項目が空欄の場合、NULLに設定(SQLのinteger型は""だとエラーがでるので)
-----------------------------------------------------------------------------*/
if ($request["frame_lens_height"] == "") $request["frame_lens_height"] = null;
if ($request["frame_frame_width"] == "") $request["frame_frame_width"] = null;
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    <<送信ボタンが押されて、エラーメッセージがない時、新規登録or修正開始
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
if (isset($request["send"]) && empty($error_msgs)) {
/*-----------------------------------------------------------------------------
    画像の投稿処理
-----------------------------------------------------------------------------*/
  if ($image["error"] == 0){
    /*
    //フレーム時のみ古い画像を削除
    if ($mode == "change") {
      //古い画像削除
      unlink("../images/frames/{$_SESSION["old_image"]}");
      unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
    }
    */
    //画像リソースを作成
    //jpeg, jpg
    if ($image_extension == "jpeg" || $image_extension == "jpg") $original_image = imagecreatefromjpeg($image_tmp);
    //png
    if ($image_extension == "png") $original_image = imagecreatefrompng($image_tmp);
    //gif
    if ($image_extension == "gif") $original_image = imagecreatefromgif($image_tmp);
    //画像サイズを変数に格納
    list($original_w, $original_h) = getimagesize($image_tmp);
    //リソースからリサイズした画像作成
    //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
    $resized_w = 600;
    $resized_h = $original_h*$resized_w/$original_w;
    $resized_image = imagecreatetruecolor($resized_w, $resized_h);
    imagecopyresized($resized_image, $original_image, 0, 0, 0, 0, $resized_w, $resized_h, $original_w, $original_h);
    imagejpeg($resized_image, "../images/frames/{$image_name}");
    //リソースからサムネの画像作成
    //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
    $thumb_w = 240;
    $thumb_h = $original_h*$thumb_w/$original_w;
    $thumb_image = imagecreatetruecolor($thumb_w, $thumb_h);
    imagecopyresized($thumb_image, $original_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
    imagejpeg($thumb_image, "../images/frames/thumb_{$image_name}");
    //古い画像削除
    unlink("../images/frames/{$_SESSION["old_image"]}");
    unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
  }
/*=============================================================================
    <<データベース更新
=============================================================================*/
  try {
    $pdo->beginTransaction();
/*-----------------------------------------------------------------------------
    修正モード
-----------------------------------------------------------------------------*/
    if ($mode == "change" && $row_frame["frame_poster_id"] === $_SESSION["user_id"]) {
      $sql = "update frames set frame_poster_id = :frame_poster_id, frame_price = :frame_price, frame_image = :frame_image, frame_link = :frame_link, frame_lens_width = :frame_lens_width, frame_lens_height = :frame_lens_height, frame_bridge_width = :frame_bridge_width, frame_temple_length = :frame_temple_length, frame_frame_width = :frame_frame_width where frame_id = :frame_id";
      $stmt = $pdo->prepare($sql);
      $request["frame_poster_id"] = $_SESSION["user_id"];
      $stmt->bindValue(":frame_poster_id", $request["frame_poster_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
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
      $page_msgs[] = "フレームID【{$request['frame_id']}】を修正しました";
/*-----------------------------------------------------------------------------
    新規登録モード
-----------------------------------------------------------------------------*/
    } else {
      $sql = "insert into frames (frame_poster_id, frame_price, frame_image, frame_link, frame_lens_width, frame_lens_height, frame_bridge_width, frame_temple_length, frame_frame_width) values (:frame_poster_id, :frame_price, :frame_image, :frame_link, :frame_lens_width, :frame_lens_height, :frame_bridge_width, :frame_temple_length, :frame_frame_width)";
      $stmt = $pdo->prepare($sql);
      $request["frame_poster_id"] = $_SESSION["user_id"];
      $stmt->bindValue(":frame_poster_id", $request["frame_poster_id"], PDO::PARAM_INT);
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
      $request["frame_id"] = $pdo->lastInsertId("frame_id");
      // 新規作成が成功したら、修正モードにして直近のデータを修正できるようにする
      $mode = "change";
      $page_msgs[] = "登録が完了しました";
      $page_msgs[] = "フレームID【{$request['frame_id']}】を修正しています";
    }
    $stmt = null;
/*-----------------------------------------------------------------------------
    アップデート後の各値を取得
-----------------------------------------------------------------------------*/
    $sql = "select * from frames where frame_id = :frame_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
    $stmt->execute();
    $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
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
    アップデート後の各値を取得(エラー時の各値を取得)
-----------------------------------------------------------------------------*/
} else if (!empty($error_msgs)) {
  $row_frame['frame_price'] = $request["frame_price"];
  $row_frame["frame_image"] = $request["frame_image"];
  $row_frame["frame_link"] = $request["frame_link"];
  $row_frame["frame_lens_width"] = $request["frame_lens_width"];
  $row_frame["frame_lens_height"] = $request["frame_lens_height"];
  $row_frame["frame_bridge_width"] = $request["frame_bridge_width"];
  $row_frame["frame_temple_length"] = $request["frame_temple_length"];
  $row_frame["frame_frame_width"] = $request["frame_frame_width"];
}
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    送信ボタンが押されて、エラーメッセージがない時、新規登録or修正終了>>
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
$page_title = "フレーム編集";
require("header.php");
?>
<div class="main-wrap">
  <main>
    <a class="frame-edit-list-btn" href="frame_list.php">フレーム一覧へ戻る</a>
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
    <form class="frame-edit" enctype="multipart/form-data" action="frame_edit.php" method="post">
      <div>
        <label for="kakaku">価格(円)<span class="attention">*</span></label>
        <input type="number" name="frame_price" id="kakaku" max="99999" value="<?= he($row_frame['frame_price']); ?>">
      </div>
      <div>
        <label for="gazou">画像<span class="attention">*</span></label>
        <?php if ($row_frame["frame_image"]): ?>
        <p><img src="<?= '../images/frames/' . he($row_frame["frame_image"]) ?>"></p>
      <?php endif; ?>
        <input type="file" name="frame_image" id="aikon" multiple>
        <div class="selected-images-result"></div>
      </div>
      <div>
        <label for="shohinrinku">商品リンク<span class="attention">*</span></label>
        <input type="text" name="frame_link" id="shohinrinku" size="100" value="<?= he($row_frame['frame_link']); ?>">
      </div>
      <div>
        <label for="renzuhaba">レンズ幅(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_lens_width" id="renzuhaba" max="999" value="<?= he($row_frame['frame_lens_width']); ?>">
      </div>
      <div>
        <label for="burijjihaba">ブリッジ幅(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_bridge_width" id="burijjihaba" max="999" value="<?= he($row_frame['frame_bridge_width']); ?>">
      </div>
      <div>
        <label for="tenpurunonagasa">テンプルの長さ(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_temple_length" id="tenpurunonagasa" max="999" value="<?= he($row_frame['frame_temple_length']); ?>">
      </div>
      <div>
        <label for="renzunotakasa">レンズの高さ(mm)</label>
        <input type="number" name="frame_lens_height" id="renzunotakasa" max="999" value="<?= he($row_frame['frame_lens_height']); ?>">
      </div>
      <div>
        <label for="hure-muhaba">フレーム幅(mm)</label>
        <input type="number" name="frame_frame_width" id="hure-muhaba" max="999" value="<?= he($row_frame['frame_frame_width']); ?>">
      </div>
      <div>
        <input type="submit" name="send" value="送信する">
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
        <input type="hidden" name="mode" value="<?= he($mode); ?>">
        <input type="hidden" name="frame_id" value="<?= he($request['frame_id']); ?>">
      </div>
    </form>
  </main>
</div>
<?php require("footer.php"); ?>