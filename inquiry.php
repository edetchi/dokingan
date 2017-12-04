<?php
//ライブラリの読み込み
require_once("system/common.php");
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
$whitelists = array("uname", "email", "body", "send");
$request = whitelist($whitelists);
/*-----------------------------------------------------------------------------
    メッセージの初期化
-----------------------------------------------------------------------------*/
$page_msgs = array();
$error_msgs = array();
/*-----------------------------------------------------------------------------
    フォーム項目のエラーチェック
-----------------------------------------------------------------------------*/
if (isset($request["send"])):
    //空欄チェック
    if ($request["uname"] == "") $error_msgs[] = "お名前を入力して下さい";
    if ($request["email"] == "") $error_msgs[] = "メールアドレスを入力して下さい";
    if ($request["body"] == "") $error_msgs[] = "質問内容を入力してください";
    //メアドの形式チェック、全項目記入されて初めてメアドの形式チェックエラーを表示する
    if (empty($error_msgs)):
      if (!preg_match('/^([a-zA-Z0-9\.\_\-\+\?\#\&\%])*@([a-zA-Z0-9\_\-])+([a-zA-Z0-9\.\_\-]+)+$/', $request["email"])) $error_msgs[] = "メールアドレスを正しく入力してください";
    endif;
endif;
/*=============================================================================
    送信モードの判別開始
=============================================================================*/
//直見でない送信ボタン押した後AND全項目記入済みの時
if (isset($request["send"]) && empty($error_msgs)) {
    //echo "送信モード<br>";
    //var_dump($request);
/*-----------------------------------------------------------------------------
    管理者向けメールの送信、mb_send_mail(送信先, 件名, 本文, ヘッダ);
-----------------------------------------------------------------------------*/
    //メール送信用に、言語と文字コードの指定
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    //本文用の変数を初期化して、条件に合致するたびに本文を追加していく
    $mail_body = "";
    if (isset($request["uname"])) {
        $mail_body .= "名前: ";
        $mail_body .= $request['uname'] . "\n";
    }
    if (isset($request["email"])) {
        $mail_body .= "メアド: ";
        $mail_body .= $request['email'] . "\n";
    }
    if (isset($request["body"])) {
        $mail_body .= "本文: ";
        $mail_body .= $request['body'] . "";
    }
    //管理者に送信実行
    $subject = "新規問い合わせ";
    $admin_email = "tatsutobacco@gmail.com";
    $add_header = "From:" . EMAIL_ADMIN_SENDER;
    $result = mb_send_mail($admin_email, $subject, $mail_body, $add_header);
    //送信者に確認メールをに送信
    $subject = "お問い合わせ有難うございます";
    $add_header = "From:" . EMAIL_CONTACT_SENDER;
    $mail_to = $request["email"];//メアドのバリデーションがないと脆弱性が発生する
    $result = mb_send_mail($mail_to, $subject, $mail_body, $add_header);
    //サンキューメッセージ作成
    $page_msgs[] = "お問い合わせ有難うございます！";
    //送信成功時に入力欄を空にする
    $request =array();
    //エラー避け
    $request['uname'] = "";
    $request['email'] = "";
    $request['body'] = "";
} else {
    //直見
    $page_msgs[] = "お問い合わせは以下よりお願いします";
}
/*=============================================================================
    送信モードの判別終了
=============================================================================*/
$page_title = "お問い合わせ";
require("header.php");
?>
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
    <form class="inquiry-form" action="inquiry.php" method="post">
        <div>
            <label for="namae">お名前<span class="attention">【必須】</span></label>
            <input type="text" name="uname" id="namae" size="30" value="<?= he($request['uname']) ?>">
        </div>
        <div>
            <label for="meado">メールアドレス<span class="attention">【必須】</span></label>
            <input type="email" name="email" id="meado" size="30" value="<?= he($request['email']) ?>">
        </div>
        <div>
            <label for="toiawase">お問い合わせ内容<span class="attention">【必須】</span></label>
            <textarea name="body" rows="5" id="toiawase" cols="20"><?= he($request['body']) ?></textarea>
        </div>
        <div>
            <input type="submit" name="send" value="送信する">
        </div>
    </form>
<?php require("footer.php"); ?>
