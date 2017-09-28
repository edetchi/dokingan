<?php require_once("../system/admin_common.php"); ?>
<?php $page_title = "ログイン";?>
<?php require("header.php"); ?>
    <form action="login.php" method="post">
      <div>
	<label for="roguin">ログインID<span class="attention">【必須】</span></label>
        <input type="text" name="user_loginid" id="roguin" size="30" value="">
      </div>
      <div>
	<label for="pasuwa-do">パスワード<span class="attention">【必須】</span></label>
        <input type="password" name="user_password" id="pasuwa-do" size="30" value="">
      </div>
      <div>
        <input type="submit" name="send" value="ログインする">
      </div>
    </form>
<?php require("footer.php"); ?>