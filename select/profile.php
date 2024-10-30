<?php
session_start();

// セッションの有効期限を10分に設定
$inactive = 1800; // テストとして1分（秒単位）に設定
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset(); // セッションデータをクリア
        session_destroy(); // セッションを破棄
        session_start(); // 新しいセッションを開始
        $_SESSION['timeout'] = time(); // 新しいタイマーをスタート
        // セッションが切れた場合にindex.phpに遷移
        header("Location: index.php");
        exit();
    }
} else {
    // 初回アクセス時にセッションタイムアウトをセット
    $_SESSION['timeout'] = time();
}

// セッションからクエリパラメータを取得
$utm_source = isset($_SESSION['utm_source']) ? $_SESSION['utm_source'] : '';
$utm_medium = isset($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : '';
$utm_campaign = isset($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : '';

$sessions = $_SESSION['sessions'] ?? null;

if (empty($sessions) && empty($_POST)) {
    header("Location: index.php");
    exit;
}

// POSTデータを優先して$userにマージする
foreach ($_POST as $key => $value) {
    $user[$key] = $value;
}

// 保持されたユーザー情報を取得
if (isset($_POST['user'])) {
    $user = json_decode($_POST['user'], true);
    $_SESSION['user'] = $user;
} else {
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
}

// 不要なデータを除外
unset($user['password']);
unset($user['password_confirm']);
unset($user['work_email']);
unset($user['work_email_confirm']);

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

    document.addEventListener('DOMContentLoaded', function() {
        // ユーザーのクリックを監視してセッションの状態を確認
        function checkSession() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "check_session.php", true); // セッションを確認するためのリクエスト
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.session_active === false) {
                        // セッションが切れている場合はアラートを表示してリロード
                        alert('時間が超過しました。セッションを再選択してください。');
                        window.location.href = 'index.php';
                    }
                }
            };
            xhr.send();
        }

        // クリック時にセッションを確認
        document.addEventListener('click', checkSession);
    });
    </script>
    <!-- End Google Tag Manager -->

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



    <title>お客様情報</title>


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
    }

    .profileform-text {
        font-size: 2em;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        font-weight: bold;
        color: #333;
        text-align: center;

    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #dcdcdc;
        text-align: left;
        vertical-align: middle;
        padding: 0 10px;
        border: 1px solid #fff;
        width: 270px;
    }

    td {
        padding: 0 10px;
        vertical-align: middle;
    }

    td,
    th,
    .required {
        font-size: clamp(13px, 1.2vw, 18px);
    }

    /*td input[type="text"] {
        width: 80%; 
    } */

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select,
    textarea {
        width: auto;
        padding: 10px;
        margin-top: 5px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    #surname,
    #given_name,
    #surname_kana,
    #given_name_kana {
        width: 36%;
    }

    #company_name,
    #company_name_kana,
    #department_name,
    #position_name,
    #city,
    #town,
    #street,
    #building {
        width: 88%;
    }

    #work_email,
    #work_email_confirm {
        width: 80%
    }

    #industry_category,
    #department_category,
    #position_category,
    #employees_count,
    #annual_revenue,
    #event_involvement,
    #event_involvement_other {
        width: 100%
    }

    #corporate_prefix {
        display: inline-block;
        vertical-align: middle;
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



    .error {
        color: red;
        font-size: 12px;
    }

    .note {
        font-size: 12px;
        color: #777;
    }

    .required::after {
        content: "(※)";
        color: red;
        margin-left: 2px;
        font-size: 0.7em;
    }

    /* CSS部分は既存のスタイルシートに追記してください */
    p.grey-text {
        color: grey;
        font-size: 0.9em;
    }

    .display-none {
        display: none;
    }

    .label-gray {
        background-color: #dcdcdc;
        height: 50px;
        line-height: 50px;
        padding: 0px 10px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select,
    textarea {

        font-size: clamp(13px, 1.2vw, 18px);
    }

    input[type="radio"] {
        display: inline;
    }

    @media (max-width: 767.98px) {
        th {
            width: 160px;
        }

        #company_name,
        #company_name_kana,
        #department_name,
        #position_name,
        #city,
        #town,
        #street,
        #building {
            width: 100%;
        }

        #work_email,
        #work_email_confirm {
            width: 68%;
        }

        #surname,
        #given_name,
        #surname_kana,
        #given_name_kana {
            width: 100%;
        }

        #corporate_prefix {
            display: block;
        }

        table td {
            display: inline-table;
            margin: 10px;
        }



        .label-gray {
            font-size: clamp(13px, 1.2vw, 18px);
            display: inline-table;
            width: 100%;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            margin-right: 7px;
        }

        input[name="phone1"],
        input[type="phone2"],
        input[type="phone3"],
            {
            min-width: 40px;
        }

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

