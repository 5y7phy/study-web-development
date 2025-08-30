<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root"; // ここは環境に合わせて
$password = "";
$dbname = "chat_app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// 部屋を作成した場合
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_name'])) {
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $sql = "INSERT INTO rooms (room_name, created_by) VALUES ('$room_name', " . $_SESSION['user_id'] . ")";
    $conn->query($sql);
    header("Location: chat.php?room=" . $conn->insert_id);
    exit;
}

// 部屋一覧を取得
$sql = "SELECT * FROM rooms ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>部屋一覧</title>
</head>
<body>
    <h2>部屋一覧</h2>

    <form method="POST">
        <input type="text" name="room_name" placeholder="部屋名を入力" required>
        <button type="submit">部屋を作成</button>
    </form>

    <h3>既存の部屋</h3>
    <ul>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <li><a href="chat.php?room=<?= $row['id'] ?>"><?= htmlspecialchars($row['room_name']) ?></a></li>
        <?php } ?>
    </ul>
</body>
</html>