<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:host=localhost;dbname=chat_db;charset=utf8';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('DB接続失敗: ' . $e->getMessage());
}

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if ($content) {
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, content) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $content]);
    }
}

// 投稿一覧取得（古い順に表示）
$stmt = $pdo->query('SELECT p.content, p.created_at, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at ASC');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>匿名チャット</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; height: 100vh; display: flex; flex-direction: column; }
.logout { text-align: right; padding: 10px; }
.logout a { color: #333; text-decoration: none; }
.logout a:hover { text-decoration: underline; }
.chat-wrapper { flex: 1; display: flex; flex-direction: column-reverse; overflow-y: auto; padding: 10px; }
.post { border-bottom: 1px solid #ccc; padding: 5px 0; }
.username { font-weight: bold; }
.timestamp { font-size: 0.8em; color: #888; }
form { display: flex; border-top: 1px solid #ccc; padding: 10px; }
textarea { flex: 1; height: 50px; padding: 5px; resize: none; }
button { width: 80px; margin-left: 5px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background-color: #45a049; }
</style>
</head>
<body>

<div class="logout">
    <a href="logout.php">ログアウト</a>
</div>

<div class="chat-wrapper">
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <span class="username"><?php echo htmlspecialchars($post['username']); ?>:</span>
            <span class="content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></span><br>
            <span class="timestamp"><?php echo $post['created_at']; ?></span>
        </div>
    <?php endforeach; ?>
</div>

<form method="post" action="">
    <textarea name="content" placeholder="メッセージを入力..." required></textarea>
    <button type="submit">送信</button>
</form>

</body>
</html>