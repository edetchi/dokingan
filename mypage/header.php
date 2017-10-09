<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title><?= $page_title; ?> | マイページ</title>
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/script.js"></script>
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/style.css">
  </head>
  <body>
    <h1><?= "$page_title"; ?></h1>
    <?php if (!$is_mypage_top && $login_flag): ?>
        <div>
          <a href=".">マイページ</a>
        </div>
      <?php endif; ?>
    <?php if ($login_flag): ?>
        <div>
          <a href="logout.php">ログアウト</a>
        </div>
    <?php endif; ?>