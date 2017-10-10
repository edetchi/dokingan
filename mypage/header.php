<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page_title; ?> | ドキンガン</title>
  <script src="../js/jquery-3.2.1.min.js"></script>
  <script src="../js/script.js"></script>
  <link rel="stylesheet" href="../css/reset.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
  <header>
    <ul class="nav-bar">
      <li class="nav-bar__logo">
        <a href="../"><img src="#" alt="<?= $site_name ?>"></a>
      </li>
      <?php if (!$login_flag): ?>
      <li class="nav-bar__login">ログイン</li>
      <li class="nav-bar__register">新規登録</li>
      <?php else: ?>
      <li class="nav-bar__mymenu">マイメニュー</li>
      <?php endif; ?>
      <li class="nav-bar__filter">
        <span class="icon icon-filter">
      </li>
      <li class="nav-bar__back"></span>
        <span class="icon icon-undo"></span>
      </li>
    </ul><!--.nav-bar-->
    <div class="login-pop">
      <h2>ログイン</h2>
      <form class="login-pop__login-form" action="login.php" method="post">
        <div class="login-pop__email">
          <label for="roguin"><span class="attention"></span></label>
          <input type="text" name="user_loginid" id="roguin" size="30" value="ユーザー名→メールアドレスに変更したい">
        </div>
        <div class=login-pop__password>
          <label for="pasuwa-do"><span class="attention"></span></label>
          <input type="password" name="user_password" id="pasuwa-do" size="30" value="パスワード">
        </div>
        <div class="login-pop__btn">
          <input type="submit" name="send" value="ログイン">
        </div>
        <p class="login-pop__notice-password">パスワードを忘れた方は <a href="#  ">こちら</a></p>
        <p class="login-pop__notice-register">アカウントをお持ちでない方 <a href="#">新規登録</a></p>
      </form>
    </div><!--.login-pop-->
    <div class="register-pop">

    </div><!--.register-pop-->
    <div class="mymenu-pop">
      <ul>
        <li>
          <a href="./favorites.php">
            <i class="fa fa-star-o" aria-hidden="true"></i>お気に入り
          </a>
        </li>
        <li>
          <a href="./frame_list.php">
            <span class="icon icon-glasses"></span>フレーム管理
          </a>
        </li>
        <li>
          <a href="./account.php">
            <span class="icon icon-setting1"></span>アカウント設定
          </a>
        </li>
        <li>
          <a href="./logout.php">
            <i class="fa fa-sign-out" aria-hidden="true"></i>ログアウト
          </a>
        </li>
      </ul>
    </div><!--.mymenu-pop-->
  </header>