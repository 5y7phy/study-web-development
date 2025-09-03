<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_name'])) {
    $room_name = $_POST['room_name'];
    $sql = "INSERT INTO rooms (room_name, created_by) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $room_name, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $room_id = $stmt->insert_id;
        header("Location: chat.php?room_id=" . $room_id);
        exit();
    }
}
?>

<h2>部屋を作成</h2>
<form method="post" style="margin-bottom:20px;">
    部屋名: <input type="text" name="room_name" required>
    <button type="submit">作成</button>
</form>

<hr>

<h2>チャットルーム一覧</h2>
<ul>
<?php
$result = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    echo "<li><a href='chat.php?room_id=".$row['id']."'>".$row['room_name']."</a></li>";
}
?>
</ul>