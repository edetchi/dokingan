<?php require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
//エラー避け
$_SESSION["page_message"] = (!empty($_SESSION["page_message"])) ? $_SESSION["page_message"] : "";
$page_msgs[] = $_SESSION["page_message"];
?>
<?php $page_title = "マイページ";?>
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
      <ul class="mypage-menu">
        <li>
          <a href="favorites.php">お気に入り</a>
        </li>
        <li>
          <a href="frame_list.php">フレーム管理</a>
        </li>
        <li>
          <a href="account.php">アカウント設定</a>
        </li>
        <li>
          <a href="logout.php">ログアウト</a>
        </li>
      </ul>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php require("footer.php"); ?>