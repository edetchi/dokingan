<?php require_once("system/common.php"); ?>
<?php

//echo $_POST["favorite"];
try {
  $sql = "select * from favorites where user_id = :user_id and frame_id = :frame_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
  $stmt->bindValue(":frame_id", $_SESSION["frame_id"], PDO::PARAM_INT);
  $stmt->execute();
  $row_favorite = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt = null;
  if (($row_favorite["removed_flag"]  == 0  or $row_favorite["removed_flag"]  == 1) and ($row_favorite["removed_flag"] !== null)) {
    $sql = "update favorites set removed_flag = :removed_flag where user_id = :user_id and frame_id = :frame_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":frame_id", $_SESSION["frame_id"], PDO::PARAM_INT);
    $stmt->bindValue(":removed_flag", $_POST["favorite"], PDO::PARAM_INT);
    $stmt->execute();
  } else if ($row_favorite["removed_flag"] === null) {
    $sql = "insert into favorites (user_id, frame_id, removed_flag) values(:user_id, :frame_id, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":frame_id", $_SESSION["frame_id"], PDO::PARAM_INT);
    //$stmt->bindValue(":removed_flag", 0, PDO::PARAM_INT);
    $stmt->execute();
  }
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
var_export($row_favorite["removed_flag"]);
?>