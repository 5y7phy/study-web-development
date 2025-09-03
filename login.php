<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: rooms.php");
        exit();
    } else {
        echo "ログイン失敗";
    }
}
?>

<form method="post">
    <h2>ログイン</h2>
    ユーザー名: <input type="text" name="username" required><br>
    パスワード: <input type="password" name="password" required><br>
    <button type="submit">ログイン</button>
</form>

<p><a href="register.php">新規登録はこちら</a></p>