<body class="site drawer drawer--left">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWVJM2V" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
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
            <h1 class="profileform-text">お客様情報</h1>
            <p><b>
                    <font color="red">
                        <center>※ブラウザの戻るボタンは使用しないでください。</center>
                    </font>
                </b></p>
            <p><b>
                    <center>下記フォームにお客様の情報を入力してください。</center>
                </b></p>
            <form id="profileForm"
                action="confirm.php?utm_source=<?php echo urlencode($utm_source); ?>&utm_medium=<?php echo urlencode($utm_medium); ?>&utm_campaign=<?php echo urlencode($utm_campaign); ?>"
                method="post">
                <?php if (!empty($sessions)): ?>
                <?php foreach ($sessions as $session) : ?>
                <?php list($time, $title, $hall) = explode(";", $session); ?>

                <div class="display-none">
                    <p>講演内容の時間帯: <span><?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?></span></p>
                    <input type="hidden" name="lecture_time[]"
                        value="<?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?>">
                    <p>講演タイトル: <span><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></span></p>
                    <input type="hidden" name="lecture_title[]"
                        value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                    <p>会場: <span><?php echo htmlspecialchars($hall, ENT_QUOTES, 'UTF-8'); ?></span></p>
                </div>
                <input type="hidden" name="hall[]" value="<?php echo htmlspecialchars($hall, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endforeach; ?>
                <?php endif; ?>



                <button type="button" style="margin-bottom: 15px;"
                    onclick="location.href='index.php?utm_source=<?php echo urlencode($utm_source); ?>&utm_medium=<?php echo urlencode($utm_medium); ?>&utm_campaign=<?php echo urlencode($utm_campaign); ?>'"
                    class="select-btn-2">セッション選択に戻る</button>
                <p><label class="required"></label>は必須項目です</p>
                <table>
                    <tr>
                        <th>お名前</th>
                        <td>
                            姓：<input type="text" id="surname" name="surname" placeholder=""
                                value="<?php echo htmlspecialchars($user['surname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                            名：<input type="text" id="given_name" name="given_name" placeholder=""
                                value="<?php echo htmlspecialchars($user['given_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required><label class="required"></label>
                            <span class="error" id="error_surname"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>お名前フリガナ</th>
                        <td>
                            セイ：<input type="text" id="surname_kana" name="surname_kana" placeholder=""
                                pattern="^[ァ-ヶー]+$"
                                value="<?php echo htmlspecialchars($user['surname_kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                            メイ：<input type="text" id="given_name_kana" name="given_name_kana" placeholder=""
                                pattern="^[ァ-ヶー]+$"
                                value="<?php echo htmlspecialchars($user['given_name_kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required><label class="required"></label>
                            <span class="error" id="error_given_name_kana"></span>
                        </td>
                    </tr>
                    <tr>
                    <tr id="corporatePrefixSection">
                        <th rowspan="2">法人名</th>
                        <td>
                            法人格：
                            <select id="corporate_type" name="corporate_type" required style="vertical-align: middle;">
                                <option value="">お選びください</option>
                                <option value="株式会社"
                                    <?php if (($user['corporate_type'] ?? '') == '株式会社') echo 'selected'; ?>>株式会社
                                </option>
                                <option value="有限会社" <?php if ($user['corporate_type'] == '有限会社') echo 'selected'; ?>>
                                    有限会社</option>
                                <option value="合名会社" <?php if ($user['corporate_type'] == '合名会社') echo 'selected'; ?>>
                                    合名会社</option>
                                <option value="合同会社" <?php if ($user['corporate_type'] == '合同会社') echo 'selected'; ?>>
                                    合同会社</option>
                                <option value="合資会社" <?php if ($user['corporate_type'] == '合資会社') echo 'selected'; ?>>
                                    合資会社</option>
                                <option value="社団法人" <?php if ($user['corporate_type'] == '社団法人') echo 'selected'; ?>>
                                    社団法人</option>
                                <option value="一般社団法人"
                                    <?php if ($user['corporate_type'] == '一般社団法人') echo 'selected'; ?>>一般社団法人</option>
                                <option value="公益社団法人"
                                    <?php if ($user['corporate_type'] == '公益社団法人') echo 'selected'; ?>>公益社団法人</option>
                                <option value="財団法人" <?php if ($user['corporate_type'] == '財団法人') echo 'selected'; ?>>
                                    財団法人</option>
                                <option value="一般財団法人"
                                    <?php if ($user['corporate_type'] == '一般財団法人') echo 'selected'; ?>>一般財団法人</option>
                                <option value="公益財団法人"
                                    <?php if ($user['corporate_type'] == '公益財団法人') echo 'selected'; ?>>公益財団法人</option>
                                <option value="学校法人" <?php if ($user['corporate_type'] == '学校法人') echo 'selected'; ?>>
                                    学校法人</option>
                                <option value="医療法人" <?php if ($user['corporate_type'] == '医療法人') echo 'selected'; ?>>
                                    医療法人</option>
                                <option value="医療法人社団"
                                    <?php if ($user['corporate_type'] == '医療法人社団') echo 'selected'; ?>>医療法人社団</option>
                                <option value="医療法人財団"
                                    <?php if ($user['corporate_type'] == '医療法人財団') echo 'selected'; ?>>医療法人財団</option>
                                <option value="社会医療法人"
                                    <?php if ($user['corporate_type'] == '社会医療法人') echo 'selected'; ?>>社会医療法人</option>
                                <option value="国立研究開発法人"
                                    <?php if ($user['corporate_type'] == '国立研究開発法人') echo 'selected'; ?>>国立研究開発法人
                                </option>
                            </select>
                            <span class="error" id="error_corporate_type"></span>

                            <div id="corporate_prefix" name="corporate_prefix">
                                <label>
                                    <input type="radio" name="corporate_prefix" value="前"
                                        <?php if (($user['corporate_prefix'] ?? '') == '前') echo 'checked'; ?>> 前
                                </label>
                                <label style="margin-left: 10px;">
                                    <input type="radio" name="corporate_prefix" value="後"
                                        <?php if (($user['corporate_prefix'] ?? '') == '後') echo 'checked'; ?>> 後
                                </label>
                                <label style="margin-left: 10px;">
                                    <input type="radio" name="corporate_prefix" value="なし"
                                        <?php if (($user['corporate_prefix'] ?? '') == 'なし') echo 'checked'; ?>> なし
                                </label>
                            </div>
                            <label class="required"></label>
                            <span class="error" id="error_corporate_prefix"></span>
                        </td>

                    <tr>
                        <td>
                            <input type="text" id="company_name" name="company_name"
                                value="<?php echo htmlspecialchars($user['company_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                            <label class="required"></label>
                            <span class="note">※正式名称を全角でご記入ください</span>
                            <span class="error" id="error_company_name"></span>
                        </td>
                    </tr>
                    </tr>
                    <tr>
                        <th><label for="company_name_kana">法人名フリガナ</label></th>
                        <td>
                            <input type="text" id="company_name_kana" name="company_name_kana" pattern="^[ァ-ヶー]+$"
                                value="<?php echo htmlspecialchars($user['company_name_kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required pattern="[\u30A0-\u30FF\s]+">
                            <label class="required"></label>
                            <span class="note">※正式名称を全角カタカナでご記入ください（ ・は入力不要です。）</span>
                            <span class="error" id="error_company_name_kana"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>部署名</th>
                        <td>
                            <input type="text" id="department_name" name="department_name"
                                value="<?php echo htmlspecialchars($user['department_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                            <label class="required"></label>
                            <span class="note">※全角でご記入ください（無い場合は「ナシ」とご記入ください。）</span>

                            <span class="error" id="error_department_name"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>役職名</th>
                        <td>
                            <input type="text" id="position_name" name="position_name"
                                value="<?php echo htmlspecialchars($user['position_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                            <label class="required"></label>
                            <span class="note">※全角でご記入ください（無い場合は「ナシ」とご記入ください。）</span>
                            <span class="error" id="error_position_name"></span>
                        </td>
                    </tr>
                    <!-- 郵便番号入力フィールド -->
                    <table>
                        <tr>
                            <th rowspan="6">勤務先住所</th>
                            <td>
                                郵便番号：
                            </td>
                            <td>
                                <input type="text" id="postal_code1" name="postal_code1" size="3" placeholder=""
                                    maxlength="3" pattern="\d{3}" required
                                    value="<?php echo htmlspecialchars($user['postal_code1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                -
                                <input type="text" id="postal_code2" name="postal_code2" size="4" placeholder=""
                                    maxlength="4" pattern="\d{4}" required
                                    value="<?php echo htmlspecialchars($user['postal_code2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="button" class="select-btn-3" onclick="searchAddress()">住所検索</button>
                                <label class="required"></label>
                                <span class="error" id="error_postal_code"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>勤務先都道府県：</td>
                            <td>

                                <select id="prefecture" name="prefecture" required>
                                    <option value="">お選びください</option>
                                    <option value="北海道" <?php if ($user['prefecture'] == '北海道') echo 'selected'; ?>>北海道
                                    </option>
                                    <option value="青森県" <?php if ($user['prefecture'] == '青森県') echo 'selected'; ?>>青森県
                                    </option>
                                    <option value="岩手県" <?php if ($user['prefecture'] == '岩手県') echo 'selected'; ?>>岩手県
                                    </option>
                                    <option value="宮城県" <?php if ($user['prefecture'] == '宮城県') echo 'selected'; ?>>宮城県
                                    </option>
                                    <option value="秋田県" <?php if ($user['prefecture'] == '秋田県') echo 'selected'; ?>>秋田県
                                    </option>
                                    <option value="山形県" <?php if ($user['prefecture'] == '山形県') echo 'selected'; ?>>山形県
                                    </option>
                                    <option value="福島県" <?php if ($user['prefecture'] == '福島県') echo 'selected'; ?>>福島県
                                    </option>
                                    <option value="茨城県" <?php if ($user['prefecture'] == '茨城県') echo 'selected'; ?>>茨城県
                                    </option>
                                    <option value="栃木県" <?php if ($user['prefecture'] == '栃木県') echo 'selected'; ?>>栃木県
                                    </option>
                                    <option value="群馬県" <?php if ($user['prefecture'] == '群馬県') echo 'selected'; ?>>群馬県
                                    </option>
                                    <option value="埼玉県" <?php if ($user['prefecture'] == '埼玉県') echo 'selected'; ?>>埼玉県
                                    </option>
                                    <option value="千葉県" <?php if ($user['prefecture'] == '千葉県') echo 'selected'; ?>>千葉県
                                    </option>
                                    <option value="東京都" <?php if ($user['prefecture'] == '東京都') echo 'selected'; ?>>東京都
                                    </option>
                                    <option value="神奈川県" <?php if ($user['prefecture'] == '神奈川県') echo 'selected'; ?>>
                                        神奈川県</option>
                                    <option value="新潟県" <?php if ($user['prefecture'] == '新潟県') echo 'selected'; ?>>新潟県
                                    </option>
                                    <option value="富山県" <?php if ($user['prefecture'] == '富山県') echo 'selected'; ?>>富山県
                                    </option>
                                    <option value="石川県" <?php if ($user['prefecture'] == '石川県') echo 'selected'; ?>>石川県
                                    </option>
                                    <option value="福井県" <?php if ($user['prefecture'] == '福井県') echo 'selected'; ?>>福井県
                                    </option>
                                    <option value="山梨県" <?php if ($user['prefecture'] == '山梨県') echo 'selected'; ?>>山梨県
                                    </option>
                                    <option value="長野県" <?php if ($user['prefecture'] == '長野県') echo 'selected'; ?>>長野県
                                    </option>
                                    <option value="岐阜県" <?php if ($user['prefecture'] == '岐阜県') echo 'selected'; ?>>岐阜県
                                    </option>
                                    <option value="静岡県" <?php if ($user['prefecture'] == '静岡県') echo 'selected'; ?>>静岡県
                                    </option>
                                    <option value="愛知県" <?php if ($user['prefecture'] == '愛知県') echo 'selected'; ?>>愛知県
                                    </option>
                                    <option value="三重県" <?php if ($user['prefecture'] == '三重県') echo 'selected'; ?>>三重県
                                    </option>
                                    <option value="滋賀県" <?php if ($user['prefecture'] == '滋賀県') echo 'selected'; ?>>滋賀県
                                    </option>
                                    <option value="京都府" <?php if ($user['prefecture'] == '京都府') echo 'selected'; ?>>京都府
                                    </option>
                                    <option value="大阪府" <?php if ($user['prefecture'] == '大阪府') echo 'selected'; ?>>大阪府
                                    </option>
                                    <option value="兵庫県" <?php if ($user['prefecture'] == '兵庫県') echo 'selected'; ?>>兵庫県
                                    </option>
                                    <option value="奈良県" <?php if ($user['prefecture'] == '奈良県') echo 'selected'; ?>>奈良県
                                    </option>
                                    <option value="和歌山県" <?php if ($user['prefecture'] == '和歌山県') echo 'selected'; ?>>
                                        和歌山県</option>
                                    <option value="鳥取県" <?php if ($user['prefecture'] == '鳥取県') echo 'selected'; ?>>鳥取県
                                    </option>
                                    <option value="島根県" <?php if ($user['prefecture'] == '島根県') echo 'selected'; ?>>島根県
                                    </option>
                                    <option value="岡山県" <?php if ($user['prefecture'] == '岡山県') echo 'selected'; ?>>岡山県
                                    </option>
                                    <option value="広島県" <?php if ($user['prefecture'] == '広島県') echo 'selected'; ?>>広島県
                                    </option>
                                    <option value="山口県" <?php if ($user['prefecture'] == '山口県') echo 'selected'; ?>>山口県
                                    </option>
                                    <option value="徳島県" <?php if ($user['prefecture'] == '徳島県') echo 'selected'; ?>>徳島県
                                    </option>
                                    <option value="香川県" <?php if ($user['prefecture'] == '香川県') echo 'selected'; ?>>香川県
                                    </option>
                                    <option value="愛媛県" <?php if ($user['prefecture'] == '愛媛県') echo 'selected'; ?>>愛媛県
                                    </option>
                                    <option value="高知県" <?php if ($user['prefecture'] == '高知県') echo 'selected'; ?>>高知県
                                    </option>
                                    <option value="福岡県" <?php if ($user['prefecture'] == '福岡県') echo 'selected'; ?>>福岡県
                                    </option>
                                    <option value="佐賀県" <?php if ($user['prefecture'] == '佐賀県') echo 'selected'; ?>>佐賀県
                                    </option>
                                    <option value="長崎県" <?php if ($user['prefecture'] == '長崎県') echo 'selected'; ?>>長崎県
                                    </option>
                                    <option value="熊本県" <?php if ($user['prefecture'] == '熊本県') echo 'selected'; ?>>熊本県
                                    </option>
                                    <option value="大分県" <?php if ($user['prefecture'] == '大分県') echo 'selected'; ?>>大分県
                                    </option>
                                    <option value="宮崎県" <?php if ($user['prefecture'] == '宮崎県') echo 'selected'; ?>>宮崎県
                                    </option>
                                    <option value="鹿児島県" <?php if ($user['prefecture'] == '鹿児島県') echo 'selected'; ?>>
                                        鹿児島県</option>
                                    <option value="沖縄県" <?php if ($user['prefecture'] == '沖縄県') echo 'selected'; ?>>沖縄県
                                    </option>
                                </select>
                                <label class="required"></label>
                            </td>
                        </tr>
                        <tr>
                            <td>市区郡名：</td>
                            <td>
                                <input type="text" id="city" name="city" placeholder="市区郡名" required
                                    value="<?php echo htmlspecialchars($user['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label class="required"></label>
                            </td>
                        </tr>
                        <tr>
                            <td>町村名：</td>
                            <td>
                                <input type="text" id="town" name="town" placeholder="町村名" required
                                    value="<?php echo htmlspecialchars($user['town'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label class="required"></label>
                            </td>
                        </tr>
                        <tr>
                            <td>丁目番地：</td>
                            <td>
                                <input type="text" id="street" name="street" placeholder="丁目番地" required
                                    value="<?php echo htmlspecialchars($user['street'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label class="required"></label>
                            </td>
                        </tr>
                        <tr>
                            <td>建物名：</td>
                            <td>
                                <input type="text" id="building" name="building" placeholder="建物名"
                                    value="<?php echo htmlspecialchars($user['building'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                        </tr>
                    </table>
                    <table>
                        <!-- 電話番号入力フィールド -->
                        <tr>
                            <th>電話番号</th>
                            <td>
                                <input type="text" size="6" maxlength="6" id="phone1" name="phone1" placeholder=""
                                    pattern="\d{1,4}" required
                                    value="<?php echo htmlspecialchars($user['phone1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                -
                                <input type="text" size="6" maxlength="6" id="phone2" name="phone2" placeholder=""
                                    pattern="\d{1,4}" required
                                    value="<?php echo htmlspecialchars($user['phone2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                -
                                <input type="text" size="6" maxlength="4" id="phone3" name="phone3" placeholder=""
                                    pattern="\d{4}" required
                                    value="<?php echo htmlspecialchars($user['phone3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label class="required"></label>
                                <span class="error" id="error_phone"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>勤務先メールアドレス</th>
                            <td>
                                入力：<input type="email" id="work_email" name="work_email"
                                    value="<?php echo htmlspecialchars($user['work_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    required>
                                <label class="required"></label>
                                <span class="error" id="error_work_email"></span><br>
                                確認：<input type="email" id="work_email_confirm" name="work_email_confirm" placeholder=""
                                    value="<?php echo htmlspecialchars($user['work_email_confirm'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    required>
                                <label class="required"></label>
                                <span class="error" id="error_work_email_confirm"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>パスワード<br><span class="note">半角英数字8~12文字</span></th>
                            <td>
                                入力：<input type="password" id="password" name="password"
                                    value="<?php echo htmlspecialchars($user['password'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    size="15" pattern="(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{8,12}" maxlength="12" required>
                                <label class="required"></label>
                                <span class="error" id="error_password"></span><br>
                                確認：<input type="password" id="password_confirm" name="password_confirm"
                                    pattern="(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{8,12}"
                                    value="<?php echo htmlspecialchars($user['password_confirm'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    size="15" maxlength="12" placeholder="" required>
                                <label class="required"></label>
                                <span class="error" id="error_password_confirm"></span>
                            </td>
                        </tr>

                    </table>
                </table>
                <br>
                <div class="label-gray"><label class="required">あなたの業種区分を選択してください。</label></div>

                <select id="industry_category" name="industry_category" required>
                    <option value="">お選びください</option>
                    <option value="製造業" <?php if (($user['industry_category'] ?? '') == '製造業') echo 'selected'; ?>>製造業
                    </option>
                    <option value="運輸/鉄道/航空/船舶/倉庫/商社/貿易"
                        <?php if (($user['industry_category'] ?? '') == '運輸/鉄道/航空/船舶/倉庫/商社/貿易') echo 'selected'; ?>>
                        運輸/鉄道/航空/船舶/倉庫/商社/貿易</option>
                    <option value="小売/卸業" <?php if (($user['industry_category'] ?? '') == '小売/卸業') echo 'selected'; ?>>
                        小売/卸業</option>
                    <option value="建設/建築/不動産/プラント/化学/鉄鋼/電気機器"
                        <?php if (($user['industry_category'] ?? '') == '建設/建築/不動産/プラント/化学/鉄鋼/電気機器') echo 'selected'; ?>>
                        建設/建築/不動産/プラント/化学/鉄鋼/電気機器</option>
                    <option value="通信/電話" <?php if (($user['industry_category'] ?? '') == '通信/電話') echo 'selected'; ?>>
                        通信/電話</option>
                    <option value="自動車" <?php if (($user['industry_category'] ?? '') == '自動車') echo 'selected'; ?>>
                        自動車
                    </option>
                    <option value="非営利団体/官公庁/政府機関"
                        <?php if (($user['industry_category'] ?? '') == '非営利団体/官公庁/政府機関') echo 'selected'; ?>>
                        非営利団体/官公庁/政府機関</option>
                    <option value="銀行/信託銀行/信用金庫"
                        <?php if (($user['industry_category'] ?? '') == '銀行/信託銀行/信用金庫') echo 'selected'; ?>>
                        銀行/信託銀行/信用金庫
                    </option>
                    <option value="証券/リース/クレジットカード/金融業全般"
                        <?php if (($user['industry_category'] ?? '') == '証券/リース/クレジットカード/金融業全般') echo 'selected'; ?>>
                        証券/リース/クレジットカード/金融業全般</option>
                    <option value="メディア/エンターテインメント"
                        <?php if (($user['industry_category'] ?? '') == 'メディア/エンターテインメント') echo 'selected'; ?>>
                        メディア/エンターテインメント</option>
                    <option value="生命保険/損害保険"
                        <?php if (($user['industry_category'] ?? '') == '生命保険/損害保険') echo 'selected'; ?>>
                        生命保険/損害保険
                    </option>
                    <option value="医薬品" <?php if (($user['industry_category'] ?? '') == '医薬品') echo 'selected'; ?>>
                        医薬品
                    </option>
                    <option value="消費財" <?php if (($user['industry_category'] ?? '') == '消費財') echo 'selected'; ?>>
                        消費財
                    </option>
                    <option value="電力/水道/ガス"
                        <?php if (($user['industry_category'] ?? '') == '電力/水道/ガス') echo 'selected'; ?>>
                        電力/水道/ガス
                    </option>
                    <option value="ホテル/旅行/サービス/人材サービス"
                        <?php if (($user['industry_category'] ?? '') == 'ホテル/旅行/サービス/人材サービス') echo 'selected'; ?>>
                        ホテル/旅行/サービス/人材サービス</option>
                    <option value="ヘルスケア" <?php if (($user['industry_category'] ?? '') == 'ヘルスケア') echo 'selected'; ?>>
                        ヘルスケア</option>
                    <option value="航空宇宙/防衛"
                        <?php if (($user['industry_category'] ?? '') == '航空宇宙/防衛') echo 'selected'; ?>>
                        航空宇宙/防衛
                    </option>
                    <option value="鉱業/石油/防衛"
                        <?php if (($user['industry_category'] ?? '') == '鉱業/石油/防衛') echo 'selected'; ?>>
                        鉱業/石油/防衛
                    </option>
                    <option value="大学/大学院/教育機関/リサーチ/調査"
                        <?php if (($user['industry_category'] ?? '') == '大学/大学院/教育機関/リサーチ/調査') echo 'selected'; ?>>
                        大学/大学院/教育機関/リサーチ/調査</option>
                    <option value="システムインテグレーター/ITコンサルティング"
                        <?php if (($user['industry_category'] ?? '') == 'システムインテグレーター/ITコンサルティング') echo 'selected'; ?>>
                        システムインテグレーター/ITコンサルティング</option>
                    <option value="ベンダー(ハード/ソフト）"
                        <?php if (($user['industry_category'] ?? '') == 'ベンダー(ハード/ソフト）') echo 'selected'; ?>>
                        ベンダー(ハード/ソフト）</option>
                    <option value="監査法人/シンクタンク/コンサルティングファーム"
                        <?php if (($user['industry_category'] ?? '') == '監査法人/シンクタンク/コンサルティングファーム') echo 'selected'; ?>>
                        監査法人/シンクタンク/コンサルティングファーム</option>
                    <option value="ハイテクノロジー"
                        <?php if (($user['industry_category'] ?? '') == 'ハイテクノロジー') echo 'selected'; ?>>
                        ハイテクノロジー
                    </option>
                    <option value="その他" <?php if (($user['industry_category'] ?? '') == 'その他') echo 'selected'; ?>>
                        その他
                    </option>
                </select>
                <span class="error" id="error_industry_category"></span>



                <div class="label-gray"><label class="required">あなたの部署カテゴリーを選択してください。</label></div>

                <select id="department_category" name="department_category" required>
                    <option value="">お選びください</option>
                    <option value="情報システム アプリケーション"
                        <?php if (($user['department_category'] ?? '') == '情報システム アプリケーション') echo 'selected'; ?>>
                        情報システム
                        アプリケーション</option>
                    <option value="情報システム その他部門"
                        <?php if (($user['department_category'] ?? '') == '情報システム その他部門') echo 'selected'; ?>>
                        情報システム
                        その他部門</option>
                    <option value="情報システム データウェアハウス/データ統合"
                        <?php if (($user['department_category'] ?? '') == '情報システム データウェアハウス/データ統合') echo 'selected'; ?>>
                        情報システム データウェアハウス/データ統合</option>
                    <option value="情報システム アーキテクチャ"
                        <?php if (($user['department_category'] ?? '') == '情報システム アーキテクチャ') echo 'selected'; ?>>
                        情報システム
                        アーキテクチャ</option>
                    <option value="情報システム 役員"
                        <?php if (($user['department_category'] ?? '') == '情報システム 役員') echo 'selected'; ?>>
                        情報システム 役員
                    </option>
                    <option value="情報システム セキュリティ"
                        <?php if (($user['department_category'] ?? '') == '情報システム セキュリティ') echo 'selected'; ?>>
                        情報システム
                        セキュリティ</option>
                    <option value="営業 / プリセールス"
                        <?php if (($user['department_category'] ?? '') == '営業 / プリセールス') echo 'selected'; ?>>
                        営業 /
                        プリセールス
                    </option>
                    <option value="研究 / 開発"
                        <?php if (($user['department_category'] ?? '') == '研究 / 開発') echo 'selected'; ?>>研究
                        / 開発
                    </option>
                    <option value="マーケティング"
                        <?php if (($user['department_category'] ?? '') == 'マーケティング') echo 'selected'; ?>>
                        マーケティング
                    </option>
                    <option value="製造" <?php if (($user['department_category'] ?? '') == '製造') echo 'selected'; ?>>
                        製造
                    </option>
                    <option value="オペレーション"
                        <?php if (($user['department_category'] ?? '') == 'オペレーション') echo 'selected'; ?>>
                        オペレーション
                    </option>
                    <option value="調達 / 購買"
                        <?php if (($user['department_category'] ?? '') == '調達 / 購買') echo 'selected'; ?>>調達
                        / 購買
                    </option>
                    <option value="カスタマーサービス"
                        <?php if (($user['department_category'] ?? '') == 'カスタマーサービス') echo 'selected'; ?>>
                        カスタマーサービス
                    </option>
                    <option value="人事 /教育"
                        <?php if (($user['department_category'] ?? '') == '人事 /教育') echo 'selected'; ?>>人事
                        /教育
                    </option>
                    <option value="財務" <?php if (($user['department_category'] ?? '') == '財務') echo 'selected'; ?>>
                        財務
                    </option>
                    <option value="その他部門"
                        <?php if (($user['department_category'] ?? '') == 'その他部門') echo 'selected'; ?>>その他部門
                    </option>
                </select>
                <span class="error" id="error_department_category"></span>



                <div class="label-gray"><label class="required">あなたの役職区分を選択してください。</label></div>

                <select id="position_category" name="position_category" required>
                    <option value="">お選びください</option>
                    <option value="役員" <?php if (($user['position_category'] ?? '') == '役員') echo 'selected'; ?>>役員
                    </option>
                    <option value="部長 / 本部長"
                        <?php if (($user['position_category'] ?? '') == '部長 / 本部長') echo 'selected'; ?>>部長 /
                        本部長
                    </option>
                    <option value="課長 / 係長"
                        <?php if (($user['position_category'] ?? '') == '課長 / 係長') echo 'selected'; ?>>課長 /
                        係長
                    </option>
                    <option value="その他役職" <?php if (($user['position_category'] ?? '') == 'その他役職') echo 'selected'; ?>>
                        その他役職</option>
                    <option value="開発エンジニア"
                        <?php if (($user['position_category'] ?? '') == '開発エンジニア') echo 'selected'; ?>>
                        開発エンジニア
                    </option>
                    <option value="プロジェクトマネージャー"
                        <?php if (($user['position_category'] ?? '') == 'プロジェクトマネージャー') echo 'selected'; ?>>
                        プロジェクトマネージャー
                    </option>
                    <option value="アーキテクト"
                        <?php if (($user['position_category'] ?? '') == 'アーキテクト') echo 'selected'; ?>>アーキテクト
                    </option>
                    <option value="アナリスト" <?php if (($user['position_category'] ?? '') == 'アナリスト') echo 'selected'; ?>>
                        アナリスト</option>
                </select>
                <span class="error" id="error_position_category"></span>

                <div class="label-gray"><label class="required">あなたのお勤め先の従業員数は、次のうちどのくらいですか？</label></div>

                <select id="employees_count" name="employees_count" required>
                    <option value="">お選びください</option>
                    <option value="25,000名以上"
                        <?php if (($user['employees_count'] ?? '') == '25,000名以上') echo 'selected'; ?>>
                        25,000名以上
                    </option>
                    <option value="5,000名～24,999名"
                        <?php if (($user['employees_count'] ?? '') == '5,000名～24,999名') echo 'selected'; ?>>
                        5,000名～24,999名</option>
                    <option value="1,000名～4,999名"
                        <?php if (($user['employees_count'] ?? '') == '1,000名～4,999名') echo 'selected'; ?>>
                        1,000名～4,999名
                    </option>
                    <option value="500名～999名"
                        <?php if (($user['employees_count'] ?? '') == '500名～999名') echo 'selected'; ?>>
                        500名～999名
                    </option>
                    <option value="100名～499名"
                        <?php if (($user['employees_count'] ?? '') == '100名～499名') echo 'selected'; ?>>
                        100名～499名
                    </option>
                    <option value="100名未満" <?php if (($user['employees_count'] ?? '') == '100名未満') echo 'selected'; ?>>
                        100名未満</option>
                    <option value="不明" <?php if (($user['employees_count'] ?? '') == '不明') echo 'selected'; ?>>
                        不明
                    </option>
                </select>
                <span class="error" id="error_employees_count"></span>


                <div class="label-gray"><label class="required">あなたのお勤め先の年商規模は、次のうちどのくらいですか？</label></div>

                <select id="annual_revenue" name="annual_revenue" required>
                    <option value="">お選びください</option>
                    <option value="1兆円以上" <?php if (($user['annual_revenue'] ?? '') == '1兆円以上') echo 'selected'; ?>>
                        1兆円以上</option>
                    <option value="1,000億円～1兆円未満"
                        <?php if (($user['annual_revenue'] ?? '') == '1,000億円～1兆円未満') echo 'selected'; ?>>
                        1,000億円～1兆円未満
                    </option>
                    <option value="300億円～1,000億円未満"
                        <?php if (($user['annual_revenue'] ?? '') == '300億円～1,000億円未満') echo 'selected'; ?>>
                        300億円～1,000億円未満</option>
                    <option value="100億円～300億円未満"
                        <?php if (($user['annual_revenue'] ?? '') == '100億円～300億円未満') echo 'selected'; ?>>
                        100億円～300億円未満
                    </option>
                    <option value="100億円未満" <?php if (($user['annual_revenue'] ?? '') == '100億円未満') echo 'selected'; ?>>
                        100億円未満</option>
                    <option value="不明" <?php if (($user['annual_revenue'] ?? '') == '不明') echo 'selected'; ?>>
                        不明
                    </option>
                </select>
                <span class="error" id="error_annual_revenue"></span>


                <div class="label-gray"><label class="required">あなたは本イベントに関連する製品/サービス導入に、主にどのように関与されていますか？</label></div>

                <select id="event_involvement" name="event_involvement" required>
                    <option value="">お選びください</option>
                    <option value="導入についての決定権のメンバー"
                        <?php if (($user['event_involvement'] ?? '') == '導入についての決定権のメンバー') echo 'selected'; ?>>
                        導入についての決定権のメンバー</option>
                    <option value="導入についての製品決定権のメンバー"
                        <?php if (($user['event_involvement'] ?? '') == '導入についての製品決定権のメンバー') echo 'selected'; ?>>
                        導入についての製品決定権のメンバー</option>
                    <option value="導入についてのアドバイスメンバー"
                        <?php if (($user['event_involvement'] ?? '') == '導入についてのアドバイスメンバー') echo 'selected'; ?>>
                        導入についてのアドバイスメンバー</option>
                    <option value="他者への提案を行うSI・リセラー"
                        <?php if (($user['event_involvement'] ?? '') == '他者への提案を行うSI・リセラー') echo 'selected'; ?>>
                        他者への提案を行うSI・リセラー</option>
                    <option value="特に導入には関与していない立場"
                        <?php if (($user['event_involvement'] ?? '') == '特に導入には関与していない立場') echo 'selected'; ?>>
                        特に導入には関与していない立場</option>
                    <option value="その他" <?php if (($user['event_involvement'] ?? '') == 'その他') echo 'selected'; ?>>
                        その他
                    </option>
                </select>
                <span class="error" id="error_event_involvement"></span>



                <div class="label-gray">本イベントに関連する製品/サービス導入関与に「その他」を選択された方はご記入ください。</div>

                <textarea id="event_involvement_other" name="event_involvement_other" rows="1"
                    cols="50"><?php echo htmlspecialchars($user['event_involvement_other'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                <!--   <div class="button-col"><button type="submit" class="profileform-submit">確認画面へ</button></div> -->

                <!-- utm_campaign の値を送信 -->
                <input type="hidden" name="parameter"
                    value="<?php echo htmlspecialchars($parameter, ENT_QUOTES, 'UTF-8'); ?>">
                <!-- セッション選択に戻るボタンを追加 -->
                <div class="button-col">

                    <button type="submit" class="select-btn">確認画面へ</button>
                </div>

            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let emailValidationError = true; // メールアドレスにエラーがあるかどうか
            let emailExistsError = false; // メールアドレスが既に存在するエラーかどうか

            // バリデーション関数
            function validateKatakana(input) {
                const regex = /^[ァ-ヶー　]*$/;
                if (!regex.test(input.value)) {
                    input.setCustomValidity("全角カタカナで入力してください");
                } else {
                    input.setCustomValidity("");
                }
            }

            document.getElementById('surname_kana').addEventListener('input', function() {
                validateKatakana(this);
            });

            document.getElementById('given_name_kana').addEventListener('input', function() {
                validateKatakana(this);
            });

            document.getElementById('company_name_kana').addEventListener('input', function() {
                validateKatakana(this);
            });

            // corporate_prefixの選択チェック関数
            function validateCorporatePrefix() {
                const prefixElements = document.getElementsByName('corporate_prefix');
                let isSelected = false;

                for (let i = 0; i < prefixElements.length; i++) {
                    if (prefixElements[i].checked) {
                        isSelected = true;
                        break;
                    }
                }

                if (!isSelected) {
                    document.getElementById('error_corporate_prefix').textContent =
                        "「法人格」の「前」「後」「なし」が選択されていません。";
                    document.getElementById('error_corporate_prefix').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                } else {
                    document.getElementById('error_corporate_prefix').textContent = "";
                    return true;
                }
            }

            // 住所検索関数
            function searchAddress() {
                const postalCode1 = document.getElementById('postal_code1').value;
                const postalCode2 = document.getElementById('postal_code2').value;
                const postalCode = postalCode1 + postalCode2;
                const postalCodeError = document.getElementById('error_postal_code');

                if (postalCode.length === 7) {
                    fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.results && data.results.length > 0) {
                                const result = data.results[0];
                                document.getElementById('prefecture').value = result.address1;
                                document.getElementById('city').value = result.address2;
                                document.getElementById('town').value = result.address3;
                                postalCodeError.textContent = "";
                            } else {
                                postalCodeError.textContent =
                                    "自動検索出来ない郵便番号、もしくは個別番号の可能性があります。お手数ですが、住所を手動で入力してください。";
                                postalCodeError.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        })
                        .catch(error => {
                            alert('住所検索中にエラーが発生しました。');
                        });
                } else {
                    postalCodeError.textContent = "正しい郵便番号を入力してください。";
                    postalCodeError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            // 住所のバリデーション関数
            function validateAddress() {
                const postalCode1 = document.getElementById('postal_code1').value;
                const postalCode2 = document.getElementById('postal_code2').value;
                const postalCode = postalCode1 + postalCode2;
                const prefecture = document.getElementById('prefecture').value;
                const city = document.getElementById('city').value;
                const town = document.getElementById('town').value;
                const postalCodeError = document.getElementById('error_postal_code');

                return fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            const result = data.results[0];
                            if (
                                result.address1 === prefecture &&
                                result.address2 === city &&
                                result.address3 === town
                            ) {
                                postalCodeError.textContent = "";
                                return true;
                            } else {
                                postalCodeError.textContent = "郵便番号と住所が相違しています。正しい住所を入力してください。";
                                postalCodeError.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                return false;
                            }
                        } else {
                            postalCodeError.textContent =
                                "自動検索出来ない郵便番号、もしくは個別番号の可能性があります。お手数ですが、住所を手動で入力してください。";
                            postalCodeError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            return true; // 個別番号の場合は手動入力を許可するためにtrueを返す
                        }
                    })
                    .catch(error => {
                        alert('住所検索中にエラーが発生しました。');
                        return false;
                    });
            }

            // メールアドレスのバリデーション関数
            async function validateEmail() {
                const email = document.getElementById('work_email').value;
                const emailError = document.getElementById('error_work_email');

                if (email === "") {
                    emailError.textContent = "";
                    emailValidationError = false;
                    emailExistsError = false;
                    return;
                }

                const restrictedEmails = ["webzo@outlook.com", "nakatasakae@yahoo.co.jp",
                    "nebulamain@gmail.com"
                ];
                const restrictedDomains = [
                    "bizreach.co.jp", "raynos.co.jp", "ewc.co.jp", "search-firm.co.jp", "kandc.com",
                    "pro-bank.co.jp", "aims-japan.com", "mri-tmg.jp", "careercarver.jp", "dmk.co.jp",
                    "asteria.com", "appresso.com", "ashisuto.co.jp", "terrasky.co.jp", "talend.com",
                    "saison.co.jp", "syncsort.com", "sas.com", "yahoo.co.jp", "hotmail.com",
                    "yandex.ru", "aol.jp", "icloud.com", "mail.com", "docomo.ne.jp",
                    "ezweb.ne.jp", "au.com", "softbank.ne.jp", "softbank.jp", "vodafone.ne.jp",
                    "pdx.ne.jp", "disney.ne.jp", "y-mobile.ne.jp", "willcom.com", "gmail",
                    "i.softbank.jp",
                    "saison-technology.com",
                    "alation.com", "sis2.saison.co.jp", "allcareer.co.jp", "precisely.com"
                ];
                const emailDomain = email.split('@').pop();

                if (restrictedEmails.includes(email) || restrictedDomains.includes(emailDomain)) {
                    emailError.textContent = "競合企業にお勤めの方、個人の方のお申し込みはお断りしております。";
                    emailError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    emailValidationError = true;
                    emailExistsError = false;
                } else {
                    try {
                        const response = await fetch(`validate_email.php?email=${email}`);
                        const data = await response.json();
                        if (data.exists) {
                            emailError.textContent = "既に登録済みのメールアドレスです。";
                            emailError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            emailValidationError = true;
                            emailExistsError = true;
                        } else {
                            emailError.textContent = "";
                            emailValidationError = false;
                            emailExistsError = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        emailError.textContent = "メールアドレスの検証中にエラーが発生しました。";
                        emailError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        emailValidationError = true;
                        emailExistsError = true;
                    }
                }
            }

            // メールアドレスの一致チェック関数
            function validateEmailMatch() {
                const email = document.getElementById('work_email').value;
                const confirmEmail = document.getElementById('work_email_confirm').value;
                const emailConfirmError = document.getElementById('error_work_email_confirm');

                if (email !== confirmEmail) {
                    emailConfirmError.textContent = "メールアドレスが一致しません。";
                    emailConfirmError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                } else {
                    emailConfirmError.textContent = "";
                    return true;
                }
            }

            // メールアドレスのバリデーション関数
            function validateEmailFormal() {
                const email = document.getElementById('work_email').value;
                const emailError = document.getElementById('error_work_email');
                if (email === "") {
                    emailError.textContent = "";
                    return true;
                }

                const emailPattern =
                    /^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/;
                if (!emailPattern.test(email)) {
                    emailError.textContent = "正式なメールアドレスを入力してください";
                    emailError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                } else {
                    emailError.textContent = "";
                    return true;
                }
            }

            // パスワードの一致チェック関数
            function validatePasswordMatch() {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('password_confirm').value;
                const passwordConfirmError = document.getElementById('error_password_confirm');

                if (password !== confirmPassword) {
                    passwordConfirmError.textContent = "パスワードが一致しません。";
                    passwordConfirmError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                } else {
                    passwordConfirmError.textContent = "";
                    return true;
                }
            }

            // フォーム送信時にチェック関数を呼び出す
            document.getElementById('profileForm').addEventListener('submit', async function(event) {
                event.preventDefault();

                // 非同期処理を含むバリデーション
                await validateEmail();

                if (emailValidationError || emailExistsError) {
                    if (emailExistsError) {
                        const emailError = document.getElementById('error_work_email');
                        emailError.textContent = "既に登録済みのメールアドレスです。";
                        emailError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    return; // バリデーションが失敗した場合はフォーム送信を中止
                }

                // 他のバリデーションを通過した場合のみフォーム送信
                if (
                    validateCorporatePrefix() &&
                    validateEmailFormal() &&
                    validateEmailMatch() &&
                    validatePasswordMatch() &&
                    await validateAddress()
                ) {
                    this.submit();
                }
            });

            // メールアドレスの入力時にバリデーションを行う
            document.getElementById('work_email').addEventListener('input', function() {
                validateEmail();
                validateEmailMatch();
            });

            document.getElementById('work_email_confirm').addEventListener('input', validateEmailMatch);
            document.getElementById('password').addEventListener('input', validatePasswordMatch);
            document.getElementById('password_confirm').addEventListener('input', validatePasswordMatch);
            document.getElementsByName('corporate_prefix').forEach(function(element) {
                element.addEventListener('change', validateCorporatePrefix);
            });

            document.querySelector('.select-btn-3').addEventListener('click', searchAddress);

            // 郵便番号のリアルタイムバリデーション
            function validatePostalCode() {
                const postalCode1 = document.getElementById('postal_code1').value;
                const postalCode2 = document.getElementById('postal_code2').value;
                const postalCode = postalCode1 + postalCode2;

                if (postalCode.length === 7) {
                    searchAddress();
                } else {
                    document.getElementById('error_postal_code').textContent = "";
                }
            }

            document.getElementById('postal_code1').addEventListener('input', validatePostalCode);
            document.getElementById('postal_code2').addEventListener('input', validatePostalCode);
        });
        </script>










</body>

</html>