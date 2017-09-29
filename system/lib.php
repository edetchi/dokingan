<?php
/*-----------------------------------------------------------------------------
    サニタイズを多用するのでhtmlentities()を簡略化
-----------------------------------------------------------------------------*/
function he($str) {
 	return htmlentities($str, ENT_QUOTES, "utf-8");
}
/*-----------------------------------------------------------------------------
    変数をホワイトリスト化
-----------------------------------------------------------------------------*/
//$_REQUEST[]の取りうるキーを限定する
function whitelist($whitelists) {
  $request = array();
  //入力欄が空欄なら連想配列$requestにnull、入力値があるならその値を格納
  foreach($whitelists as $whitelist){
      $request[$whitelist] = null;
      if(isset($_REQUEST[$whitelist])){
        //keyからヌルバイト除去
        $whitelist = str_replace("\0", "", $whitelist);
        $request[$whitelist] = $_REQUEST[$whitelist];
      }
  }
  return $request;
}
?>