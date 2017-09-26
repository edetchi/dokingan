<?php
if(isset($_REQUEST["send"])){
    echo "送信モード<br>";
    var_dump($_REQUEST);
} else {
    echo "直見";
}
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
