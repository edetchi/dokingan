<?php require_once("system/common.php"); ?>
<?php $page_title = "フレーム編集";?>
<?php require("header.php"); ?>
    <form class="frame-edit" action="register.php" method="post">
      <div>
        <label for="yu-za-mei">ユーザー名<span class="attention">【必須】</span></label>
        <div class="user_loginid_result"></div>
        <input type="text" name="user_loginid" id="yu-za-mei" size="30" value="">
      </div>
      <label for="me-ruadoresu">メールアドレス<span class="attention">【必須】</span></label>
      <div class="user_email_result"></div>
      <input type="text" name="user_email" id="me-ruadoresu" size="30" value="">
    </div>
      <div>
        <input class="register-btn" type="submit" name="send" value="送信する">
      </div>
    </form>
  </main>
</div>
<?php require("footer.php"); ?>