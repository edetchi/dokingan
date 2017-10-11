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
});