<?php
session_start();
require 'config.php';

// POSTリクエストの場合にのみ処理を実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // パスワードのバリデーション
    if (empty($username) || empty($password)) {
        $error_message = "ユーザー名とパスワードを入力してください。";
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[A-Za-z\d]{8,12}$/', $password)) {
        $error_message = "パスワードは英数字を含む8桁から12桁以内で入力してください。";
    } else {
        // パスワードをハッシュ化
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("INSERT INTO admin_staffs (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $success_message = "管理者が追加されました。";
        } catch (PDOException $e) {
            // エラーメッセージをユーザーに表示しない
            $error_message = "エラーが発生しました。";
            // エラーの詳細はログに記録する
            error_log("Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>新しい管理者を追加</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #e9ecef;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        width: 400px;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
        font-size: 24px;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 8px;
        color: #495057;
        font-weight: 600;
    }

    input[type="text"],
    input[type="password"] {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        font-size: 16px;
        color: #495057;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #80bdff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
    }

    .button-container {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        gap: 10px;
    }

    button,
    .admin-button {
        padding: 10px;
        background-color: #6c766e;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        width: 100%;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #218838;
    }

    .admin-button {
        background-color: #0069d9;
    }

    .admin-button:hover {
        background-color: #0056b3;
    }

    .admin-button {
        text-decoration: none;
        /* 下線を削除 */
    }

    p.error {
        text-align: center;
        color: red;
        font-weight: bold;
        margin-bottom: 15px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>新しい管理者を追加</h1>

        <?php if (isset($error_message)) : ?>
        <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)) : ?>
        <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">ユーザー名</label>
            <input type="text" id="username" name="username" required>
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
            <div class="button-container">
                <button type="submit">管理者を追加</button>
                <a href="adminsLogin_iwt2024.php" class="admin-button">管理者ログイン</a>
            </div>
        </form>

    </div>
</body>

</html>