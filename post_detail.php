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

$post_id = $_GET['id'];

// SQLクエリのカラム名を修正
$sql = "SELECT name, content, image_path, created_at FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
} else {
    die("投稿が見つかりませんでした。");
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>写真ブログ</h1>
        <a href="posts.php">投稿一覧へ</a>
    </header>

    <main>
        <article class="post-detail">
            <h2><?php echo htmlspecialchars($post['name']); ?></h2>
            <p class="post-meta">投稿日: <?php echo $post['created_at']; ?></p>
            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['name']); ?>" class="full-image">
            <p class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        </article>
    </main>

</body>
</html>