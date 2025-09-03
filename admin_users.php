<?php
include 'db.php';

// ユーザー一覧を取得
$result = $conn->query("SELECT id, username, password, created_at FROM users");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー管理ページ</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>登録ユーザー一覧</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>ユーザー名</th>
            <th>パスワード（ハッシュ）</th>
            <th>登録日</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["username"]); ?></td>
            <td><?php echo htmlspecialchars($row["password"]); ?></td>
            <td><?php echo $row["created_at"]; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>