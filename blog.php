<?php $page_title = "ブログ"; ?>
<?php require "header.php"; ?>
    <p>
      準備中
    </p>
<?php require "footer.php"; ?>
<?php
/*=============================================================================
    関数コーナー
=============================================================================*/
//サニタイズを多用するのでhtmlentities()を簡略化
function he($str){
  return htmlentities($str, ENT_QUOTES, "utf-8");
}
?>