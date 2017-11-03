<?php require_once("../system/common.php");
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_message = "";
$error_message = "";

$page_message .= $_SESSION["page_message"];
$_SESSION["page_message"] = "";
?>
<?php $page_title = "マイページ";?>
<?php require("header.php"); ?>
  <div class="main-wrap">
    <main>
      <p>
        <?= he($page_message) ?>
      </p>
      <p class="attention">
        <?= nl2br(he($error_message)) ?>
      </p>
      <ul>
        <li>
          <a href="favorites.php">お気に入り</a>
        </li>
        <li>
          <a href="frame_list.php">フレーム管理</a>
        </li>
        <li>
          <a href="account.php">アカウント設定</a>
        </li>
      </ul>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php require("footer.php"); ?>