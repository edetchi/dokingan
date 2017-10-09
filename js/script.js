$(function(){
  var fbtn = $("button[data-favorite]");
  if (fbtn.data("favorite")==0)  {
    fbtn.css("color", "yellow");
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
        fbtn.css("color", "");
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
        fbtn.css("color", "yellow");
        $("#result").html(data);
      })
      .fail(function() {
        console.log("error");
      });
    }
  });
});