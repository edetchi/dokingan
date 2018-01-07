<?php
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("mode", "frame_id","frame_poster_id","frame_price","frame_image","frame_link","frame_lens_width","frame_lens_height","frame_bridge_width","frame_temple_length","frame_frame_width", "send");
$request = whitelist($whitelists);
/*=============================================================================
    <<画像のアップロードがある時に行う事前処理
=============================================================================*/
//エラー避け
$_FILES["frame_image"] = (!empty($_FILES["frame_image"])) ? $_FILES["frame_image"] : "";
//画像アップ時に変数に格納
if ($_FILES["frame_image"]) {
  $image = $_FILES["frame_image"];
  //print('$image');
  //print("<br>");
  //var_dump($image);
  //print("<br><br>");
  $image_tmp = $image["tmp_name"];
  //print('$image_tmp');
  //print("<br>");
  //var_export($image_tmp);
  //print("<br><br>");
/*-----------------------------------------------------------------------------
    画像の名前用配列を用意
-----------------------------------------------------------------------------*/
  $image_names = array();
  foreach ($image["name"] as $key => $value) {
    //画像の拡張子をファイル名から抽出
    $image_str_extension = substr($value, strrpos($value, '.') + 1);
    //画像から拡張子を除いたファイル名をゲット
    $image_basename = str_replace(".{$image_str_extension}", "", $value);
    //拡張子をjpgにして時間をプリフィックスにして画像名を作成、ベースのファイル名がない時は空白
    $image_name = (!empty($image_basename)) ? date("YmdHis") . $image_basename . ".jpg" : "";
    //var_export($image_basename);
    //最後のinputが空白の場合、$image_namesに名前を追加しない
    if (!empty($image_basename)) $image_names[] = $image_name;
  }
  //print('$image_names');
  //print("<br>");
  //var_export($image_names);
  //print("<br><br>");
  //データベース登録用に配列を文字列に変換し、変数に格納
  //print('$image_names_imploded');
  //print("<br>");
  $image_names_imploded = toggleStrArray($image_names);
  //var_dump($image_names_imploded);
  //print("<br><br>");
}
/*=============================================================================
    画像のアップロードがある時に行う事前処理>>
=============================================================================*/
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
  $sql = "
    SELECT
      *
    FROM
      frames
    WHERE
      frame_id = :frame_id
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
/*-----------------------------------------------------------------------------
    投稿者以外がフレームデータを編集、削除しようとすると転送、select後の最初の処理に実施
-----------------------------------------------------------------------------*/
if (!empty($mode) && $row_frame["frame_poster_id"] !== $_SESSION["user_id"]) {
  header("Location: frame_list.php");
  exit;
}
/*-----------------------------------------------------------------------------
    frame_idで該当するデータがある時、画像とメッセージ系の初期処理
-----------------------------------------------------------------------------*/
if ($row_frame/* && $form["frame_poster_id"] === $_SESSION["user_id"]*/) {
  //古い画像削除用にファイル名をセッションに取得しておく
  $_SESSION["old_image"] = $row_frame["frame_image"];
  //フレーム編集の初見時のコメントセット
  if (empty($request["send"])) $page_msgs[] = "フレームID【{$request['frame_id']}】を修正しています";
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
    $sql = "
      DELETE FROM
        frames
      WHERE
        frame_id = :frame_id
    ";
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
  deleteImages($_SESSION["old_image"]);
  //unlink("../images/frames/{$_SESSION["old_image"]}");
  //unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
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
  //画像のアップロードがある時の処理開始
  //画像のアップロードがある時、$_FILES["name"]["error"]で得られる連想配列の値が0となる
  //print('array_search(0, $image["error"])');
  //print("<br>");
  //var_export(array_search(0, $image["error"]));
  //print("<br><br>");
  if (array_search(0, $image["error"]) !== false) {
    //拡張子のバリデーションチェック
    foreach ($image_tmp as $key => $value) {
      if (!empty($value)) {
        //文字列から抽出した拡張子($image_str_extension)と$_FILE["type"]の拡張子は偽装できるので、getimagesizeで本当の拡張子ゲット
        //print('getimagesize($value)');
        //print("<br>");
        //var_export(getimagesize($value));
        //print("<br><br>");
        $image_extension = str_replace("image/", "", getimagesize($value)["mime"]);
        //var_dump($image_extension);
        //許可された拡張子か関数でチェック
        if (imageExtensionFlag($image_extension) == 0) {
          $error_msgs[] = "【{$image['name'][$key]}】拡張子は、jpg, jpeg, gif, pngのものにしてください";
          //unlink($image_tmp["{$key}"]);
        }
      }
    }
    //画像サイズのチェック
    foreach ($image["size"] as $key => $value) {
      //画像サイズを制限(5MB以下)
      if ($value > 5*1024*1024) {
      $error_msgs[] = "【{$image['name'][$key]}】画像サイズは、5MB以下にして下さい";
      //unlink($image_tmp["{$key}"]);
      }
    }
    //画像枚数のチェック
    $uploaded_count = 0;
    foreach ($image["error"] as $key => $value) {
      if ($value == 0) ++$uploaded_count;
    }
    if ($uploaded_count > 10) $error_msgs[] = "画像の登録は10枚までにしてください";
    //var_dump($uploaded_count);
  }
  //新規登録時のみ画像必須、array_searchは配列中に該当データがなければfalseを返す
  if (empty($mode) && array_search(0, $image["error"]) === false) $error_msgs[] = "画像を選択してください";
  //商品リンクのチェック
  if (empty($request["frame_link"])) $error_msgs[] = "商品リンクを入力してください";
  if (!empty($request["frame_link"]) && !preg_match("/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $request["frame_link"])) $error_msgs[] = "商品リンクのURL形式が正しくありません";
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
  //print('array_search(0, $image["error"]) !== false');
  //print("<br>");
  //var_export(array_search(0, $image["error"]) !== false);
  //print("<br><br>");
  //画像のアップロードがある時の処理開始
  if (array_search(0, $image["error"]) !== false){
    //画像が複数ある時、$_FILESは多次元の連想配列になり、値に格納される配列のインデックスがファイルの番号と関係するので、ファイルの数count($image_tmp)分forで回す
    for ($i = 0; $i < count($image_tmp); ++$i) {
      //print('$image["error"][$i] === 0');
      //print("<br>");
      //var_export($image['error'][$i] === 0);
      //print("<br><br>");
      //画像データの投稿がある時のみ実行
      if ($image['error'][$i] === 0) {
        //本物の画像拡張子をgetimagesizeでゲットし、拡張子だけ(jpg, png, gif等)に整形
        //getimagesize($image_tmp['tmp_name'][0,1,2...])
        $image_extension = str_replace("image/", "", getimagesize($image_tmp[$i])["mime"]);
        //print('$image_tmp[$i]');
        //print("<br>");
        //var_export($image_tmp[$i]);
        //print("<br><br>");
        //画像リソースを拡張子に応じて作成
        //jpeg, jpg
        if ($image_extension == "jpeg" || $image_extension == "jpg") $original_image = imagecreatefromjpeg($image_tmp[$i]);
        //png
        if ($image_extension == "png") $original_image = imagecreatefrompng($image_tmp[$i]);
        //gif
        if ($image_extension == "gif") $original_image = imagecreatefromgif($image_tmp[$i]);
        //print('$original_image');
        //print("<br>");
        //var_export($original_image);
        //print("<br><br>");
        //画像サイズを変数に格納
        list($original_w, $original_h) = getimagesize($image_tmp[$i]);
        //リソースからリサイズした画像作成
        //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
        $resized_w = 600;
        $resized_h = $original_h*$resized_w/$original_w;
        $resized_image = imagecreatetruecolor($resized_w, $resized_h);
        imagecopyresized($resized_image, $original_image, 0, 0, 0, 0, $resized_w, $resized_h, $original_w, $original_h);
        imagejpeg($resized_image, "../images/frames/{$image_names[$i]}");
        //リソースからサムネの画像作成
        //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
        $thumb_w = 240;
        $thumb_h = $original_h*$thumb_w/$original_w;
        $thumb_image = imagecreatetruecolor($thumb_w, $thumb_h);
        imagecopyresized($thumb_image, $original_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
        imagejpeg($thumb_image, "../images/frames/thumb_{$image_names[$i]}");
        //画像リソースを解放、オリジナル画像のリソース・メイン画像のリソース・サムネ画像のリソース
        imagedestroy($original_image);
        imagedestroy($resized_image);
        imagedestroy($thumb_image);
      }
    }
    //古い画像削除
    //unlink("../images/frames/{$_SESSION["old_image"]}");
    //unlink("../images/frames/thumb_{$_SESSION["old_image"]}");
    deleteImages($_SESSION["old_image"]);
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
      $sql = "
        UPDATE
          frames
        SET
          frame_poster_id = :frame_poster_id,
          frame_price = :frame_price,
          frame_image = :frame_image,
          frame_link = :frame_link,
          frame_lens_width = :frame_lens_width,
          frame_lens_height = :frame_lens_height,
          frame_bridge_width = :frame_bridge_width,
          frame_temple_length = :frame_temple_length,
          frame_frame_width = :frame_frame_width
        WHERE
          frame_id = :frame_id
      ";
      $stmt = $pdo->prepare($sql);
      $request["frame_poster_id"] = $_SESSION["user_id"];
      $stmt->bindValue(":frame_poster_id", $request["frame_poster_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_id", $request["frame_id"], PDO::PARAM_INT);
      $stmt->bindValue(":frame_price", $request["frame_price"], PDO::PARAM_INT);
      //画像の更新があれば新しいのに、なければ古い画像のまま
      if (array_search(0, $image["error"]) !== false) {
        $stmt->bindValue(":frame_image", $image_names_imploded, PDO::PARAM_STR);
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
      $sql = "
        INSERT INTO frames (
          frame_poster_id,
          frame_price,
          frame_image,
          frame_link,
          frame_lens_width,
          frame_lens_height,
          frame_bridge_width,
          frame_temple_length,
          frame_frame_width
        )
        VALUES (
          :frame_poster_id,
          :frame_price,
          :frame_image,
          :frame_link,
          :frame_lens_width,
          :frame_lens_height,
          :frame_bridge_width,
          :frame_temple_length,
          :frame_frame_width
        )
      ";
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
    $sql = "
      SELECT
        *
      FROM
        frames
      WHERE
        frame_id = :frame_id
    ";
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
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    送信ボタンが押されて、エラーメッセージがない時、新規登録or修正終了>>
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
/*-----------------------------------------------------------------------------
    アップデート後の各値を取得(エラー時の各値を取得)
-----------------------------------------------------------------------------*/
} else if (!empty($error_msgs)) {
  $row_frame['frame_price'] = $request["frame_price"];
  //$row_frame["frame_image"] = $request["frame_image"];
  $row_frame["frame_link"] = $request["frame_link"];
  $row_frame["frame_lens_width"] = $request["frame_lens_width"];
  $row_frame["frame_lens_height"] = $request["frame_lens_height"];
  $row_frame["frame_bridge_width"] = $request["frame_bridge_width"];
  $row_frame["frame_temple_length"] = $request["frame_temple_length"];
  $row_frame["frame_frame_width"] = $request["frame_frame_width"];
}
/*-----------------------------------------------------------------------------
    データベースから得た画像データの文字列をループようにexplodeして配列にする、序でにキーにfigcaptionで使うものを格納
-----------------------------------------------------------------------------*/
$row_frame["frame_image"] = toggleStrArray($row_frame["frame_image"]);
foreach ($row_frame["frame_image"] as $key => $value) {
  //1枚目の時、イメージ用配列を初期化、キャプションをメインにする
  if ($key === 0) {
    $row_frame["frame_image"] = array();
    $key = "メイン";
  } else {
    $key = ++$key . "枚目";
  }
  //イメージのループ用連想配列
  $row_frame["frame_image"][$key] = $value;
}
//var_export($row_frame["frame_image"]);
//var_export($row_frame["frame_image"]["メイン"]);
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
      <div class="image-upload">
        <label for="gazou">画像<span class="attention">*</span>(画像1枚5MB以内、10枚まで登録できます)</label>
        <?php if ($row_frame["frame_image"]): ?>
        <?php foreach ($row_frame["frame_image"] as $key => $value) : ?>
        <figure>
          <figcaption><?= $key ?></figcaption>
          <img src="<?= '../images/frames/' . he($value) ?>">
        </figure>
        <?php endforeach; ?>
        <?php endif; ?>
      <input type="file" name="frame_image[]" id="aikon" accept="image/png, image/jpeg, image/gif">
        <div class="selected-images-result"></div>
      </div>
      <div>
        <label for="shohinrinku">商品リンク<span class="attention">*</span></label>
        <input type="text" name="frame_link" id="shohinrinku" size="100" value="<?= he($row_frame['frame_link']); ?>">
      </div>
      <div>
        <label for="renzuhaba">レンズ幅(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_lens_width" id="renzuhaba" max="70" min="30" value="<?= he($row_frame['frame_lens_width']); ?>">
      </div>
      <div>
        <label for="burijjihaba">ブリッジ幅(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_bridge_width" id="burijjihaba" max="40" min="10" value="<?= he($row_frame['frame_bridge_width']); ?>">
      </div>
      <div>
        <label for="tenpurunonagasa">テンプルの長さ(mm)<span class="attention">*</span></label>
        <input type="number" name="frame_temple_length" id="tenpurunonagasa" max="160" min="110" value="<?= he($row_frame['frame_temple_length']); ?>">
      </div>
      <div>
        <label for="renzunotakasa">レンズの高さ(mm)</label>
        <input type="number" name="frame_lens_height" id="renzunotakasa" max="60" min="20" value="<?= he($row_frame['frame_lens_height']); ?>">
      </div>
      <div>
        <label for="hure-muhaba">フレーム幅(mm)</label>
        <input type="number" name="frame_frame_width" id="hure-muhaba" max="160" min="110" value="<?= he($row_frame['frame_frame_width']); ?>">
      </div>
      <div>
        <input type="submit" name="send" value="送信する">
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
        <input type="hidden" name="mode" value="<?= he($mode); ?>">
        <input type="hidden" name="frame_id" value="<?= he($request['frame_id']); ?>">
      </div>
    </form>
  </main>
</div>
<?php require("footer.php"); ?>