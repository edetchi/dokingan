<?php require_once("../system/common.php"); ?>
<?php
$_SESSION["user_id"] = array();
if (isset($_COOKIE["PHPSESSID"])) {
  setcookie("PHPSESSID", "", time()-3600, "/");
}
session_destroy();
$page_message = "ログアウトしました"
?>
<?php $page_title = "ログアウト";?>
<?php require("header.php"); ?>
    <p>
      <?= he($page_message) ?>
    </p>
<?php require("footer.php"); ?>