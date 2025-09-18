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

$sql = "SELECT id, name, content, image_path, created_at FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ブログトップ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>写真ブログ</h1>
        <a href="index.html">新しい投稿を作成</a>
    </header>

    <main>
        <div class="header-with-search">
            <h2>最新の投稿</h2>
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="tag" placeholder="タグで検索...">
                <button type="submit">検索</button>
            </form>
        </div>
        <div class="post-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // タグを取得する
                    $tags_sql = "SELECT t.name FROM tags t JOIN post_tag pt ON t.id = pt.tag_id WHERE pt.post_id = ?";
                    $tags_stmt = $conn->prepare($tags_sql);
                    $tags_stmt->bind_param("i", $row['id']);
                    $tags_stmt->execute();
                    $tags_result = $tags_stmt->get_result();
                    $tags = [];
                    while($tag_row = $tags_result->fetch_assoc()) {
                        $tags[] = $tag_row['name'];
                    }
                    $tags_stmt->close();

                    echo '<div class="post-card">';
                    
                    // タイトルを詳細ページへのリンクにする
                    echo '<h3><a href="post_detail.php?id=' . $row["id"] . '">' . htmlspecialchars($row["name"]) . '</a></h3>';
                    
                    echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '" class="thumbnail">';
                    echo '<p class="post-meta">投稿日: ' . $row["created_at"] . '</p>';

                    if (!empty($tags)) {
                        echo '<p class="tags">';
                        foreach($tags as $tag) {
                            echo '<a href="search.php?tag=' . urlencode($tag) . '">#' . htmlspecialchars($tag) . '</a> ';
                        }
                        echo '</p>';
                    }

                    // 編集と削除のボタン
                    echo '<div class="actions">';
                    echo '<a href="edit.php?id=' . $row["id"] . '">編集</a>';
                    echo '<a href="delete.php?id=' . $row["id"] . '" onclick="return confirm(\'本当に削除しますか？\');">削除</a>';
                    echo '</div>';

                    echo '</div>';
                }
            } else {
                echo "<p>まだ投稿はありません。</p>";
            }
            ?>
        </div>
    </main>

</body>
</html>
<?php
$conn->close();
?>