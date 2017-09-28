# HTML
 - form
  - inputの識別子（id）をlabelのforで指定すると、label要素をクリックした時に、inputがフォーカスされるので指定しておくと便利かも
   ```html
   <label for="lastname">名前: </label>
   <input type="text" name="mei" id="lastname">
   ```
   - textareaの属性: rows="縦文字数（行数）" cols="横文字数"
# CSS
 - reset.cssは、自分の適用させたいスタイルより前に読み込むこと
 - CSSプロパティの並び順は、**視覚整形→ボックス→背景→フォント→コンテンツ**がベター

# PHP
 - include/require構文: require構文はファイルが存在しなかったらエラー出力で停止、include構文はそのまま続行
 - include/require構文を呼び出すファイルで、変数を、呼び出すファイルに反映させたいときは、呼び出す前に変数の定義をしておく
 - <?= "出力したい文字列" ?>: これはどのphpバージョンでもサポートしている、最後のセミコロンも省略できる（<?php echo 文字列; ?>と同義）
 - $_REQUEST: $_POST, $_GET, $_COOKIEの合算ver
 - elseifはelse ifでもおｋ
 - フォームが直見か送信後の画面かの判別は、$_REQUEST["submitのname"]があるかないかで判別する
 - フォーム内容をメールで送信: `mb_send_mail(送信先, 件名, 本文, ヘッダー)`
  - 送信にあたっての準備: `mb_language("Japanese")`, `mb_internal_encoding("utf-8")`
 - 可読性があがりそうなコメントアウト方法（CSS等で親子関係がわかる、大きな処理とその中の小さな処理で分ける）
 ```php
 /*=========================================
    大きな処理開始
 =========================================*/

 /*----------------------------------------------------------------------------------
    小さい処理
 ----------------------------------------------------------------------------------*/
 //細かい補足内容1
 //細かい補足内容2
 //細かい補足内容3

 /*=========================================
    大きな処理終了
 =========================================*/
 ```
 - `if(条件) 処理;` : 一行if文
 - `preg_match("検索文字列", "対象文字列")` : 検索文字列があるときは1を返す、ないときは0を返す
 - サニタイズ: タグ等に使う特殊文字をエスケープ処理する
 - `htmlspecialchars("文字列", ENT_QUOTES, "utf-8")`
 - `htmlentities("文字列", ENT_QUOTES, "utf-8")`
 - `str_replace("検索文字列", "置換文字列", "対象文字列")`: 文字列の置換
 - 変数のホワイトリスト化
 ```php
 $whitelists = array(white1, white2, white3, ...);
 $white = array();
 foreach($whitelists as $whitelist){
   $white[$whitelist] = $whitelist;
 }
 ```

#SQL
 - `where 1`, `where 1=1`の使用で条件数による場合分けを簡潔にできる。条件が1つの時は`where`, ２つ以上の時は`and`, `or`になるので面倒くさい
 - `truncate table テーブル名`: テーブルを空にする

# Git
 - `git commit --amend`: 直近のコミットのコメント修正

# Linux
 - 改行: LinuxではLF（Line Feed）、MacではCR（Carriage Return）
