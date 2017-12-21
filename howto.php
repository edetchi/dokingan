<?php
require_once("system/common.php");
$page_title = "このサイトの使い方";
require("header.php");
?>
  <div class="main-wrap">
    <main>
      <div class="fixed-pages">
        <h2>どんなサイト？</h2>
        <p>新規メガネ作成時、出来上がったメガネを見て「ぶ、ぶ厚い…」、「なんか違う」と思ったことはありますか？</p>
        <p>このサイトは、メガネの処方箋に書いてあるデータ（度数、瞳孔間距離）を入力するだけで各フレームごとのレンズの厚みを計算してくれるサイトです。</p>
        <p>事前にフレーム中心部、フレーム端のレンズの厚みを把握しておくことで、メガネ新調時のこんなはずじゃなかったをなくしましょう。</p>
      </div>
      <div class="fixed-pages">
        <h2>フレーム一覧ページの見方</h2>
        <figure>
          <img src="images/pages/frame-list01.png">
          <figcaption>非ログイン時</figcaption>
        </figure>
        <figure>
          <img src="images/pages/frame-list02.png">
          <figcaption>ログイン時</figcaption>
        </figure>
        <ul>
          <li>①価格</li>
          <li>②お気に入り数</li>
          <li>③フレームのデータ(レンズ幅□ブリッジ幅-テンプル長、単位mm)</li>
          <li>④投稿ユーザー</li>
          <li>⑤レンズ中央の厚み、端の厚み、単位mm。レンズ両端の厚い方が赤字で表示されます。アカウント作成後、SPY(度数)、PD(瞳孔間距離)の入力が必須です。</li>
        </ul>
      </div>
      <div class="fixed-pages">
        <h2>フレームの並び替え</h2>
        <figure>
          <img src="images/pages/frame-list03-1.png">
          <figcaption>並び替え前</figcaption>
        </figure>
        <figure>
          <img src="images/pages/frame-list05-1.png">
          <figcaption>並び替え後</figcaption>
        </figure>
        <p>右上のナビバーからフレームの並び替えが可能です。アカウント作成後のSPY(度数)、PD(瞳孔間距離)の入力で、レンズの厚みに応じた並び替えも可能です。</p>
      </div>
      <div class="fixed-pages">
        <h2>フレーム個別ページの見方</h2>
        <figure>
          <img src="images/pages/frame-list06.png">
          <figcaption>個別ページ</figcaption>
        </figure>
        <ul>
          <li>①各種フレームデータ(画像、フレームデータ、投稿者等)</li>
          <li>②各種ボタン(購入先リンク、スパム報告、お気に入りボタン等)。お気に入り機能は、ログインが必要です。</li>
          <li>③コメント投稿機能。コメントするには、ログインが必要です。また、自分の投稿したコメントは右上のクロスで削除可能です。</li>
        </ul>
      </div>
      <div class="fixed-pages">
        <h2>フレーム管理</h2>
        <figure>
          <img src="images/pages/frame-list.png">
          <figcaption>フレーム一覧画面</figcaption>
        </figure>
        <ul>
          <li>①フレームの新規登録</li>
          <li>②フレームの編集</li>
          <li>③フレームの削除</li>
        </ul>
        <p>フレームに関わる操作が行えます。あなたの投稿してくれたフレームデータが他ユーザーにとって最適なメガネとなるかもしれません。是非とも投稿してみてください。</p>
        <figure>
          <img src="images/pages/frame-edit.png">
          <figcaption>フレームの新規登録、編集画面</figcaption>
        </figure>
        <p>フレームに関するデータを入力してください。投稿されたフレームは、一覧ページにて他のユーザーも閲覧できるようになります。</p>
      </div>
      <div class="fixed-pages">
        <h2>アカウント設定</h2>
        <figure>
          <img src="images/pages/account.png">
          <figcaption>アカウント設定画面</figcaption>
        </figure>
        <p>アカウントの各種設定画面です。SPH(度数)、PD(瞳孔間距離)は処方箋作成時の数値を入力してください。この2つの値は、フレームごとのレンズの厚みを計算するのに必要です。</p>
      </div>
      <div class="fixed-pages">
        <h2>最後に</h2>
        <p>新機能の要望、トラブル等ございましたら下記の問い合わせフォームからご連絡ください。皆様、良いメガネライフを！</p>
      </div>
    </main>
    <aside>
    </aside>
  </div><!--.main-wrap-->
<?php
/*=============================================================================
    body部>>
=============================================================================*/
require("footer.php"); ?>