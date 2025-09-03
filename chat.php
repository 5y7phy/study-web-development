<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$room_id = $_GET['room_id'] ?? 0;

// 部屋名を取得
$sql = "SELECT room_name FROM rooms WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$room_name = $room['room_name'] ?? "不明な部屋";

// メッセージ送信
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg = $_POST['message'] ?? "";

    // メッセージをDBに保存
    $sql = "INSERT INTO messages (room_id, user_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $room_id, $_SESSION['user_id'], $msg);
    $stmt->execute();
    $message_id = $stmt->insert_id;

    // 複数画像アップロード処理
    if (!empty($_FILES['images']['name'][0])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($_FILES['images']['name'] as $key => $name) {
            $fileName = time() . "_" . basename($name);
            $targetFilePath = $targetDir . $fileName;

            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowTypes = ['jpg','jpeg','png','gif'];

            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $targetFilePath)) {
                    $sql = "INSERT INTO message_images (message_id, image_path) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $message_id, $targetFilePath);
                    $stmt->execute();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($room_name); ?> - チャット</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .chat-box {
            border: 1px solid #ccc;
            height: 600px;
            overflow-y: scroll;
            display: flex;
            flex-direction: column-reverse;
            padding: 10px;
            font-size: 16px;
        }
        .chat-msg {
            margin-bottom: 15px;
        }
        .chat-img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ccc;
            margin-top: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .chat-img:hover {
            opacity: 0.8;
        }
        form {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        input[type="text"] {
            flex: 1;
            padding: 10px;
            font-size: 16px;
        }
        input[type="file"] {
            flex: 1;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
        }
        /* モーダル背景 */
        #imgModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
        }
        /* 拡大画像 */
        #imgModal img {
            max-width: 90%;
            max-height: 90%;
            border: 4px solid #fff;
            border-radius: 4px;
        }
        /* 閉じるボタン */
        #imgModal .close-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            color: #fff;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- 部屋を出るボタン -->
    <div style="text-align:left; margin-bottom:10px;">
        <a href="rooms.php" style="padding:5px 10px; background:#ddd; text-decoration:none; border:1px solid #aaa;">← 部屋を出る</a>
    </div>

    <h2><?php echo htmlspecialchars($room_name); ?></h2>

    <!-- チャット表示 -->
    <div class="chat-box">
    <?php
    $sql = "SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON m.user_id=u.id 
            WHERE room_id=? 
            ORDER BY m.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<div class='chat-msg'><b>".htmlspecialchars($row['username']).":</b> ";
        if (!empty($row['message'])) {
            echo htmlspecialchars($row['message']);
        }

        // 複数画像を取得
        $sql_img = "SELECT image_path FROM message_images WHERE message_id=?";
        $stmt_img = $conn->prepare($sql_img);
        $stmt_img->bind_param("i", $row['id']);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();

        while ($img = $result_img->fetch_assoc()) {
            echo "<br><img src='".htmlspecialchars($img['image_path'])."' class='chat-img'>";
        }

        echo "</div>";
    }
    ?>
    </div>

    <!-- 入力フォーム -->
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="message" placeholder="メッセージを入力">
        <input type="file" name="images[]" multiple accept="image/*">
        <button type="submit">送信</button>
    </form>

    <!-- モーダル -->
    <div id="imgModal">
        <span class="close-btn">&times;</span>
        <img id="modalImg">
    </div>

    <script>
    // 画像クリックで拡大
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('chat-img')) {
            document.getElementById('imgModal').style.display = 'flex';
            document.getElementById('modalImg').src = e.target.src;
        }
    });

    // 閉じるボタン
    document.querySelector('#imgModal .close-btn').addEventListener('click', function() {
        document.getElementById('imgModal').style.display = 'none';
    });

    // 背景クリックでも閉じる
    document.getElementById('imgModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
    </script>
</body>
</html>