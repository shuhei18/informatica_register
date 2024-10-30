<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

session_start();
require 'vendor/autoload.php';
require 'config.php'; // データベース設定を含むファイルを読み込む

use TCPDF;
// 下記追加
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;


// ユーザーIDをセッションから取得
if (isset($_SESSION['loginID'])) {
    $loginID = $_SESSION['loginID'];
} else {
    // ユーザーがログインしていない場合の処理
    header('Location: login.php');
    exit;
}
// POSTデータの取得
$sessions = isset($_POST['sessions']) ? json_decode($_POST['sessions'], true) : [];
$loginID = isset($_POST['loginID']) ? $_POST['loginID'] : '';

// データベース接続
$pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 既存の講演情報を削除
$stmt = $pdo->prepare('DELETE FROM user_lectures WHERE loginID = ?');
$stmt->execute([$loginID]);

// 新しい講演情報を追加
foreach ($sessions as $session) {
    $lecture_time = $session['time'];
    $lecture_title = $session['title'];
    $hall = $session['hall'];

    // データベースに新しい講演情報を追加
    $stmt = $pdo->prepare('INSERT INTO user_lectures (loginID, lecture_time, lecture_title, hall) VALUES (?, ?, ?, ?)');
    $stmt->execute([$loginID, $lecture_time, $lecture_title, $hall]);
}


// ユーザー情報を取得
$stmt = $pdo->prepare("SELECT work_email, surname, given_name, corporate_type, corporate_prefix, company_name FROM users WHERE loginID = ?");
$stmt->execute([$loginID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$corporate_type = $user['corporate_type'];
$corporate_prefix = $user['corporate_prefix'];
$company_name = $user['company_name'];
$surname = $user['surname'];
$given_name = $user['given_name'];

$corporateDisplayName = $corporate_prefix === '前' ? $corporate_type . '' . $company_name : ($corporate_prefix === '後' ? $company_name . '' . $corporate_type : $company_name);
        
        
$sender_name = "Informatica World Tour 2024 事務局";
$sender_email = "iwt2024_registration@s-bev.jp";
$subject = " 【Informatica World Tour 2024】変更完了のご連絡";
$message = <<<EOT

            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            【Informatica World Tour 2024】変更完了のご連絡
            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            このたびは、「Informatica World Tour 2024」にお申し込みいただきまして、
            誠にありがとうございます。
            お客様の変更のお手続きが完了いたしましたのでご連絡申し上げます。
            後日、事務局より当日来場時に必要な参加受講票を、お送りいたします。

            ＜Informatica World Tour 2024 開催概要＞
            会期：2024年9月13日(金) 13:30-18:30（13:00- 受付/展示オープン）
            会場：大手町プレイス ホール&カンファレンス（2F 受付）
            　　　東京都千代田区大手町2-3-1大手町プレイス(イーストタワー) 1F/2F
            アクセス：東京メトロ・都営地下鉄 「大手町」駅 A5出口直結
            https://otemachi-place-hc.jp/access.html


            ＜お客様情報＞
            ----------------------------------------------------------------------
            [ID] {$loginID}
            [会社名] {$corporateDisplayName}
            [氏名] {$surname} {$given_name}
            [セッション]
            マイページより、ご登録いただいたセッションをご確認いただけます。
            ----------------------------------------------------------------------

            ＜マイページ＞
            下記URLよりアクセスいただき、お客様情報に記載されている[ID]番号とご登録いただいた[パスワード]をご入力の上、マイページへログインしてください。
            URL：https://s-bev.jp/login.php

            ご登録いただいた[パスワード]が不明の場合は、マイページのログイン画面にあります
            「パスワードを忘れた方」よりお問い合わせください。


            ＜お問い合わせ＞
            Informatica World Tour 2024 事務局
            ◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内）
            　Email：iwt2024_registration@s-bev.jp

            ◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内）
            　Email：iwt2024@jevent.jp

EOT;


$sesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
    'credentials' => [
        'key'    => 'AKIAXF53I65F66G6TK6R',
        'secret' => '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',
    ],
]);

