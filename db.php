<?php
// データベース接続情報
$host = "localhost";   // サーバー名（phpMyAdminと同じ）
$user = "root";        // phpMyAdminのユーザー名
$pass = "root";            // パスワード（環境によって設定）
$dbname = "chat_db";  // 作ったデータベース名

// 接続
$conn = new mysqli($host, $user, $pass, $dbname);

// エラーチェック
if ($conn->connect_error) {
    die("データベース接続失敗: " . $conn->connect_error);
}

// セッション開始（ログイン情報を保存するため）
session_start();
?>