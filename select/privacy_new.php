<?php
session_start();
// セッションタイムアウトの時間を設定（例えば10分）
$inactive = 1800;

// 最終活動時間を更新またはセッションタイムアウトをチェック
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
$_SESSION['timeout'] = time(); // ページをロードまたはフォームが送信されるたびに更新

// セッションからクエリパラメータを取得
$utm_source = isset($_SESSION['utm_source']) ? $_SESSION['utm_source'] : '';
$utm_medium = isset($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : '';
$utm_campaign = isset($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // フォームデータの処理
    if (isset($_POST['user'])) {
        $_SESSION['user'] = json_decode($_POST['user'], true);
    }
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
    if (isset($_POST['sessions'])) {
        $_SESSION['sessions'] = json_decode($_POST['sessions'], true);
    }

    // フォームが送信されたら、profile_new.phpへリダイレクト
    header("Location: profile_new.php?utm_source=".urlencode($utm_source)."&utm_medium=".urlencode($utm_medium)."&utm_campaign=".urlencode($utm_campaign));
    exit();
}

// セッションからクエリパラメータを取得
$utm_source = isset($_SESSION['utm_source']) ? $_SESSION['utm_source'] : '';
$utm_medium = isset($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : '';
$utm_campaign = isset($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : '';

// 前のページから渡されたユーザー情報をセッションに保存
if (isset($_POST['user'])) {
    $_SESSION['user'] = json_decode($_POST['user'], true);
    }
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
?>
<!doctype html>
<html lang="ja">

<head>
    <!-- Google Tag Manager -->
    <script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-NVZDXZ2Q');
    </script>
    <!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
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

    <title>個人情報取扱</title>

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
        max-width: 900px;
        box-sizing: border-box;
        margin: 0 auto;
        margin-top: 20px;
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
        margin-bottom: 28px;
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
        flex-direction: row-reverse;
        flex-wrap: wrap;
        justify-content: space-evenly;
        margin-bottom: 20px;
        gap: 0px;
    }

    .select-btn {
        margin: 0 10px;
        width: 215px;
        padding: 12px 20px;
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
    </style>
</head>

<body class="site drawer drawer--left">
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWVJM2V" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NVZDXZ2Q" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
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
            <p><b>
                    <font color="red">
                        <center>※ブラウザの戻るボタンは使用しないでください。</center>
                    </font>
                </b></p>
            <p><b>
                    <center>下記、個人情報取り扱いの内容をご確認いただき、お客様情報の入力へお進みください。</center>
                </b></p>

            <div class="section-title">推奨ブラウザについて</div>
            <div class="section-content">
                <p>推奨ブラウザ以外でご利用の場合、正常に表示および動作しない恐れがあります。</p>
                <ul>
                    <li>Edge</li>
                    <li>Google Chrome</li>
                    <li>Safari</li>
                </ul>
            </div>

            <div class="section-title">個人情報の取り扱い</div>
            <div class="section-content">
                <p>ご登録頂きました個人情報、その他の登録情報及びアンケート回答（以下、総称して「登録情報」といいます。）は、下記リンク先にあるインフォマティカプライバシーポリシーに従って利用等を行い、製品、サービス、セール情報、プロモーション、特典、イベントに関する連絡等の情報提供の目的において、取得した個人情報を関連会社の
                    Informatica LLC、US、EMEA 及び Asia Pacific に提供いたします。</p>
                <p>なお、登録情報につきましてデータ処理及びデータ管理の目的のため、第三者に業務を委託する場合がございます。また、登録情報は、本イベントの協賛企業（以下「スポンサー」といいます。）に提供いたします。
                </p>
                <p>その後は当該スポンサーの責任において管理されます。当該スポンサーから、お客様に対し、製品・サービスに関する情報やその他お知らせ等が、電子メール、電話、郵送、ダイレクトメール等で直接ご案内される場合がございます。予めご了承ください。
                </p>
                <p>■インフォマティカ・ジャパン株式会社のプライバシーポリシー<br>
                    <a href="https://www.informatica.com/jp/privacy-policy.html"
                        target="_blank">https://www.informatica.com/jp/privacy-policy.html</a>
                </p>
                <p>■ スポンサー企業名及び各社のプライパシーポリシーは、以下になります。（会社名 50音順）</p>
                <ul>
                    <li><a href="https://www.alsi.co.jp/privacy/" target="_blank">アルプス システム インテグレーション株式会社</a></li>
                    <li><a href="https://www.ctc-g.co.jp/utility/security_policy.html"
                            target="_blank">伊藤忠テクノソリューションズ株式会社</a></li>
                    <li><a href="https://www.scsk.jp/privacy.html" target="_blank">SCSK株式会社</a></li>
                    <li><a href="https://www.nsw.co.jp/corporate/csr/privacy/index.html" target="_blank">NSW株式会社</a>
                    </li>
                    <li><a href="https://www.ntt.com/about-us/hp/privacy.html" target="_blank">NTTコミュニケーションズ株式会社</a>
                    </li>
                    <li><a href="https://www.nttdata.com/jp/ja/info/privacy_policy/" target="_blank">株式会社NTTデータ</a>
                    </li>
                    <li><a href="https://www.hitachi-systems.com/privacy/index.html" target="_blank">株式会社日立システムズ</a>
                    </li>
                </ul>
            </div>

            <form id="privacyForm"
                action="profile_new.php?utm_source=<?php echo urlencode($utm_source); ?>&utm_medium=<?php echo urlencode($utm_medium); ?>&utm_campaign=<?php echo urlencode($utm_campaign); ?>"
                method="post">
                <div class="section-title">
                    <input type="checkbox" name="agree" id="agree" required>
                    <label for="agree"
                        style="color: #fff; font-weight: 800; margin-bottom: 20px ">個人情報の取り扱いに同意します。</label>
                </div>
                <div class="bottom-button">
                    <div class="select-btn-box">
                        <button type="submit" class="select-btn">次へ</button>
                    </div>
                </div>
                <div class="bottom-space"></div>
            </form>
        </div>

        <!-- footer start -->
        <!-- footer end -->

        <div class="drawer-overlay drawer-toggle"></div>
        <script src="assets/js/countdown.js"></script>
        <script src="assets/js/scroll_color.js"></script>




</body>

</html>