try {
    $result = $sesClient->sendEmail([
        'Source' => "=?UTF-8?B?" . base64_encode($sender_name) . "?= <$sender_email>",
        'Destination' => [
            'ToAddresses' => [$user['work_email']],
        ],
        'Message' => [
            'Subject' => [
                'Data' => $subject,
                'Charset' => 'UTF-8',
            ],
            'Body' => [
                'Text' => [
                    'Data' => $message,
                    'Charset' => 'UTF-8',
                ],
            ],
        ],
    ]);

} catch (AwsException $e) {
    error_log("メール送信失敗: " . $e->getMessage());
}
       
?>
<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">


    <link rel="icon" type="image/png" href="assets/images/favicon.png" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="assets/js/iscroll.js"></script>
    <script type="text/javascript" src="assets/js/drawer.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/custom.css">

    <title>セッション変更完了</title>
    <style>
            .confirm-container {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                max-width: 900px;
                width: 100%;
                box-sizing: border-box;
                margin: 0 auto;
                margin-top: 20px;
                margin-bottom: 15%;
            }

            .confirm-text {
                font-size: 2em;
                margin-block-start: 0.67em;
                margin-block-end: 0.67em;
                font-weight: bold;
                color: #333;
                text-align: center;
            }

            p {
                margin: 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th,
            td {
                padding: 10px;
                border: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
                text-align: left;
            }

            td span {
                font-weight: bold;
                color: #555;
            }




            .hidden-field {
                display: none;
            }

            .field-postal_code1,
            .field-postal_code2,
            .field-phone1,
            .field-phone2,
            .field-phone3,
            .field-hall {
                display: none;
            }

            /* th要素からインデックス部分を非表示にするスタイル */
            th .index {
                display: none;
            }

            th {
                width: 30%;
            }

            .single-column-table th,
            .single-column-table td {
                display: block;
                width: 100%;
            }

            .single-column-table td {
                height: 50px;
                line-height: 25px;
            }

            .time-column-table th {
                width: 15%;
            }

              /*　ボタン */
      .select-btn-box {
                /* display: flex; */
                display: flex;
                flex-direction: row-reverse;
                flex-wrap: wrap;
                justify-content: space-evenly;
                margin-bottom: 20px;
                gap: 0px;
            }

            .select-btn {
                margin: 0 10px;
                width: 200px;
                padding: 12px 20px;
                text-align: center;
            }
            .select-btn a {
                color: #FFF;
            }

            .select-btn:hover{
                opacity: 0.7;
            }
    </style>
</head>

<body class="site drawer drawer--left">
    <!-- Header -->
    <header class="site-header">

        <button type="button" class="drawer-toggle drawer-hamburger">
            <span class="sr-only">toggle navigation</span>
            <span class="drawer-hamburger-icon"></span>
        </button>
        <div class="container">
            <div class="site-branding">
                <a href="/"><img src="assets/images/logo/informatica-logo.png" class="site-logo" alt="Infomatica"></a>
            </div>
        </div>
        <div class="header-right">
        </div>
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
        <div class="confirm-container">
            <h1 class="confirm-text">セッション変更完了</h1>
            <p><b>
                    <font color="red">
                        <center>※ブラウザの戻るボタンは使用しないでください。</center>
                    </font>
                </b></p><br>
                <center><p>ご登録ありがとうございました。<br>
                        この後登録いただいたアドレスに変更完了メールをお送りします。<br>
                        当日は、登録証をプリントアウトし、総合受付までお越しください。<br>
                        この度はご登録をいただきありがとうございました。<br>
                        当日のご来場を心よりお待ちいたします。</p>
                                <div class='select-btn'>
                                    <a href="mypage.php">マイページに戻る</a>
                                </div>



        </div>
</body>
</html>