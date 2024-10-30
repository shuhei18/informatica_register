<?php
session_start(); // セッションの開始
require 'config.php';

// セッションIDがセットされていない場合
if (!isset($_SESSION['loginID'])) {
    echo "<p>ログインしてください。</p>";
    echo "<a href='login.php'>ログインページへ</a>";
    exit;
}
$loginID = $_SESSION['loginID'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);

    // ユーザーの基本情報を取得
    $stmt = $pdo->prepare("SELECT surname, given_name, surname_kana, given_name_kana, corporate_type, corporate_prefix, company_name, company_name_kana, department_name, position_name, postal_code, prefecture, city, town, street, building, phone, work_email, password, create_date, industry_category, department_category, position_category, employees_count, annual_revenue, event_involvement, event_involvement_other FROM users WHERE loginID = ?");
    $stmt->execute([$loginID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // 単一レコードを取得

    if (!$user) {
        echo "<p>ユーザー情報が見つかりません。</p>";
        exit;
    }

    // 法人名の前後に法人種類を付ける
    $corporate_type = htmlspecialchars($user['corporate_type'], ENT_QUOTES, 'UTF-8');
    $corporate_prefix = htmlspecialchars($user['corporate_prefix'], ENT_QUOTES, 'UTF-8');
    $company_name = htmlspecialchars($user['company_name'], ENT_QUOTES, 'UTF-8');

    if ($corporate_prefix == '前') {
        $display_company_name = $corporate_type . $company_name;
    } elseif ($corporate_prefix == '後') {
        $display_company_name = $company_name . $corporate_type;
    } else {
        $display_company_name = $company_name;
    }

    // 講演情報を取得
    $stmt = $pdo->prepare("SELECT lecture_time, lecture_title, hall FROM user_lectures WHERE loginID = ?");
    $stmt->execute([$loginID]);
    $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>データベースエラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    exit;
}

?>

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


    <title>マイページ</title>
    <style>


    .logout-container {
        display: flex;
         justify-content: flex-end;
         
    }

    .logout-button {
        background-color: #ff4c4c;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        display: inline-block;
        width: auto;
        text-align: right;
    }


    p {
        color: #555;
        font-size: 16px;
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
        width: max(2vw, 120px);
    }

    

    td span {
        font-weight: bold;
        color: #555;
    }



    .info-item {
        margin-bottom: 10px;
    }

    .info-item span {
        font-weight: bold;
    }

    .session-item {
        margin-bottom: 20px;
    }

    hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 20px 0;
    }

    button {
        display: block;
        border: none;
        padding: 10px;
        cursor: pointer;
        font-size: 16px;
                width: auto;
                padding: 12px 20px;
                color: #FFF;
                background: linear-gradient(92.34deg, #FF7D00 -1.28%, #E23400 104.91%) !important;
    }

    button:hover {
        background-color: #e65b00;
    }

    .change-button {
        background-color: #ff4c4c;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 20px;
        display: inline-block;
        width: auto;
        text-align: left;
    }

    .change-button:hover {
        background-color: #e65b00;
    }

    #qrcode {
        text-align: center;
        margin-top: 20px;
    }

    .print-instruction {
        color: red;
        font-weight: bold;
        margin-top: 20px;
        text-align: center;
    }

    .profileform-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
        margin: 0 auto;
        margin-top: 20px;
        max-width: 800px;
        width: 100%;
    }


    .profileform-text {
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        font-weight: bold;
        color: #333;
        font-size: clamp(12px, 1.7vw, 3em);
        
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

                width: auto;
                padding: 12px 20px;
                color: #FFF;
                background: linear-gradient(92.34deg, #FF7D00 -1.28%, #E23400 104.91%) !important;
            }

            .select-btn-2 {

                width: 215px;
                padding: 12px 20px;
                color: #001AFF !important;
                border: 2px solid #001AFF;
                outline: 2px solid transparent;
                background: #FFF;
                text-align: center;
            }
           .profileform-container h2 {
                margin-top:30px;
                padding: 15px 0;
                border-bottom: none;
                border-top: 1px solid #CCC;
            }

    </style>
    <script src="qrcode.min.js"></script>
</head>

<body class="site drawer drawer--left">
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWVJM2V" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->

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

        <div class="profileform-container">

<body>
    <div class="container">
    <h1 class="profileform-text">マイページ</h1>
        <div class="logout-container">
            <button class="select-btn" onclick="location.href='logout.php'">ログアウト</button>
        </div>
        <div class="info-item">名前: <span
                id="name"><?php echo htmlspecialchars($user['surname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user['given_name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="info-item">メールアドレス: <span
                id="email"><?php echo htmlspecialchars($user['work_email'], ENT_QUOTES, 'UTF-8'); ?></span></div>
        <div class="info-item">電話番号: <span
                id="phone"><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></span></div>
        <div class="info-item">法人名: <span id="company_name"><?php echo $display_company_name; ?></span></div>
        <div class="info-item">部署名: <span
                id="department_name"><?php echo htmlspecialchars($user['department_name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <h2 class="profileform-text">お申込みの講演情報</h2>
<form id="changeForm" action="change_index.php" method="post" style="display:none;">
    <button class="select-btn" style="margin: 0 0 15px 0;" type="submit">講演情報を変更する</button>
</form>
<?php if (!empty($lectures)): ?>
    <table>
    <?php foreach ($lectures as $record): ?>
        <tr class='lecture-time'>
        <th><?php echo htmlspecialchars($record['lecture_time'], ENT_QUOTES, 'UTF-8'); ?></th>
        <td><?php echo htmlspecialchars($record['lecture_title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="display: none"><?php echo htmlspecialchars($record['hall'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>講演情報はありません。</p>
<?php endif; ?>
<div class="info-item">ログインID: <span id="loginID"><?php echo htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8'); ?></span></div>


         <button onclick="downloadPDF()">受講票発行（PDFをダウンロード）</button>
        <div class="print-instruction">受講票をA4サイズ・100%で印刷し四つ折りにして会場へお持ちください</div>
    </div>
    <script>
    function generateQRCode() {
        const loginID = "<?php echo htmlspecialchars($_SESSION['loginID'], ENT_QUOTES, 'UTF-8'); ?>";
        if (!loginID) {
            alert('ログインIDがありません。');
            return;
        }
        const qrcodeContainer = document.getElementById('qrcode');
        qrcodeContainer.innerHTML = '';
        new QRCode(qrcodeContainer, {
            text: loginID,
            width: 128,
            height: 128
        });
    }

    function downloadPDF() {
        window.location.href = 'generate_pdf.php';
    }
    </script>
</body>

</html>