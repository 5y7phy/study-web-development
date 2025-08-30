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
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        try {
            $stmt->execute([$username, $password_hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;

            // 登録成功 → チャットページに遷移
            header('Location: room.php');
            exit;
        } catch (PDOException $e) {
            // ユーザー名が重複した場合
            $error = "そのユーザー名は既に使われています";
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
<title>新規登録 - 匿名チャット</title>
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

<h1>新規登録</h1>

<?php if (isset($error)) echo "<div class='error'>{$error}</div>"; ?>

<form method="post" action="">
  <label>ユーザー名:</label>
  <input type="text" name="username" required>

  <label>パスワード:</label>
  <input type="password" name="password" required>

  <button type="submit">登録</button>
</form>

<div class="link">
  <a href="login.php">すでにアカウントをお持ちですか？ログインする</a>
</div>

</body>
</html>