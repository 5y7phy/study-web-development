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

$search_tag = $_GET['tag'] ?? '';

$posts = [];
if (!empty($search_tag)) {
    $sql = "SELECT p.id, p.name, p.content, p.image_path, p.created_at FROM posts p JOIN post_tag pt ON p.id = pt.post_id JOIN tags t ON pt.tag_id = t.id WHERE t.name = ? ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_tag);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タグ検索: <?php echo htmlspecialchars($search_tag); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>写真ブログ</h1>
        <a href="posts.php">投稿一覧へ</a>
    </header>

    <main>
        <h2>「<?php echo htmlspecialchars($search_tag); ?>」の検索結果</h2>
        <div class="post-list">
            <?php if (empty($posts)): ?>
                <p>該当する投稿は見つかりませんでした。</p>
            <?php else: ?>
                <?php foreach ($posts as $row): ?>
                    <div class="post-card">
                        <h3><?php echo htmlspecialchars($row["name"]); ?></h3>
                        <img src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="<?php echo htmlspecialchars($row["name"]); ?>" class="thumbnail">
                        <p class="post-meta">投稿日: <?php echo $row["created_at"]; ?></p>
                        <a href="post_detail.php?id=<?php echo $row["id"]; ?>">詳細を見る</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>