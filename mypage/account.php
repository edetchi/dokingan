<?php
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("user_id", "user_loginid", "user_password", "user_email", "user_sph", "user_pd", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    プロフィール画像のアップロードがある時だけ変数に格納
-----------------------------------------------------------------------------*/
if ($_FILES["user_icon"]) {
    $user_icon = $_FILES["user_icon"];
    $user_icon_name = date("YmdHis") . $user_icon["name"];
}
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
$form = array();
$form["user_loginid"] = $request["user_loginid"];
$form["user_password"] = $request["user_password"];
$form["user_email"] = $request["user_email"];
$form["user_sph"] = $request["user_sph"];
$form["user_pd"] = $request["user_pd"];
/*-----------------------------------------------------------------------------
    ページ読み込み時に各値を取得
-----------------------------------------------------------------------------*/
try {
    $sql = "select * from users where user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
    $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    if ($row_user) {
        $form["user_loginid"] = $row_user["user_loginid"];
        $form["user_password"] = $row_user["user_password"];
        $form["user_icon"] = $row_user["user_icon"];
        $form["user_email"] = $row_user["user_email"];
        $form["user_sph"] = $row_user["user_sph"];
        $form["user_pd"] = $row_user["user_pd"];
        //古い画像削除用にファイル名をセッションに取得しておく
        $_SESSION["old_image"] = $row_user["user_icon"];
    } else {
        die("異常なアクセスです");
    }
} catch (PDOException $e) {
    die("エラー: " . $e->getMessage());
}
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    <<送信ボタンが押されて、エラーメッセージがない時
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
if (isset($request["send"])) {
    //空欄チェック
    if ($request["user_loginid"] == "") {
        $error_message .= "ユーザーIDを入力してください\n";
    }
    if ($request["user_password"] == "") {
        $error_message .= "パスワードを入力してください\n";
    }
    //ブラウザが判断するファイルタイプがjpegじゃなかったら、もしくは拡張子がjpegじゃなかったら
    if (!$user_icon["error"]) {
      if (($user_icon["type"] != "image/jpeg"  && $user_icon["type"] != "image/pjpeg") || strtolower(mb_strrchr($user_icon["name"], ".", false)) != ".jpg") {
          $error_message .= "画像(jpegファイル)をアップロードして下さい\n";
      }
    }
    //画像サイズを制限
    if ($image["size"] > 10*1024*1024) {
        $error_message .= "画像サイズは10MB以下にして下さい\n";
    }
    if ($request["user_email"] == "") {
        $error_message .= "メールアドレスを入力してください\n";
    }
    if ($request["user_sph"] == "") {
        $error_message .= "SPH（度数）を入力してください\n";
    }
    if ($request["user_pd"] == "") {
        $error_message .= "PD（瞳孔間距離）を入力してください\n";
    }
}
/*=============================================================================
    <<エラーメッセージがない時
=============================================================================*/
if (isset($request["send"]) && $error_message == "") {
/*-----------------------------------------------------------------------------
    画像の投稿処理
-----------------------------------------------------------------------------*/
  if ($user_icon["error"] == 0){
    //古い画像削除
    unlink("../images/users/{$_SESSION["old_image"]}");
    move_uploaded_file($user_icon["tmp_name"], "../images/users/{$user_icon_name}");
    //サムネ作成
    $original_image = imagecreatefromjpeg("../images/users/{$user_icon_name}");
    list($original_w, $original_h) = getimagesize("../images/users/{$user_icon_name}");
    //ファイルサイズがない時はエラー表示、それ以外はサムネ作成
    if ($original_w == 0 || $original_h == 0) {
        $error_message .= "画像ファイルではありません\n";
        unlink("../images/users/{$user_icon_name}");
    } else {
        //比率の計算 $original_w : $original_h = $thumb_w : $thumb_h
        $thumb_w = 120;
        $thumb_h = $original_h*$thumb_w/$original_w;
        $thumb_image = imagecreatetruecolor($thumb_w, $thumb_h);
        imagecopyresized($thumb_image, $original_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
        //できたサムネイルをオリジナルに上書き保存
        imagejpeg($thumb_image, "../images/users/{$user_icon_name}");
        imagedestroy($original_image);
        imagedestroy($thumb_image);
    }
  }
/*-----------------------------------------------------------------------------
    データベース更新
-----------------------------------------------------------------------------*/
  try {
      $pdo->beginTransaction();
      $sql = "update users set user_loginid = :user_loginid, user_password = :user_password, user_icon = :user_icon, user_email = :user_email, user_sph = :user_sph, user_pd = :user_pd where user_id = :user_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
      $stmt->bindValue(":user_loginid", $request["user_loginid"], PDO::PARAM_STR);
      $stmt->bindValue(":user_password", $request["user_password"], PDO::PARAM_STR);
      //プロフ画像の更新があれば新しいのに、なければ古い画像のまま
      if ($user_icon["error"] == 0) {
        $stmt->bindValue(":user_icon", $user_icon_name, PDO::PARAM_STR);
      } else {
        $stmt->bindValue(":user_icon", $_SESSION["old_image"], PDO::PARAM_STR);
      }
      $stmt->bindValue(":user_email", $request["user_email"], PDO::PARAM_STR);
      $stmt->bindValue(":user_sph", $request["user_sph"], PDO::PARAM_INT);
      $stmt->bindValue(":user_pd", $request["user_pd"], PDO::PARAM_INT);
      $stmt->execute();
      $stmt = null;
      $pdo->commit();
  } catch (PDOException $e) {
      $pdo->rollBack();
      die("エラー: " . $e->getMessage());
  }
/*-----------------------------------------------------------------------------
    アップデート後の各値を取得
-----------------------------------------------------------------------------*/
  try {
      $sql = "select * from users where user_id = :user_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
      $stmt->execute();
      $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = null;
      if ($row_user) {
        $form["user_loginid"] = $row_user["user_loginid"];
        $form["user_password"] = $row_user["user_password"];
        $form["user_icon"] = $row_user["user_icon"];
        $form["user_email"] = $row_user["user_email"];
        $form["user_sph"] = $row_user["user_sph"];
        $form["user_pd"] = $row_user["user_pd"];
        $_SESSION["old_image"] = $row_user["user_icon"];
      } else {
          die("異常なアクセスです");
      }
  } catch (PDOException $e) {
      die("エラー: " . $e->getMessage());
  }
$page_message = "修正しました";
}
/*=============================================================================
    エラーメッセージがない時>>
=============================================================================*/
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    送信ボタンが押されて、エラーメッセージがない時>>
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
/*
print '$user_icon';
print "<br>";
var_export($user_icon);
print "<br>";
print '$user_icon_name';
print "<br>";
var_export($user_icon_name);
print "<br>";
print '$form["user_icon"]';
print "<br>";
var_export($form["user_icon"]);
print "<br>";
*/
?>
<?php $page_title = "アカウント設定";?>
<?php require("header.php"); ?>
  <p>
    <a href="frame_list.php">一覧へ戻る</a>
  </p>
  <p>
    <?= he($page_message) ?>
  </p>
  <p class="attention">
    <?= nl2br(he($error_message)) ?>
  </p>
  <form enctype="multipart/form-data" action="account.php" method="post">
    <div>
      <label for="yu-za-mei">ユーザー名<span class="attention">【必須】</span></label>
      <input type="text" name="user_loginid" id="yu-za-mei" size="30" value="<?= he($form["user_loginid"]); ?>">
    </div>
    <div>
    <label for="pasuwa-do">パスワード<span class="attention">【必須】</span></label>
      <input type="password" name="user_password" id="pasuwa-do" size="30" value="<?= he($form["user_password"]); ?>">
    </div>
    <div>
      <label for="aikon">アイコン<span class="attention"></span></label>
      <?php if ($user_icon["error"] == 0 && $error_message == ""): ?>
      <p><img src='<?= "../images/users/" . he($user_icon_name) ?>'></p>
      <input type="file" name="user_icon" id="aikon">
      <?php else : ?>
      <p><img src="<?= '../images/users/' . he($form["user_icon"]) ?>"></p>
      <input type="file" name="user_icon" id="aikon">
      <?php endif; ?>
    </div>
    <div>
    <label for="meado">メールアドレス<span class="attention">【必須】</span></label>
      <input type="text" name="user_email" id="meado" size="30" value="<?= he($form["user_email"]); ?>">
    </div>
    <div>
    <label for="esupieichi">SPH（度数）<span class="attention">【必須】</span></label>
      <input type="number" name="user_sph" id="esupieichi" size="30" value="<?= he($form["user_sph"]); ?>">
    </div>
    <div>
    <label for="doukoukankyori">瞳孔間距離<span class="attention">【必須】</span></label>
      <input type="number" name="user_pd" id="doukoukankyori" size="30" value="<?= he($form["user_pd"]); ?>">
    </div>
    <div>
      <input type="submit" name="send" value="更新">
    </div>
  </form>
<?php require("footer.php"); ?>