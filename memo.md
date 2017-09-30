# HTML
 - form
  - inputの識別子（id）をlabelのforで指定すると、label要素をクリックした時に、inputがフォーカスされるので指定しておくと便利かも
   ```html
   <label for="lastname">名前: </label>
   <input type="text" name="mei" id="lastname">
   ```
   - textareaの属性: rows="縦文字数（行数）" cols="横文字数"
 - `time`タグ
 - `<textarea>`タグはコーディング時に行をまたぐとデフォルトの値としてインデントが出力されるので注意
 ```HTML
 <textarea>
      <?= $_REQUEST["body"] ?><!--この行のインデント部分がデフォルト値として出力されてしまうので注意-->
 </textarea>
 ```
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
 - データベースへの接続: `$pdo = new PDO(データソースネーム, データベースユーザー, ユーザーパス);`
 - データソースネーム(`$dsn`): `$dsn = "mysql:host=localhost;dbname=データベース名;charset=utf8";`
 - データベース接続エラー時は、try catch文のcatch分で`try{} catch(PDOException $e){die($e->getMessage());}`
 - デバッグ用関数 `print_f()`, `var_dump()`, `var_export()`の違い
 ```php
<?php
$data = array(
    "A" => "Apple",
    "B" => "Banana",
    "C" => "Cherry"
);
print_r($data);
/*出力結果
Array
(
    [A] => Apple
    [B] => Banana
    [C] => Cherry
)
*/
var_dump($data);
/*出力結果
array(3) {
  ["A"]=>
  string(5) "Apple"
  ["B"]=>
  string(6) "Banana"
  ["C"]=>
  string(6) "Cherry"
}
*/
var_export($data);
/*出力結果
array (
  'A' => 'Apple',
  'B' => 'Banana',
  'C' => 'Cherry',
)
*/
 ```
 - ステートメントハンドラ`fetchAll()`と`fetch()`の使い分け: 一般的に取得データが多い時`fetchAll()`だと処理が停止することがあるので、1件ずつ処理する`fetch()`を使用するほうがベター。両者ともオプション`PDO::FETCH_ASSOC`の指定がなければ連番配列が入ってくるので注意
 - `fetch`で1件ずつ取得する場合は、事前に`$row = $stmh->fetch("PDO::FETCH_ASSOC")を定義して、while($row)とすると無限ループに陥るので、whileの条件内で$rowを定義すること、つまり`while($row = $stmh->fetch("PDO::FETCH_ASSOC"))`
 - `fetchAll()`と`fetch()`で得られる取得結果の違い
```php
 //fetchAllの結果
 array ( 0 => array ( 'post_id' => 1, 0 => 1, 'post_title' => '記事1', 1 => '記事1', 'post_content' => '内容です1', 2 => '内容です1', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', ), 1 => array ( 'post_id' => 2, 0 => 2, 'post_title' => '記事2', 1 => '記事2', 'post_content' => '内容です2', 2 => '内容です2', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', ), 2 => array ( 'post_id' => 3, 0 => 3, 'post_title' => '記事3', 1 => '記事3', 'post_content' => '内容です3', 2 => '内容です3', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', ), 3 => array ( 'post_id' => 4, 0 => 4, 'post_title' => '記事4', 1 => '記事4', 'post_content' => '内容です4', 2 => '内容です4', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', ), 4 => array ( 'post_id' => 5, 0 => 5, 'post_title' => '記事5', 1 => '記事5', 'post_content' => '内容です5', 2 => '内容です5', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', ), )
 //fetchの結果
 array ( 'post_id' => 1, 0 => 1, 'post_title' => '記事1', 1 => '記事1', 'post_content' => '内容です1', 2 => '内容です1', 'post_updated' => '2017-09-28 10:11:40', 3 => '2017-09-28 10:11:40', 'post_created' => '2017-09-28 00:00:00', 4 => '2017-09-28 00:00:00', )
```
 - `fetchAll()`, `fetch()`の取得結果から、連番配列を除去するオプション: `fetchAll(PDO::FETCH_ASSOC)`, `fetch(PDO::FETCH_ASSOC)`
 - ライブラリ用のファイルを`~/system`以下に`.htaccess`ファイルと共に作成
  - `config.php`: 環境設定をまとめるファイル
  - `lib.php`: 共通で利用する関数を定義
  - `common.php`: 環境設定とライブラリを読み込み、共通処理をまとめる。これだけ読み込めばおｋなファイル
 - 文字列のチェック
  - `strpos("検索文字列", "検索対象文字列")`: 正規表現なしの時に使用、該当文字列がない時にfalseをかえす
  - `preg_match("/検索文字列/", 検索対象文字列)`:

#SQL
 - `where 1`, `where 1=1`の使用で条件数による場合分けを簡潔にできる。条件が1つの時は`where`, ２つ以上の時は`and`, `or`になるので面倒くさい
 - `truncate table テーブル名`: テーブルを空にする

# Git
 - `git commit --amend`: 直近のコミットのコメント修正

# Linux
 - 改行: LinuxではLF（Line Feed）、MacではCR（Carriage Return）
 - `grep -r パターン 場所`: ファイル内容にパターンの文字列を含むものを返す
