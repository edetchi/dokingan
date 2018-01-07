$(function(){
/*-----------------------------------------------------------------------------
    ajaxでお気に入り登録
-----------------------------------------------------------------------------*/
  var fbtn = $("button[data-favorite]");
  //お気に入り登録されているかどうかでボタンの見た目を変更
  if (fbtn.data("favorite")==0)  {
    $("button[data-favorite] i").css("color", "yellow");
  } else {
    $("button[data-favorite] i").css("color", "white");
  }
  //data-favorite=0はお気に入り登録されている状態
  fbtn.click(function() {
    if (fbtn.data("favorite")==0) {
      $.ajax({
        url: "favorite.php",
        type: "post",
        data: {favorite: 1}
      })
      .done(function(data) {
        fbtn.data("favorite", 1);
        $("button[data-favorite] i").css("color", "white");
        $("#result").html(data);
        //お気に入りの数を格納し、数を変更
        var fcnt = Number($(".frame-detail__action__favorite-cnt").text());
        $(".frame-detail__action__favorite-cnt").text(fcnt-1);
      })
      .fail(function() {
        console.log("error");
      });
    }
    //data-favorite=1はお気に入り解除されている状態
    if (fbtn.data("favorite")==1) {
      $.ajax({
        url: "favorite.php",
        type: "post",
        data: {favorite: 0}
      })
      .done(function(data) {
        fbtn.data("favorite", 0);
        $("button[data-favorite] i").css("color", "yellow");
        $("#result").html(data);
        //お気に入りの数を格納し、数を変更
        var fcnt = Number($(".frame-detail__action__favorite-cnt").text());
        $(".frame-detail__action__favorite-cnt").text(fcnt+1);
      })
      .fail(function() {
        console.log("error");
      });
    }
  });
/*-----------------------------------------------------------------------------
    ajaxで新規登録時のバリデーション
-----------------------------------------------------------------------------*/
  $(document).on('blur', '#yu-za-mei, #me-ruadoresu, #pasuwa-do', function() {
    var user_loginid = $('#yu-za-mei').val();
    var user_email = $('#me-ruadoresu').val();
    var user_password = $('#pasuwa-do').val();
    console.log(user_loginid);
    $.ajax({
      url: 'registration_validator.php',
      type: 'post',
      data: {
        user_loginid: user_loginid,
        user_email: user_email,
        user_password: user_password
        }
    })
    .done(function(data) {
      //バリデーションの結果を表示させる
      //console.log(data);
      $('.registration-form__loginid_result').html(data.user_loginid);
      $('.registration-form__email_result').html(data.user_email);
      $('.registration-form__password_result').html(data.user_password);
      //入力欄のcssを状態に応じて変化
      errorToggle(".registration-form__loginid_result");
      errorToggle(".registration-form__email_result");
      errorToggle(".registration-form__password_result");
    })
    .fail(function() {
      console.log('error');
      console.log("XMLHttpRequest : " + XMLHttpRequest.status);
      console.log("textStatus     : " + textStatus);
      console.log("errorThrown    : " + errorThrown.message);
    });
  });
/*-----------------------------------------------------------------------------
    .modal-login
-----------------------------------------------------------------------------*/
  $('.modal-login__trigger').click(function() {
    $('body').append('<div class="modal-login__overlay"></div>');
    $('.modal-login__overlay').fadeIn();
    var modal = '.' + $(this).attr('data-modal');
    modalResize();
    $(modal).fadeIn();
    $('.modal-login__overlay').off().click(function() {
      $(modal).fadeOut('slow', function() {
        $('.modal-login__overlay').remove();
      });
    });
    $(window).on('resize', function() {
      modalResize();
    });
    function modalResize() {
      var w = $(window).width();
      var h = $(window).height();
      //.modal-loginを真ん中に表示
      var x = (w - $(modal).outerWidth(true)) / 2;
      var y = (h - $(modal).outerHeight(true)) / 2;
      $(modal).css({'left': x + 'px','top': y + 'px'});
	  console.log(x, y);
    }
  });
/*-----------------------------------------------------------------------------
    .modal-register
-----------------------------------------------------------------------------*/
/*
  $('.modal-register__trigger').click(function() {
    $('body').append('<div class="modal-register__overlay"></div>');
    $('.modal-register__overlay').fadeIn();
    var modal = '.' + $(this).attr('data-modal');
    modalResize();
    $(modal).fadeIn();
    $('.modal-register__overlay').off().click(function() {
      $(modal).fadeOut('slow', function() {
        $('.modal-register__overlay').remove();
      });
    });
    $(window).on('resize', function() {
      modalResize();
    });
    function modalResize() {
      var w = $(window).width();
      var h = $(window).height();
      //.modal-registerを真ん中に表示
      var x = (w - $(modal).outerWidth(true)) / 2;
      var y = (h - $(modal).outerHeight(true)) / 2;
      $(modal).css({'left': x + 'px','top': y + 'px'});
	  console.log(x, y);
    }
  });
*/
/*-----------------------------------------------------------------------------
    .modal-mymenu
-----------------------------------------------------------------------------*/
  $('.modal-mymenu__trigger').click(function() {
    $('body').append('<div class="modal-mymenu__overlay"></div>');
    $('.modal-mymenu__overlay').fadeIn();
    var modal = '.' + $(this).attr('data-modal');
    modalResize();
    $(modal).fadeIn();
    $('.modal-mymenu__overlay').off().click(function() {
      $(modal).fadeOut('slow', function() {
        $('.modal-mymenu__overlay').remove();
      });
    });
    $(window).on('resize', function() {
      modalResize();
    });
    function modalResize() {
      var w = $(window).width();
      var h = $(window).height();
      //.modal-mymenuを真ん中に表示
      var x = (w - $(modal).outerWidth(true)) / 2;
      var y = (h - $(modal).outerHeight(true)) / 2;
      $(modal).css({'left': x + 'px','top': y + 'px'});
	  console.log(x, y);
    }
  });
/*-----------------------------------------------------------------------------
    .modal-sort
-----------------------------------------------------------------------------*/
  //urlからgetパラメーターを取得する
  var getparams  = new Object;
  url = location.search.substring(1).split('&');
  for(i=0; url[i]; i++) {
     var k = url[i].split('=');
     getparams[k[0]] = k[1];
  }
  //page, per_pageのgetパラメータがない時、初期値をそれぞれセット
  var sort = (getparams.sort == undefined) ? "frame_updated" : getparams.sort;
  var order = (getparams.order == undefined) ? "desc" : getparams.order;
  //console.log(sort);
  //console.log(order);
  //画面右上のトリガー部分に表示する現在設定中のソートを変数に格納する
  var sort_text = $(`.sort-${sort} a`).text();
  //デフォルトのソート・昇降順がない時だけアイコンのみ表示、それ以外は何の昇降順を選択中か右上に表示
  if (sort == "frame_updated" && order == "desc") {
    $(`.sort-${sort} a i`).addClass("fa-sort-amount-desc");
  } else if (order == "asc") {
    $(`.sort-${sort} a i`).addClass("fa-sort-amount-asc");
    $(`.sort-${sort} a i`).removeClass("fa-sort-amount-desc");
    //
    $('.modal-sort__trigger').html(`${sort_text}<i class="fa fa-sort-amount-${order}" aria-hidden="true"></i>`);
  } else if (order == "desc") {
    $(`.sort-${sort} a i`).removeClass("fa-sort-amount-asc");
    $(`.sort-${sort} a i`).addClass("fa-sort-amount-desc");
    $('.modal-sort__trigger').html(`${sort_text}<i class="fa fa-sort-amount-${order}" aria-hidden="true"></i>`);
  }
  //trigger部の実装
  $('.modal-sort__trigger').click(function() {
    $('body').append('<div class="modal-sort__overlay"></div>');
    $('.modal-sort__overlay').fadeIn();
    var modal = '.' + $(this).attr('data-modal');
    modalResize();
    $(modal).fadeIn();
    $('.modal-sort__overlay').off().click(function() {
      $(modal).fadeOut('slow', function() {
        $('.modal-sort__overlay').remove();
      });
    });
    $(window).on('resize', function() {
      modalResize();
    });
    function modalResize() {
      var w = $(window).width();
      var h = $(window).height();
      //.modal-sortを真ん中に表示
      var x = (w - $(modal).outerWidth(true)) / 2;
      var y = (h - $(modal).outerHeight(true)) / 2;
      $(modal).css({'left': x + 'px','top': y + 'px'});
    //console.log(x, y);
    }
  });
/*-----------------------------------------------------------------------------
    .nav-bar
-----------------------------------------------------------------------------*/
  var nav = $('.nav-bar');
  offset = nav.offset();
  $(window).scroll(function() {
    if($(window).scrollTop() > offset.top) {
      nav.addClass('fixed');
    } else {
      nav.removeClass('fixed');
    }
  });
/*-----------------------------------------------------------------------------
    .frame-list__admin-action__delete
-----------------------------------------------------------------------------*/
  $('.frame-list__admin-action__delete').on('click', function() {
    $answer = confirm("本当に削除してよろしいですか？");
    if ($answer) {
      alert('削除しました');
    } else {
      alert('キャンセルしました');
      return false;
    }
  });
/*-----------------------------------------------------------------------------
    .frame-detail__each-comment
-----------------------------------------------------------------------------*/
  console.log(getDevice());
  if (getDevice() == "mobile") {
    $("[data-ownpost=1]").find(".frame-detail__each-comment-right-close").css("display", "inline");
    $("[data-ownpost=1]").css("border", "1vw dotted #f5f5f5");
  } else {
    //自分の投稿のコメントにマウスが乗った時に見た目を変化させる
    $(document).on("mouseenter", ".frame-detail__each-comment", function(){
      var ownpost = $(this).data("ownpost");
      if (ownpost == 1) {
        $(this).find(".frame-detail__each-comment-right-close").css("display", "inline");
        $(this).css("border", "1vw dotted #f5f5f5");
      }
    });
    $(document).on("mouseleave", ".frame-detail__each-comment", function(){
      $(this).find(".frame-detail__each-comment-right-close").css("display", "none");
      $(this).css("border", "1vw dotted transparent");
    });
  }
/*-----------------------------------------------------------------------------
    ajaxでコメント削除
-----------------------------------------------------------------------------*/
  //バッテンをクリックでコメントを消す
  $(document).on("click", ".frame-detail__each-comment-right-close", function(){
    //確認用のアラートを出す
    $answer = confirm("コメントを削除してよろしいですか？");
    if ($answer) {
      var commentid = $(this).parent().parent().data("commentid");
      var ownpost = $(this).parent().parent().data("ownpost");
      console.log(commentid);
      if (ownpost == 1) {
        //見た目を削除して変化させる
        $(this).parent().parent().hide(500);
        console.log(commentid);
        $.ajax({
          url: "ajax.php",
          type: "post",
          data: {
            commentid: commentid,
            //ownpost: ownpost,
          }
        })
        .done(function(data) {
        })
        .fail(function() {
          console.log("comme_error");
        });
      }
    } else {
      //alert('キャンセルしました');
      return false;
    }
  });
/*-----------------------------------------------------------------------------
    .tooltip
-----------------------------------------------------------------------------*/
  var $body = $("body");
  //ログインしているかどうかをチェックする要素
  var login = $("[data-favorite]").attr("disabled");
  //console.log(login);
  //tooltipのメッセージ(お気に入りとコメント欄)
  var tooltipFavoriteMsg = "お気に入り登録をするにはログインしてください"
  var tooltipCommentMsg = "コメントするにはログインしてください"
  //ログイン時にお気に入りボタンの親(li)に
  if (login == "disabled") {
    //お気に入りへのツールチップ
    $("[data-favorite]").parent().addClass("my-tooltip");
    $("[data-favorite]").parent().attr("title", tooltipFavoriteMsg);
    //コメント欄へのツールチップ
    $(".frame-detail__comment-form").addClass("my-tooltip");
    $(".frame-detail__comment-form").attr("title", tooltipCommentMsg);
  }
  // 各 `.my-tooltip` 要素に対して処理をしていきます
  $(".my-tooltip").each(function(){
    //何度も使うので変数に格納
    var $this = $(this);
    //ターゲトのタイトルを格納
    var title = $this.attr("title");
    // ツールチップ本体(配列のタグをjoin()で連結)
    var $tooltip = $([
      "<span class='tooltip'>",
        "<span class='tooltip__body'>",
          title,
        "</span>",
      "</span>"
    ].join(""));
    //本来のツールチップを削除
    $this.attr("title", "");
    //イベントの設定(mouseoverだと子要素でイベントが発生するのでこっちを使う)
    $this.on("mouseenter", function(){
      //alert("mouseover");
      //ツールチップ追加
      $body.append($tooltip);
      //要素の表示位置
      var offset = $this.offset();
      //ターゲット要素のサイズ
      var size = {
        width: $this.outerWidth(),
        height: $this.outerHeight()
      };
      //ツールチップのサイズ
      var ttSize = {
        width: $tooltip.outerWidth(),
        height: $tooltip.outerHeight()
      };
      //要素の上に横中央で配置
      $tooltip.css({
        top: offset.top - ttSize.height,
        left: offset.left + size.width / 2 - ttSize.width / 2
      });
    })
    //マウスが離れた時発生
    .on("mouseleave", function(){
      //ツールチップを削除
      $tooltip.remove();
    });
  });
/*-----------------------------------------------------------------------------
    .mobile-pager
-----------------------------------------------------------------------------*/
/*
  var endpoint;
  if ($(".mobile-pager").length != 0) {
    $(window).on("load resize", function() {
       $mobilePager = $(".mobile-pager");
       mobilePagerHeight = $mobilePager.height();
       mobilePagerTop = $mobilePager.offset().top;
       windowHeight = $(window).height();
       cssTopValue = windowHeight * 0.03;
       endpoint = mobilePagerTop - (windowHeight - mobilePagerHeight) - cssTopValue;
      //console.log(endpoint);
    });
    //$('.mobile-pager').css("top",mobilePagerTop)
    $(window).on("load scroll resize", function() {
      var sc = $(this).scrollTop();
      //console.log(sc);
      if(sc > endpoint) {
        $(".mobile-pager").removeClass('mobile-pager__fixed');
        $(".dummy").css("display", "none");
      } else {
        $(".mobile-pager").addClass('mobile-pager__fixed');
        $(".dummy").css("display", "inline");
      }
    });
  }
*/
/*-----------------------------------------------------------------------------
    .selected-images
-----------------------------------------------------------------------------*/
  $(document).on('change', '[name="frame_image[]"]', function(){
    //画像投稿用input全て格納
    var $fp = $('[name="frame_image[]"]');
    //console.log($fp);
    //1枚目の画像要素
    //console.log($fp[0].files);
    //console.log($fp[1].files);
    //選択要素数
    //console.log($fp.length);
    var result = "";
    //画像のアップロードが一枚でもある時
    if ($fp.length > 0) {
      for (var i = 0; i < $fp.length; i++) {
        //ファイルが選択されていない時、undefinedとなるため選択された時のみ実行
        if ($fp[i].files[0] != undefined) {
          //i枚目の画像サイズ
          console.log(i);
          console.log($fp[i].files[0]);
          var imageSize = $fp[i].files[0]['size'];
          //console.log(imageSize);
          //画像サイズの単位をMBにして小数点第2位を切り上げ
          imageSize = Math.ceil(imageSize / (1024 * 1024) * 100) / 100;
          //画像追加選択用の要素
          var input = '<input type="file" name="frame_image[]" accept="image/png, image/jpeg, image/gif">';
          var ok_msg = `<span class="ok_msg">${$fp[i].files[0]['name']}【${imageSize}MB】: OK</span>`;
          var error_msg = `<span class="error_msg">${$fp[i].files[0]['name']}【${imageSize}MB】: 画像サイズが5MBを超えています</span>`;
          //画像サイズが5MB以下の場合のノーエラーで追加選択用の要素追加
          if (imageSize < 5) {
            //forのループ中なので最後のinput要素が5MB以下の場合追加選択用の要素追加、if (i == $fp.length - 1)がないと選択要素の数だけ追加されてしまうので注意
            if (i == $fp.length - 1 && $fp.length < 10) $('.image-upload').append(input);
            $(`[name="frame_image[]"]:eq(${i})`).addClass('image-no-error');
            $(`[name="frame_image[]"]:eq(${i})`).removeClass('image-error');
            $(`[name="frame_image[]"]:eq(${i})`).next('.ok_msg, .error_msg').remove();
            $(`[name="frame_image[]"]:eq(${i})`).after(ok_msg);
          } else {
            $(`[name="frame_image[]"]:eq(${i})`).addClass('image-error');
            $(`[name="frame_image[]"]:eq(${i})`).removeClass('image-no-error');
            $(`[name="frame_image[]"]:eq(${i})`).next('.ok_msg, .error_msg').remove();
            $(`[name="frame_image[]"]:eq(${i})`).after(error_msg);
          }
        }
      }
    }
  });
/*=============================================================================
      <<関数
=============================================================================*/
/*-----------------------------------------------------------------------------
    入力欄に応じてクラスをtoggleさせる関数（会員登録ページ）
-----------------------------------------------------------------------------*/
  function errorToggle(className) {
    if ($(className).text() == "OK") {
      $(className).next().addClass("input-no-error");
      $(className).next().removeClass("input-error");
    } else {
      $(className).next().removeClass("input-no-error");
      $(className).next().addClass("input-error");
    }
  }
/*-----------------------------------------------------------------------------
    ディバイスを取得
-----------------------------------------------------------------------------*/
  function getDevice() {
    var ua = navigator.userAgent;
    if (ua.indexOf('iPhone') > 0 || ua.indexOf('iPod') > 0 || ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0){
        return 'mobile';
    } else {
        return 'pc';
    }
  }
/*=============================================================================
    関数>>
=============================================================================*/
});
/*=============================================================================
    loadはreadyの外で発火
=============================================================================*/
/*-----------------------------------------------------------------------------
    .mobile-pager
-----------------------------------------------------------------------------*/
//グローバル変数の汚染を防ぐのに名前空間を用意
var pager = {};
$(window).on({
  "load resize": function() {
    if ($(".mobile-pager").length != 0) {
      pager.$mobilePager = $(".mobile-pager");
      pager.mobilePagerHeight = pager.$mobilePager.height();
      pager.mobilePagerTop = pager.$mobilePager.offset().top;
      pager.windowHeight = $(window).height();
      pager.cssTopValue = pager.windowHeight * 0.03;
      pager.endpoint = pager.mobilePagerTop - (pager.windowHeight - pager.mobilePagerHeight) - pager.cssTopValue;
      pager.sc = $(this).scrollTop();
      //console.log(sc);
      if(pager.sc > pager.endpoint) {
        $(".mobile-pager").removeClass('mobile-pager__fixed');
        $(".dummy").css("display", "none");
      } else {
        $(".mobile-pager").addClass('mobile-pager__fixed');
        $(".dummy").css("display", "inline");
      }
    }
  },
  "scroll": function() {
    if ($(".mobile-pager").length != 0) {
      pager.sc = $(this).scrollTop();
      if(pager.sc > pager.endpoint) {
        $(".mobile-pager").removeClass('mobile-pager__fixed');
        $(".dummy").css("display", "none");
      } else {
        $(".mobile-pager").addClass('mobile-pager__fixed');
        $(".dummy").css("display", "inline");
      }
    }
  },
});