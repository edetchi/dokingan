<?php
require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("user_id", "user_password", "user_email", "user_sph", "user_pd", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    プロフィール画像のアップロードがある時だけ変数に格納
-----------------------------------------------------------------------------*/
//プロフ画像のエラー避け
$_FILES["user_icon"] = (!empty($_FILES["user_icon"])) ? $_FILES["user_icon"] : "";
$user_icon = array();
$user_icon["error"] = (!empty($user_icon["error"])) ? $user_icon["error"] : "";
if ($_FILES["user_icon"]) {
    $user_icon = $_FILES["user_icon"];
    $user_icon_name = date("YmdHis") . $user_icon["name"];
}
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
/*=============================================================================
    ページ読み込み時に各値を取得
=============================================================================*/
try {
    $sql = "select * from users where user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
    $row_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
/*-----------------------------------------------------------------------------
    フォームの初期化
-----------------------------------------------------------------------------*/
    if ($row_user) {
      $form = array();
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
    /*
    if ($request["user_loginid"] == "") {
        $error_msgs[] = "ユーザーIDを入力してください\n";
    }
    */
    /*
    if ($request["user_password"] == "") {
        $error_msgs[] = "パスワードを入力してください\n";
    }
    */
    //ブラウザが判断するファイルタイプがjpegじゃなかったら、もしくは拡張子がjpegじゃなかったら
    //$_FILESがアップロードされた時
    if ($user_icon["error"] == 0) {
      if (($user_icon["type"] != "image/jpeg"  && $user_icon["type"] != "image/pjpeg") || strtolower(mb_strrchr($user_icon["name"], ".", false)) != ".jpg") {
          $error_msgs[] = "画像(jpegファイル)をアップロードして下さい\n";
      }
    }
    //画像サイズを制限
    if ($user_icon["size"] > 10*1024*1024) {
        $error_msgs[] = "画像サイズは10MB以下にして下さい\n";
    }
    if ($request["user_email"] == "") {
        $error_msgs[] = "メールアドレスを入力してください\n";
    }
    if ($request["user_sph"] == "") {
        $error_msgs[] = "SPH（度数）を入力してください\n";
    }
    if ($request["user_pd"] == "") {
        $error_msgs[] = "PD（瞳孔間距離）を入力してください\n";
    }
}
/*=============================================================================
    <<エラーメッセージがない時
=============================================================================*/
if (isset($request["send"]) && empty($error_msgs)) {
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
        $error_msgs[] = "画像ファイルではありません\n";
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
      $sql = "update users set user_password = :user_password, user_icon = :user_icon, user_email = :user_email, user_sph = :user_sph, user_pd = :user_pd where user_id = :user_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
      //パスワードの更新がある時とない時
      if ($request["user_password"] == "" ) {
        $stmt->bindValue(":user_password", $form["user_password"], PDO::PARAM_STR);
      } else {
        $stmt->bindValue(":user_password", $request["user_password"], PDO::PARAM_STR);
      }
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
        //$form["user_password"] = $row_user["user_password"];
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
$page_msgs[] = "更新しました";
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
<div class="main-wrap">
  <main>
    <a href="frame_list.php">一覧へ戻る</a>
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
    <form enctype="multipart/form-data" action="account.php" method="post">
      <div>
      <label for="pasuwa-do">パスワード</label>
        <input type="password" name="user_password" id="pasuwa-do" size="30" >
      </div>
      <div>
        <label for="aikon">アイコン<span class="attention"></span></label>
        <?php if ($user_icon["error"] === 0 && empty($error_msgs)): ?>
        <p><img src='<?= "../images/users/" . he($user_icon_name) ?>'></p>
        <?php else : ?>
        <p><img src="<?= '../images/users/' . he($form["user_icon"]) ?>"></p>
        <?php endif; ?>
        <input type="file" name="user_icon" id="aikon">
      </div>
      <div>
      <label for="meado">メールアドレス<span class="attention">*</span></label>
        <input type="text" name="user_email" id="meado" size="30" value="<?= he($form["user_email"]); ?>">
      </div>
      <div>
      <label for="esupieichi">SPH（度数）<span class="attention">*</span></label>
        <input type="number" name="user_sph" id="esupieichi" size="30" value="<?= he($form["user_sph"]); ?>">
      </div>
      <div>
      <label for="doukoukankyori">瞳孔間距離<span class="attention">*</span></label>
        <input type="number" name="user_pd" id="doukoukankyori" size="30" value="<?= he($form["user_pd"]); ?>">
      </div>
      <div>
        <input type="submit" name="send" value="更新">
      </div>
    </form>
  </main>
</div>
<?php require("footer.php"); ?>