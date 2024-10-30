<?php
session_start();  // セッションを開始

// 管理者としてログインしているかを確認
if (!isset($_SESSION['admin_staff_in']) || $_SESSION['admin_staff_in'] !== true) {
    header('Location: adminsLogin_iwt2024.php');
    exit;
}

// セッションリストを定義
$sessions = [
    'メインセッション' => 'main',
    'ミニセッションA-1' => 'mini_a_1',

    'ミニセッションA-2' => 'mini_a_2',
    'ミニセッションA-3' => 'mini_a_3',
    'ミニセッションB-1' => 'mini_b_1',
    'ミニセッションB-2' => 'mini_b_2'

];

// ユーザーがセッションを選択した場合
if (isset($_GET['session'])) {
    $_SESSION['selected_session'] = $_GET['session'];  // 選択したセッションを $_SESSION に保存
    header('Location: qr_scan.php');  // QRコードスキャンページにリダイレクト
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セッション選択</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        box-sizing: border-box;
    }

    .container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
        margin: 10px;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
        font-size: 22px;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    li {
        margin-bottom: 10px;
        padding: 15px;
        background-color: #e9ecef;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    li span {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    a {
        padding: 8px 12px;
        background-color: #007bff;
        color: white;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.3s;
    }

    a:hover {
        background-color: #0056b3;
    }

    /* ボタンを画面右端に配置するためのスタイル */
.header {
    width: 100%;
    display: flex;
    justify-content: flex-end;  /* 右端に寄せる */
    gap: 10px;                  /* ボタン間の間隔 */
    padding: 10px;
    position: fixed;            /* 画面上部に固定 */
    top: 0;
    right: 0;
    background-color: #f8f9fa;  /* 背景色をつけてヘッダーを目立たせる */
    z-index: 1000;              /* 他の要素より前面に表示 */
}

.header button {
    background-color: #cac2be;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.header button:hover {
    background-color: #f45b00;
}

/* iPhone向けのボタンスタイル */
@media (max-width: 480px) {
    .header {
        flex-direction: row;  /* 水平方向に配置 */
        justify-content: flex-end; /* ボタンを右端に寄せる */
        gap: 5px;
    }
    .header button {
        padding: 8px 16px;
        font-size: 14px;
    }
}

    </style>
</head>

<body>
   <div class="header">
        <form method="post" action="adminMenu.php">
            <button type="submit">メニュー</button>
        </form>
        <form method="post" action="adminsLogin_iwt2024.php">
            <button type="submit" name="logout">ログアウト</button>
        </form>
    </div>
    <div class="container">
        <h1>セッション選択</h1>
        <ul>
            <?php foreach ($sessions as $session_name => $session_code): ?>
            <li>
                <span><?php echo htmlspecialchars($session_name, ENT_QUOTES, 'UTF-8'); ?></span>
                <!-- セッション選択ボタン。選択すると GET パラメータでセッションが送信される -->
                <a href="main_reception.php?session=<?php echo urlencode($session_code); ?>">QR読み取り</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>

</html>