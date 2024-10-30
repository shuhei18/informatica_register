<?php
session_start();  // セッションを開始

// 管理者としてログインしているかを確認
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: adminLogin.php');  // 未ログインならログインページにリダイレクト
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者メニュー</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        width: 100%;
        max-width: 600px;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        margin: 10px 0;
    }

    a {
        display: block;
        padding: 15px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        font-size: 16px;
    }

    a:hover {
        background-color: #0056b3;
    }

    .logout-btn {
        background-color: #dc3545;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }

    </style>
</head>

<body>
    <div class="container">
        <h1>管理者メニュー</h1>
        <ul>
            <li><a href="admin.php">メール一斉送信</a></li>
            <li><a href="main_reception.php">QR受付システム</a></li>
            <li><a href="kokyakudata_csv.php">顧客情報CSVダウンロード</a></li>
            <li><a href="rece_pdf_dl.php">講演情報PDFダウンロード</a></li>
        </ul>

        <form action="adminLogout.php" method="post">
            <button type="submit" class="logout-btn">ログアウト</button>
        </form>
    </div>
</body>

</html>
