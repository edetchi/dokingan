<?php
/*=========================================
    送信モードの判別開始
=========================================*/
if(isset($_REQUEST["send"])){
    echo "送信モード<br>";
    var_dump($_REQUEST);
/*----------------------------------------------------------------------------------
    管理者向けメールの送信、mb_send_mail(送信先, 件名, 本文, ヘッダ);
----------------------------------------------------------------------------------*/
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
    //送信実行
    $subject = "新規問い合わせ";
    $admin_email = "suteado@edetchi.com";
    $add_header = "From:" . $admin_email;
    $result = mb_send_mail($admin_email, $subject, $mail_body, $add_header);
} else {
    echo "直見";
}
/*=========================================
    送信モードの判別終了
=========================================*/
?>
<?php $page_title = "お問い合わせ"; ?>
<?php require "header.php"; ?>
    <p>
      お問い合わせは以下よりお願いします
    </p>
    <form action="inquiry.php" method="post">
        <div>
            <label for="namae">お名前: </label>
            <input type="text" name="uname" id="namae" size="30">
        </div>
        <div>
            <label for="meado">メールアドレス: </label>
            <input type="email" name="email" id="meado" size="30">
        </div>
        <div>
            <label for="toiawase">お問い合わせ内容: </label>
            <textarea name="body" rows="5" id="toiawase" cols="20"></textarea>
        </div>
        <div>
            <input type="submit" name="send" value="送信する">
        </div>
    </form>
<?php require "footer.php"; ?>
