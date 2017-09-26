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
 - <?= 出力したい文字列 ?>: これはどのphpバージョンでもサポートしている（<?php echo 文字列; ?>と同義）
 - $_REQUEST: $_POST, $_GET, $_COOKIEの合算ver

# Linux
 - 改行: LinuxではLF（Line Feed）、MacではCR（Carriage Return）
