<?php require_once("system/common.php");
try {
/*-----------------------------------------------------------------------------
    ajaxでコメント削除
-----------------------------------------------------------------------------*/
  $sql = "delete from comments where comment_id = :comment_id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":comment_id", $_POST["commentid"], PDO::PARAM_INT);
  $row_frame = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt->execute();
  $stmt = null;
} catch (PDOException $e) {
  die("エラー: " . $e->getMessage());
}
?>