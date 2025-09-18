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

// 投稿データを取得
$sql = "SELECT name, content, image_path FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    die("エラー: 該当する投稿が見つかりませんでした。");
}

// 投稿に紐づくタグを取得
$tags_sql = "SELECT t.name FROM tags t JOIN post_tag pt ON t.id = pt.tag_id WHERE pt.post_id = ?";
$tags_stmt = $conn->prepare($tags_sql);
$tags_stmt->bind_param("i", $post_id);
$tags_stmt->execute();
$tags_result = $tags_stmt->get_result();
$current_tags = [];
while ($tag_row = $tags_result->fetch_assoc()) {
    $current_tags[] = $tag_row['name'];
}
$tags_stmt->close();

// フォームが送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST['name'];
    $new_content = $_POST['content'];
    $new_tags_input = $_POST['tags'];
    $new_image_path = $post['image_path'];

    // 画像が新しくアップロードされたか確認
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // 古い画像を削除
        if (file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }
        
        // 新しい画像をアップロード
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_file_tmp = $_FILES["image"]["tmp_name"];
        $image_file_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_file_name;
        
        if (move_uploaded_file($image_file_tmp, $target_file)) {
            $new_image_path = $target_file;
        } else {
            die("新しいファイルのアップロードに失敗しました。");
        }
    }
    
    // postsテーブルを更新
    $update_sql = "UPDATE posts SET name = ?, content = ?, image_path = ?, update_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $new_name, $new_content, $new_image_path, $post_id);
    $update_stmt->execute();
    $update_stmt->close();

    // タグを更新
    // 既存の関連付けを削除
    $delete_tags_sql = "DELETE FROM post_tag WHERE post_id = ?";
    $delete_tags_stmt = $conn->prepare($delete_tags_sql);
    $delete_tags_stmt->bind_param("i", $post_id);
    $delete_tags_stmt->execute();
    $delete_tags_stmt->close();
    
    // 新しいタグを再登録
    if (!empty($new_tags_input)) {
        $tags_array = array_map('trim', explode(',', $new_tags_input));
        
        $tag_sql = "INSERT IGNORE INTO tags (name) VALUES (?)";
        $post_tag_sql = "INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)";

        foreach ($tags_array as $tag_name) {
            $tag_stmt = $conn->prepare($tag_sql);
            $tag_stmt->bind_param("s", $tag_name);
            $tag_stmt->execute();
            $tag_id = $conn->insert_id;

            if ($tag_id == 0) {
                $get_tag_id_sql = "SELECT id FROM tags WHERE name = ?";
                $get_tag_id_stmt = $conn->prepare($get_tag_id_sql);
                $get_tag_id_stmt->bind_param("s", $tag_name);
                $get_tag_id_stmt->execute();
                $get_tag_id_result = $get_tag_id_stmt->get_result();
                $tag_id = $get_tag_id_result->fetch_assoc()['id'];
            }
            $tag_stmt->close();

            $post_tag_stmt = $conn->prepare($post_tag_sql);
            $post_tag_stmt->bind_param("ii", $post_id, $tag_id);
            $post_tag_stmt->execute();
            $post_tag_stmt->close();
        }
    }

    header("Location: posts.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>投稿編集</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>投稿を編集</h1>
        <a href="posts.php">投稿一覧へ</a>
    </header>

    <main>
        <form action="edit.php?id=<?php echo htmlspecialchars($post_id); ?>" method="POST" enctype="multipart/form-data">
            <label for="name">タイトル:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($post['name']); ?>" required>

            <label for="content">本文:</label>
            <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

            <label for="image">現在の写真:</label>
            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="現在の写真" style="max-width: 200px;">
            <label for="image">新しい写真を選択:</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <label for="tags">タグ (カンマ区切り):</label>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars(implode(', ', $current_tags)); ?>">

            <button type="submit">更新</button>
        </form>
    </main>

</body>
</html>