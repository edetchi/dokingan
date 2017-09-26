<?php $page_title = "お問い合わせ"; ?>
<?php require "header.php"; ?>
<?php
/*----------------------------------------------------------------------------
    フォーム項目のエラーチェック
----------------------------------------------------------------------------*/
//エラーメッセージの初期化
$error_message = "";
if(isset($_REQUEST["send"])):
    //空欄チェック
    if($_REQUEST["uname"] == "") $error_message .= "お名前を入力して下さい\n";
    if($_REQUEST["email"] == "") $error_message .= "メールアドレスを入力して下さい\n";
    if($_REQUEST["body"] == "") $error_message .= "質問内容を入力してください\n";
    //メアドの形式チェック、全項目記入されて初めてメアドの形式チェックエラーを表示する
    if($error_message == ""):
      if (!preg_match('/^([a-zA-Z0-9\.\_\-\+\?\#\&\%])*@([a-zA-Z0-9\_\-])+([a-zA-Z0-9\.\_\-]+)+$/', $_REQUEST["email"])) $error_message .= "メールアドレスを正しく入力してください\n";
    endif;
endif;
/*=============================================================================
    送信モードの判別開始
=============================================================================*/
//直見でない送信ボタン押した後AND全項目記入済みの時
if(isset($_REQUEST["send"]) && $error_message == ""){
    echo "送信モード<br>";
    var_dump($_REQUEST);
/*----------------------------------------------------------------------------
    管理者向けメールの送信、mb_send_mail(送信先, 件名, 本文, ヘッダ);
----------------------------------------------------------------------------*/
    //メール送信用に、言語と文字コードの指定
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    //本文用の変数を初期化して、条件に合致するたびに本文を追加していく
    $mail_body = "";
    if(isset($_REQUEST["uname"])){
        $mail_body .= "名前: ";
        $mail_body .= $_REQUEST['uname'] . "\n";
    }
    if(isset($_REQUEST["email"])){
        $mail_body .= "メアド: ";
        $mail_body .= $_REQUEST['email'] . "\n";
    }
    if(isset($_REQUEST["body"])){
        $mail_body .= "本文: ";
        $mail_body .= $_REQUEST['body'] . "\n";
    }
    //管理者に送信実行
    $subject = "新規問い合わせ";
    $admin_email = "suteado@edetchi.com";
    $add_header = "From:" . $admin_email;
    $result = mb_send_mail($admin_email, $subject, $mail_body, $add_header);
    //送信者に確認メールをに送信
    $subject = "お問い合わせ有難うございます";
    $admin_email = "suteado@edetchi.com";
    $add_header = "From:" . $admin_email;
    $mail_to = $_REQUEST["email"];//メアドのバリデーションがないと脆弱性が発生する
    $result = mb_send_mail($mail_to, $subject, $mail_body, $add_header);
    //サンキューメッセージ作成
    $thnakyou = "お問い合わせ有難うございます！";
} else {
    echo "直見";
}
/*=============================================================================
    送信モードの判別終了
=============================================================================*/
?>
    <!-- サンキューメッセージ表示 -->
    <p>
      <?= $thnakyou ?>
    </p>
    <!--エラーメッセージの表示-->
    <p class="attention">
      <!--$error_messageはユーザーから受け取る値は入っていないが、変数を表示するときはサニタイズするのがベター-->
      <?= htmlentities($error_message, ENT_QUOTES) ?>
    </p>
    <p>
      お問い合わせは以下よりお願いします
    </p>
    <form action="inquiry.php" method="post">
        <div>
            <label for="namae">お名前<span class="attention">【必須】</span></label>
            <input type="text" name="uname" id="namae" size="30">
        </div>
        <div>
            <label for="meado">メールアドレス<span class="attention">【必須】</span></label>
            <input type="email" name="email" id="meado" size="30">
        </div>
        <div>
            <label for="toiawase">お問い合わせ内容<span class="attention">【必須】</span></label>
            <textarea name="body" rows="5" id="toiawase" cols="20"></textarea>
        </div>
        <div>
            <input type="submit" name="send" value="送信する">
        </div>
    </form>
<?php require "footer.php"; ?>
