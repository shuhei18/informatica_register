<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$response = ['success' => false, 'error' => ''];

debug('トークンの中身: ' . print_r($token, true));
debug('新しいパスワードの中身: ' . print_r($new_password, true));

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    debug("データベース接続成功");

    if ($token) {
        debug("トークン: $token");
        
        // トークンの検証と有効期限チェック
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
        $stmt->execute(['token' => $token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tokenData) {
            debug("トークン有効");
            debug("トークンデータ: " . print_r($tokenData, true));

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                debug("POSTメソッドによるリクエスト");

                if (empty($new_password) || empty($confirm_password)) {
                    $response['error'] = '新しいパスワードと再入力パスワードを提供してください。';
                    debug("エラー: 新しいパスワードと再入力パスワードを提供してください。");
                } elseif ($new_password !== $confirm_password) {
                    $response['error'] = 'パスワードが一致しておりません。';
                    debug("エラー: パスワードが一致しておりません。");
                } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{8,12}$/', $new_password)) {
                    $response['error'] = '半角英数字を含む8文字〜12文字で入力してください。';
                    debug("エラー: 半角英数字を含む8文字〜12文字で入力してください。");
                } else {
                    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                    debug("新しいパスワードをハッシュ化しました");

                    // パスワードの更新
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE loginID = :loginID");
                    $stmt->execute([
                        'password' => $hashedPassword,
                        'loginID' => $tokenData['loginID']
                    ]);
                    debug("パスワードを更新しました");

                    // トークンの無効化
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
                    $stmt->execute(['token' => $token]);
                    debug("トークンを無効化しました");

                    $response['success'] = true;
                    $response['error'] = '';
                    
                    header('Location: reset_success.php');
                    exit();
                }
            }
        } else {
            $response['error'] = '有効期限が切れています。ログインページからやり直しください';
            debug("エラー: 無効なトークンまたはトークンの有効期限が切れています。");
        }
    } else {
        $response['error'] = 'トークンが提供されていません。';
        debug("エラー: トークンが提供されていません。");
    }
} catch (PDOException $e) {
    $response['error'] = 'データベースエラー: ' . $e->getMessage();
    debug("データベースエラー: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $response['error']) {
    echo '
<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <!-- Google Tag Manager -->
    <!-- End Google Tag Manager -->

    <link rel="icon" type="image/png" href="assets/images/favicon.png" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="assets/js/iscroll.js"></script>
    <script type="text/javascript" src="assets/js/drawer.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/custom.css">
    <meta name="link" content="https://iwt2024.jp/" />

    <style>
        .profileform-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            margin: 0 auto;
            margin-top: 20px;
            max-width: 400px;
            width: 100%;
            margin-bottom: 5%;
        }

        .profileform-text {
            margin-block-start: 0.67em;
            margin-block-end: 0.67em;
            font-weight: bold;
            color: #333;
            text-align: center;
            font-size: clamp(12px, 1.2vw, 2em);
            
        }

        b,
        strong {
            font-size: clamp(13px, 1.2vw, 18px);
        }

        /* .profileform-container h1, h2 {
            margin-top: 20px;
            color: #333;
            text-align: center;
        }*/



        label {
            color: #555;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        p {
            color: #555;
            margin: 10px 0;
            font-size: clamp(13px, 1.2vw, 16px);
        }

        a {
            font-size: clamp(13px, 1.2vw, 16px);
        }

        .bottom-button button {
            background-color: #ff6a13;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            min-width: 9em;

        }

        .bottom-button button:hover {
            background-color: #e65b00;
        }

        .back-button {
            background-color: #ffcccc;
            color: #333;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-right: 10px;
        }

        .back-button:hover {
            background-color: #ff9999;
            color: #333;
        }

        .bottom-space {
            height: 20px;
        }

        .section-title {
            text-align: center;
            color: #ffffff;
            background: linear-gradient(92.34deg, #FF7D00 -1.28%, #E23400 104.91%);
            padding: 10px;
            font-size: clamp(13px, 1.2vw, 16px);
        }

        .section-content {
            background-color: #ffffff;
            padding: 10px 50px;
            color: #000000;
        }

        .privacy-note {
            font-size: 14px;
            color: #333;
            margin-top: 20px;
        }

        .privacy-note a {
            color: #ff6a13;
            text-decoration: none;
        }

        .privacy-note a:hover {
            text-decoration: underline;
        }

        .display-none {
            display: none;
        }

        /*　ボタン */
             .select-btn-box {
                /* display: flex; */
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-evenly;
                margin-bottom: 20px;
                gap: 0px;
            }

            .select-btn {
                margin: 0 10px;
                width: 215px;
                padding: 12px 20px;
                color: #FFF;
                background: linear-gradient(92.34deg, #FF7D00 -1.28%, #E23400 104.91%) !important;
            }

            .select-btn-2 {
                margin: 0 10px;
                width: 215px;
                padding: 12px 20px;
                color: #001AFF !important;
                border: 2px solid #001AFF;
                outline: 2px solid transparent;
                background: #FFF;
                text-align: center;
            }

            @media (max-width: 767.98px) {
        .section-content {

            padding: 10px 0px;

        }
        .select-btn-box {

                gap: 10px;
            }
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            background-color: #ff6a13;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #e65b00;
        }
        .link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #ff6a13;
            text-decoration: none;
            font-weight: bold;
        }
        .link:hover {
            color: #e65b00;
        }
.margin{
margin-bottom:15px;
}


    </style>
</head>

<body class="site drawer drawer--left">

    <!-- Header -->
    <header class="site-header">

        <div class="container">
            <div class="site-branding">
                <img src="assets/images/logo/informatica-logo.png" class="site-logo" alt="Infomatica">
            </div>
        </div>
        <div class="header-right"></div>
    </header>


    <!-- End Header-->
    <div class="infa-world-headline-img"></div>

    <div class="site-main">


        <section class="hero">
            <div id="home" class="area_top_view">
                <div class="container">
                    <div class="container-top">
                        <div class="container-top-box">
                            <div class="text-box">
                                <img class="iw23-logo" src="assets/images/event-logo.png" height="120">
                                <h1>2024年9月13日(金)｜大手町プレイス ホール＆カンファレンス</h1>

                            </div>
                        </div>

                        <div class="container-top-box">
                            <img class="container-top-box-img" src="assets/images/iw24-main-visual.png" height="120">
                        </div>
                    </div>


                </div>
            </div>
        </section>
<title>パスワード再設定</title>

           <div class="profileform-container">
        <h1 class="profileform-text">パスワード再設定</h1>
        <center><p>新しいパスワードを入力してください</p></center>
<div class="margin">
<small><font color="red">半角英数字を含む8文字〜12文字で入力してください。</small></div>
            <form method="POST">
                <label for="new_password">新しいパスワード:</label>
                <input type="password" id="new_password" name="new_password" required>
                <label for="confirm_password">新しいパスワード再入力:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
<div class="select-btn-box">
                <button type="submit" class="select-btn">リセット</button>
</div>
            </form>
            <p>' . htmlspecialchars($response['error'], ENT_QUOTES, 'UTF-8') . '</p>
        </div>
    </body>
    </html>';
}
?>