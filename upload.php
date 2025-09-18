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

// フォームの入力チェック
if (empty($_POST['name']) || empty($_POST['content']) || empty($_FILES["image"]["tmp_name"])) {
    die("エラー: タイトル、本文、画像は必須項目です。");
}

$name = $_POST['name'];
$content = $_POST['content'];
$tags_input = $_POST['tags'];

// 画像アップロード処理
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$image_file_tmp = $_FILES["image"]["tmp_name"];
$image_file_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
$target_file = $target_dir . $image_file_name;
$image_path = $target_file;

if (move_uploaded_file($image_file_tmp, $target_file)) {
    $created_at = date('Y-m-d H:i:s');
    $update_at = $created_at;

    // postsテーブルへの投稿挿入
    $sql = "INSERT INTO posts (name, content, image_path, created_at, update_at) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $content, $image_path, $created_at, $update_at);
    $stmt->execute();
    $post_id = $conn->insert_id;

    $stmt->close();

    // タグの処理
    if (!empty($tags_input)) {
        $tags_array = array_map('trim', explode(',', $tags_input));
        
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
} else {
    echo "ファイルのアップロードに失敗しました。";
}

$conn->close();
?>