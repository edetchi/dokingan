$(function(){
/*-----------------------------------------------------------------------------
    ajaxでお気に入り登録
-----------------------------------------------------------------------------*/
/*
  var fbtn = $('button[data-favorite]');
  if (fbtn.data('favorite')==0)  {
    fbtn.css('color', 'yellow');
  }
  fbtn.click(function() {
    if (fbtn.data('favorite')==0) {
      $.ajax({
        url: 'favorite.php',
        type: 'post',
        data: {favorite: 1}
      })
      .done(function(data) {
        fbtn.data('favorite', 1);
        fbtn.css('color', '');
        $('#result').html(data);
      })
      .fail(function() {
        console.log('error');
      });
    }
    if (fbtn.data('favorite')==1) {
      $.ajax({
        url: 'favorite.php',
        type: 'post',
        data: {favorite: 0}
      })
      .done(function(data) {
        fbtn.data('favorite', 0);
        fbtn.css('color', 'yellow');
        $('#result').html(data);
      })
      .fail(function() {
        console.log('error');
      });
    }
  });
*/
/*-----------------------------------------------------------------------------
    ajaxでお気に入り登録改
-----------------------------------------------------------------------------*/
  var fbtn = $("button[data-favorite]");
  if (fbtn.data("favorite")==0)  {
    //fbtn.css("color", "yellow");
    $("button[data-favorite] i").addClass("fa-star").css("color", "yellow");
  } else {
    $("button[data-favorite] i").addClass("fa-star-o");
  }
  fbtn.click(function() {
    if (fbtn.data("favorite")==0) {
      $.ajax({
        url: "favorite.php",
        type: "post",
        data: {favorite: 1}
      })
      .done(function(data) {
        fbtn.data("favorite", 1);
        $("button[data-favorite] i").removeClass("fa-star").addClass("fa-star-o").css("color", "");
        $("#result").html(data);
      })
      .fail(function() {
        console.log("error");
      });
    }
    if (fbtn.data("favorite")==1) {
      $.ajax({
        url: "favorite.php",
        type: "post",
        data: {favorite: 0}
      })
      .done(function(data) {
        fbtn.data("favorite", 0);
        $("button[data-favorite] i").removeClass("fa-star-o").addClass("fa-star").css("color", "yellow");
        $("#result").html(data);
      })
      .fail(function() {
        console.log("error");
      });
    }
  });
/*-----------------------------------------------------------------------------
    ajaxで登録時にバリデーションチェック
-----------------------------------------------------------------------------*/
  $(document).on('blur', '#yu-za-mei, #me-ruadoresu, #pasuwa-do', function() {
    var user_loginid = $('#yu-za-mei').val();
    var user_email = $('#me-ruadoresu').val();
    var user_password = $('#pasuwa-do').val();
    console.log(user_password);
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
      //console.log(data);
      $('.user_loginid_result').html(data.user_loginid);
      $('.user_email_result').html(data.user_email);
      $('.user_password_result').html(data.user_password);
      //入力欄のcssを状態に応じて変化
      errorToggle(".user_loginid_result");
      errorToggle(".user_email_result");
      errorToggle(".user_password_result");
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
    .frame-detail__delete
-----------------------------------------------------------------------------*/
$('.frame-detail__delete').on('click', function() {
  $answer = confirm("本当に削除してよろしいですか？");
  if ($answer) {
    alert('削除しました');
  } else {
    alert('キャンセルしました');
    return false;
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
/*=============================================================================
    関数>>
=============================================================================*/
});