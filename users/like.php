<?php
require_once "../includes/db.php";
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    die("Unauthorized");
}

if(isset($_GET['news_id'])) {
    $news_id = intval($_GET['news_id']);
    $user_id = $_SESSION['user_id'];

    // Check if already liked
    $stmt = $conn->prepare("SELECT * FROM reactions WHERE news_id=? AND user_id=?");
    $stmt->execute([$news_id, $user_id]);
    if($stmt->rowCount() == 0){
        // Insert reaction
        $stmt = $conn->prepare("INSERT INTO reactions (news_id,user_id,type) VALUES (?,?, 'like')");
        $stmt->execute([$news_id, $user_id]);
    }
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
