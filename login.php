<?php
session_start();

// データベース接続設定
$dsn = 'mysql:host=localhost;dbname=chat_db;charset=utf8';
$db_user = 'root';      // MySQLのユーザー名
$db_pass = '';          // MySQLのパスワード

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('DB接続失敗: ' . $e->getMessage());
}

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // ログイン成功 → チャットページへ
            header('Location: room.php');
            exit;
        } else {
            $error = "ユーザー名またはパスワードが違います";
        }
    } else {
        $error = "ユーザー名とパスワードを入力してください";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン - 匿名チャット</title>
<style>
body { font-family: Arial, sans-serif; text-align: center; padding-top: 80px; }
form { display: inline-block; text-align: left; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
input { display: block; width: 200px; margin-bottom: 10px; padding: 5px; }
button { width: 100%; padding: 8px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background-color: #45a049; }
.error { color: red; margin-bottom: 10px; }
.link { margin-top: 15px; text-align: center; }
.link a { color: #333; text-decoration: none; }
.link a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h1>ログイン</h1>

<?php if (isset($error)) echo "<div class='error'>{$error}</div>"; ?>

<form method="post" action="">
  <label>ユーザー名:</label>
  <input type="text" name="username" required>

  <label>パスワード:</label>
  <input type="password" name="password" required>

  <button type="submit">ログイン</button>
</form>

<div class="link">
  <a href="register.php">新規登録する</a>
</div>

</body>
</html>