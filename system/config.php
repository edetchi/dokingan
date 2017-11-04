<?php
/*-----------------------------------------------------------------------------
    定数一覧
-----------------------------------------------------------------------------*/
define("SITE_NAME", "ドキンガン");
//サイトから管理者へ送信する時に使うメールアドレス
define("EMAIL_ADMIN_SENDER", "admin@dokingan.com");
//サイトからユーザーへ送信する時に使うメールアドレス
define("EMAIL_CONTACT_SENDER", "contacts@dokingan.com");
//サイトからユーザーへノーリプライメールを送信する時に使うメールアドレス
define("EMAIL_NOREPLY_SENDER", "noreply@dokingan.com");
/*-----------------------------------------------------------------------------
    データベースの環境設定
-----------------------------------------------------------------------------*/
$db_type = "mysql";
$db_host = "localhost";
$db_name = "framerefugee";
$db_user = "root";
$db_pass = "root";
$dsn = "{$db_type}:host={$db_host};dbname={$db_name};charset=utf8";
?>
