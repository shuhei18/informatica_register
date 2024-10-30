<?php session_start(); ?>
<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Informatica World Tour 2024">
    <meta property="og:description" content="Informatica World Tour 2024 2024年9月13日（金）">
    <meta property="og:url" content="https://s-bev.jp/">
    <meta property="og:site_name" content="Informatica World Tour 2024">
    <meta property="og:image" content="../assets/images/ogp/iwt-ogp-image_2024.jpg">
    <meta property="og:image:width" content="1280">
    <meta property="og:image:height" content="630">
    <meta property="og:image_type" content="jpg" />


    <!-- Google Tag Manager -->

    <!-- End Google Tag Manager -->



    <title>来場者事前登録完了</title>


    <link rel="icon" type="image/png" href="../assets/images/favicon.png" />
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../assets/js/iscroll.js"></script>
    <script type="text/javascript" src="../assets/js/drawer.min.js"></script>
    <script type="text/javascript" src="../assets/js/script.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/custom.css">

    <style>
    .profileform-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        box-sizing: border-box;
        margin: 0 auto;
        margin-top: 20px;
        margin-bottom: 2%;
        text-align: center;
    }

    .profileform-text {
        font-size: 2em;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        font-weight: bold;
        color: #333;
        text-align: center;

    }

    .profileform-submit {
        background-color: #ff6a13;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
        min-width: 9em;
        width: 200px;
    }

    .button {
        background-color: #ff6a13;
        color: #fff;
    }

    .button-col {
        text-align: center;
    }


    p {
        margin: 20px 0;
    }

    .red {
        color: red;
    }

    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .button-container a {
        background-color: #FA460F;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
    }

    .button-container a:hover {
        background-color: #B24212;
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
        margin: auto 10px;
        width: 200px;
        padding: 12px 20px;
        text-align: center;
        margin: 0 auto;
    }

    .select-btn a {
        color: #FFF;
    }

    .select-btn-2 {
        margin: 0 10px;
        width: auto;
        padding: 12px 20px;
        color: #001AFF !important;
        border: 2px solid #001AFF;
        outline: 2px solid transparent;
        background: #FFF;
        text-align: center;
    }

    .select-btn-3 {
        margin: 0 10px;
        width: auto;
        padding: 12px 20px;
        color: #FFF !important;
        outline: 2px solid transparent;
        background: #001AFF;
        text-align: center;
    }

    .select-btn:hover,
    .select-btn-2:hover,
    .select-btn-3:hover {
        opacity: 0.7;
    }

    @media (max-width: 767.98px) {

        .select-btn-box {
            margin-top: 15px;
            gap: 10px;
        }
    }
    </style>
</head>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 現在のURLからクエリパラメータを取得
    const params = new URLSearchParams(window.location.search);

    // すべてのリンクを取得
    document.querySelectorAll('a').forEach(link => {
        const url = new URL(link.href, window.location.href);
        // メールや電話のリンクは除外
        if (url.protocol === 'mailto:' || url.protocol === 'tel:') return;

        // 各クエリパラメータをリンクに追加
        params.forEach((value, key) => {

            url.searchParams.set(key, value);
        });

        // 変更されたURLを設定
        link.href = url.href;
    });
});
</script>


<body class="site drawer drawer--left">

    <header class="site-header">

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
        <div class="profileform-container">
            <h1 class="profileform-text">来場者事前登録完了</h1>
            <p class='red'>*ブラウザの戻るボタンは使用しないでください。</p>
            <div class="thank-you-message">
                <?php if (isset($_SESSION['email_exists']) && $_SESSION['email_exists']) : ?>
                <p class="red">既に登録済みです。<br>お送りしたメールから受講票をご確認ください。</p>
                <?php unset($_SESSION['email_exists']); // 重複してメッセージが表示されるのを防ぐためにunset ?>
                <?php else : ?>
                <p>ご登録、ありがとうございました。<br>登録完了メールをお送りしました。</p>
                <?php endif; ?>

                <div class='select-btn'>
                    <a href='login.php'>ログインページへ</a>
                </div>
                <br>
                <a href='email_issue.html'>メールが届かない方はこちら</a>
            </div>
        </div>
</body>

</html>