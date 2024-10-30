<?php
session_start();
require 'config.php';

// ユーザーIDをセッションから取得
if (isset($_SESSION['loginID'])) {
    $loginID = $_SESSION['loginID'];
    // $loginID を使用してページの処理を行う
} else {
    // ユーザーがログインしていない場合の処理
    header('Location: login.php');
    exit;
}

// POSTメソッドでセッションが送信されている場合に処理を行う
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sessions'])) {
    $_SESSION['selected_sessions'] = $_POST['sessions'];
    debug('confirm-new.php: SESSION data set: ' . print_r($_SESSION, true));
}

// セッションから選択された予約を取得
$selectedSessions = json_decode($_SESSION['selected_sessions'], true);
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

    <title>セッション変更内容確認</title>
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
        width: max(2vw, 120px);
    }

    

    td span {
        font-weight: bold;
        color: #555;
    }

    .button-container {
        text-align: center;
    }

    button {
        background-color: #ff6a13;
        color: #fff;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        cursor: pointer;
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

            .select-btn-2 {
                margin: 0 10px;
                width: 200px;
                padding: 12px 20px;
                color: #001AFF !important;
                border: 2px solid #001AFF;
                outline: 2px solid transparent;
                background: #FFF;
                text-align: center;
            }

            .select-btn-3 {
                margin: 0 10px;
                width: 200px;
                padding: 12px 20px;
                color: #FFF !important;
                outline: 2px solid transparent;
                background: #001AFF;
                text-align: center;
            }


            .select-btn:hover, 
            .select-btn-2:hover, 
            .select-btn-3:hover{
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
                <img src="assets/images/logo/informatica-logo.png" class="site-logo" alt="Infomatica">
            </div>
        </div>
        <div class="header-right">
        </div>
    </header>

    <nav class="drawer-nav" style="touch-action: none;">
        <ul class="ul-menu"
            style="transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); transition-duration: 0ms; transform: translate(0px, 0px) translateZ(0px);">
        </ul>
    </nav>

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
            <h1 class="confirm-text">セッション変更内容確認</h1>
            <p><b>
                    <font color="red">
                        <center>※ブラウザの戻るボタンは使用しないでください。</center>
                    </font>
                </b></p><br>
            <p><b>
                    <font color="red">まだ登録は完了しておりません。</font>
                </b></p>
            <p><b>ご入力内容をご確認のうえ「登録」ボタンをクリックしてください。<br>
                    登録いただいたアドレスに登録完了メールを送信します。</b></p>




                    <form id="confirmForm" action="send_email-new.php" method="post">
    <h2>選択したセッション</h2>
    <div>
    <table>
        <?php foreach ($selectedSessions as $session): ?>
            <tr class='lecture-time'>
        <th><?php echo htmlspecialchars($session['time'], ENT_QUOTES, 'UTF-8'); ?></th>
        <td><span><?php echo htmlspecialchars($session['title'], ENT_QUOTES, 'UTF-8'); ?></span></td>
        <td style="display: none"><?php echo htmlspecialchars($session['hall'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endforeach; ?>
        </table>
    </div>
    <input type="hidden" name="loginID" value="<?php echo htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="sessions" value='<?php echo htmlspecialchars(json_encode($selectedSessions), ENT_QUOTES, 'UTF-8'); ?>'>
    <div class="button-container">
        <button type="button" class="select-btn-2" onclick="goToChangeindex()">編集</button>
        <button type="submit" class="select-btn">登録</button>
    </div>
</form>

<script>
function goToChangeindex() {
    document.getElementById('confirmForm').action = 'change_index.php';
    document.getElementById('confirmForm').method = 'post';
    document.getElementById('confirmForm').submit();
}
</script>


</body>

</html>