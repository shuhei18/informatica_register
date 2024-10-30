<?php
session_start(); // セッション開始

// ログイン確認
if (!isset($_SESSION['admin_staff_in']) || $_SESSION['admin_staff_in'] !== true) {
    header('Location: adminsLogin_iwt2024.php'); // 未ログインの場合ログインページにリダイレクト
    exit;
}
// ログアウト処理
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    // ログインページにリダイレクトする代わりに、ログインフォームを再表示
    header('Location: adminsLogin_iwt2024.php'); 
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
        margin: 0;
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
        display: block;
        margin: 20px auto 0 auto;
        padding: 15px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }

    /* スマートフォン対応 */
    @media (max-width: 480px) {
        h1 {
            font-size: 20px;
        }

        a {
            padding: 12px;
            font-size: 14px;
        }

        .logout-btn {
            font-size: 14px;
            padding: 12px;
        }
    }

    /* iPhone用 */
    @media (max-width: 375px) {
        h1 {
            font-size: 18px;
        }

        a {
            padding: 10px;
            font-size: 12px;
        }

        .logout-btn {
            font-size: 12px;
            padding: 10px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>管理者メニュー</h1>
        <ul>
            <li><a href="admin.php" target="_blank">メール一斉送信</a></li>
            <li><a href="main_reception.php" target="_blank">QR受付システム</a></li>
            <li><a href="kokyakudata_csv.php" target="_blank">ユーザー情報CSVダウンロード</a></li>
            <li><a href="rece_pdf_dl.php" target="_blank">受講票PDFダウンロード</a></li>
            <li><a href="users_information.php" target="_blank">ユーザー出席情報</a></li>
        </ul>

        <form action="adminsLogin_iwt2024.php" method="post">
            <button type="submit" class="logout-btn">ログアウト</button>
        </form>
    </div>
</body>

</html>
