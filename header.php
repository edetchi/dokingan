<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title; ?> | フレームリフュジー</title>
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/script.js"></script>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <h1><?= "$page_title"; ?></h1>
<?php if ($login_flag): ?>
    <div>
      <a href="mypage">マイページ</a>
    </div>
    <div>
      <a href="mypage/logout.php">ログアウト</a>
    </div>
<?php else: ?>
    <div>
      <a href="mypage/login.php">ログイン</a>
    </div>
<?php endif; ?>