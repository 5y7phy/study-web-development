<?php
// データベース接続設定
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ブログ";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    die("エラー: 投稿IDが指定されていません。");
}

// データベースから投稿を削除
$sql = "DELETE FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    header("Location: posts.php");
    exit();
} else {
    echo "削除に失敗しました: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>