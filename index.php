<?php
//ライブラリの読み込み
require_once("system/common.php");
$page_title = "トップページ";
require("header.php");
?>
    <p>
      閲覧ありがとうございます。<br>
      こちらはフレームリフュジーページです。
    </p>
    <h2>メニュー</h2>
    <ul>
      <li>
        <a href="blog.php">ブログ</a>
      </li>
      <li>
        <a href="inquiry.php">お問い合わせ</a>
      </li>
    </ul>
<?php require("footer.php"); ?>
