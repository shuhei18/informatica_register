<?php
session_start();
require 'config.php';

if (!isset($_POST['sessions']) || !isset($_SESSION['loginID'])) {
    echo "データが不足しています。";
    exit;
}

$loginID = $_SESSION['loginID'];
$sessions = json_decode($_POST['sessions'], true);

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);

    // 既存の講演情報を削除
    $stmt = $pdo->prepare("DELETE FROM users WHERE loginID = ? AND lecture_time IS NOT NULL AND hall IS NOT NULL");
    $stmt->execute([$loginID]);

    // 新しい講演情報を挿入
    foreach ($sessions as $session) {
        list($time, $title, $hall) = explode(";", $session);
        $stmt = $pdo->prepare("INSERT INTO users (loginID, lecture_time, lecture_title, hall) VALUES (?, ?, ?, ?)");
        $stmt->execute([$loginID, $time, $title, $hall]);
    }

    echo "講演情報が更新されました。";
    header("Location: mypage.php"); // 更新後にマイページにリダイレクト

} catch (PDOException $e) {
    echo "データベースエラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>