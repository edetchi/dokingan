<?php
/*-----------------------------------------------------------------------------
    変数一覧
-----------------------------------------------------------------------------*/
// アプリディレクトリ（各ページでrequireする前に）
$app_dir = "/dokingan.com";
// アプリルート
//$app_root = $_SERVER["DOCUMENT_ROOT"] . $app_dir;
/*-----------------------------------------------------------------------------
    定数一覧
-----------------------------------------------------------------------------*/
//サイト名
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
/*
$db_name = "framerefugee";
$db_user = "root";
$db_pass = "root";
*/
$db_name = "dokingan.com";
$db_user = "dokingan";
$db_pass = "gFQAomViTotlfxuL7P0r";

$dsn = "{$db_type}:host={$db_host};dbname={$db_name};charset=utf8";
?>
