<html>
<head><title>SQLITE3 Sample PHP</title></head>
<body>

<?php
//
//SQLITE３のDBファイルにあるすべてのテーブルのデータを確認できるプログラム
//
$db=db_open();
//table_create($db);    //サンプルテーブルを入れたい時に1回だけコメント外して実行してください
//
if(!isset($_POST["tbl"])||$_POST["tbl"]==""){
    show_form($db);
    print "<center>テーブルを選択してください<br></center>";
    exit();
}

show_form($db);
$tbl=$_POST["tbl"];

//テーブル名取得はリストボックスにセットするためにshow_form()内に組み込み
//show_tables($db);

$cnt=show_column($db,$tbl);//表の項目表示
show_data($db,$tbl,$cnt);
db_close($db);
////////////////////////////////////////////////////////////////////
function show_form($db){
print<<<HTML
        <center>
        <h3>データ検索</h3>
        <h4>DBファイルにあるすべてのテーブルのデータを確認できます。</h4>
        <form action='sqlite3_table_check.php' method='POST'>
        <table>
        <tr><th>テーブル</th><td>
        <select name="tbl">   <option value=""></option>

HTML;
//  if ($tbl == $row["name"]) {
//    print '<option value="' . $p . '" selected="selected">' . $p . '</option>';
//  } else [
        $sql="select name from sqlite_master where type='table'";
        $result=$db->query($sql);

        while ($row = $result->fetchArray()) {
    print '<option value="' . $row["name"] . '">' . $row["name"] . '</option>';
        }
 print<<<HTML
        <tr><td><input type='submit' value='go' name='submit'></td>
        <td><input type='reset' value='cancel' ></td></tr>
        </table>
        </form>
        </center>
        </body>
        </html>
HTML;
}




function db_open(){
        //データベースの作成とオープン
         $db = new SQLite3('./dokingan.sqlite3');
        if (!$db) {
            die('接続失敗です。'.$sqliteerror);
        }
        print "接続に成功しました。<br>";
        return $db;
}

function show_tables($db){
        //DB内ののテーブル名を取得する方法
        $sql="select name from sqlite_master where type='table'";
        $result=$db->query($sql);

        while ($row = $result->fetchArray()) {
            print "テーブル名＝". $row['0']."<br>";
        }
}
//テーブルのカラム名を取得する方法
function show_column($db,$tbl){
print<<<HTML
        <center>
        <h3>《 $tbl 》テーブルデータ</h3>
        <table border="1">
        <tr>
HTML;
        //テーブルのカラム名を取得する方法
        $cnt=0;
        $result = $db->query("PRAGMA table_info(".$tbl.")");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            print "<th>". $row['name']."</th>";
            $cnt++;
        }
        print "<tr>";
        return $cnt;
}
//データを取得する
function show_data($db,$tbl,$cnt){
        $sql = "SELECT * FROM $tbl";
        $result=$db->query($sql);
        if(!$result) {
            die('クエリーが失敗しました。');
        }
        //結果を1行ずつ処理する
        while ($row = $result->fetchArray()) {
            print "<tr>";
            for($i=0;$i<$cnt;$i++){
                $d=$row[$i];
                print "<td>".$d."</td>";
            }
                 print "</tr>";

        }
                 print "</table>";

}
function db_close($db){
        //DB終了(close)処理
        $db->close();
       // print"切断しました。<br>";
}
function table_create($db){
        $sql = "create table sample1 (id int primary key, name varchar(10),age int,birthday date) ";
        $result= $db->exec($sql);
        if (!$result) {
            die('クエリーが失敗しました。');
        }
        print "データの追加(INSERT)";
        $sql = "INSERT INTO sample1 VALUES (1, '鈴木',25,'2000-11-11'),(2, '佐々木',25,'2001-12-12'),(3,'金正恩',50,'1984-1-8')";
        //$result = $db->exec($sql);
        $result=$db->query($sql);
        if (!$result) {
            die('insertが失敗しました。');
        }
}

?>
</body>
</html>