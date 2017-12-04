<?php require_once("../system/common.php"); ?>
<?php
$_SESSION["user_id"] = array();
if (isset($_COOKIE["PHPSESSID"])) {
  setcookie("PHPSESSID", "", time()-3600, "/");
}
session_destroy();
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
$page_msgs[] = "ログアウトしました";
$login_flag = false;
?>
<?php $page_title = "ログアウト";?>
<?php require("header.php"); ?>
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
<?php require("footer.php"); ?>