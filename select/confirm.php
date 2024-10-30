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

// トークン生成
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); // トークン生成
}

// キャッシュ無効化
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 過去の日付
header("Pragma: no-cache"); // HTTP/1.0

// セッションからクエリパラメータを取得
$utm_source = isset($_SESSION['utm_source']) ? $_SESSION['utm_source'] : '';
$utm_medium = isset($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : '';
$utm_campaign = isset($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータをセッションに保存
    $_SESSION['user'] = $_POST;
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


    <!-- Google Tag Manager -->

    <!-- End Google Tag Manager -->



    <title>ご登録内容確認</title>


    <link rel="icon" type="image/png" href="../assets/images/favicon.png" />
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../assets/js/iscroll.js"></script>
    <script type="text/javascript" src="../assets/js/drawer.min.js"></script>
    <script type="text/javascript" src="../assets/js/script.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/custom.css">
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
    }

    td span {
        font-weight: bold;
        color: #555;
    }

    .button-container {
        text-align: center;
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
    .select-btn-3:hover {
        opacity: 0.7;
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
            <h1 class="confirm-text">ご登録内容確認</h1>
            <p><b>
                    <font color="red">
                        <center>※ブラウザの戻るボタンは使用しないでください。</center>
                    </font>
                </b></p><br>
            <p><b>
                    <font color="red">まだ登録は完了しておりません。</font>
                </b></p>
            <p><b>ご入力内容をご確認のうえ「登録」ボタンをクリックしてください。</b></p>
            <form id="confirmForm"
                action="send_email.php?utm_source=<?php echo urlencode($utm_source); ?>&utm_medium=<?php echo urlencode($utm_medium); ?>&utm_campaign=<?php echo urlencode($utm_campaign); ?>"
                method="post">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <table>
                    <?php
            // Concatenate fields for display
            $surname = htmlspecialchars($_POST['surname'] ?? '', ENT_QUOTES, 'UTF-8');
            $given_name = htmlspecialchars($_POST['given_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $full_name = $surname . ' ' . $given_name;
            $surname_kana = htmlspecialchars($_POST['surname_kana'] ?? '', ENT_QUOTES, 'UTF-8');
            $given_name_kana = htmlspecialchars($_POST['given_name_kana'] ?? '', ENT_QUOTES, 'UTF-8');
            $full_name_kana = $surname_kana . ' ' . $given_name_kana;
            $corporate_prefix = htmlspecialchars($_POST['corporate_prefix'] ?? '', ENT_QUOTES, 'UTF-8');
            $corporate_type = htmlspecialchars($_POST['corporate_type'] ?? '', ENT_QUOTES, 'UTF-8');
            $company_name = htmlspecialchars($_POST['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $company_name_kana = htmlspecialchars($_POST['company_name_kana'] ?? '', ENT_QUOTES, 'UTF-8');
            if ($corporate_prefix == '前') {
                $full_company_name = $corporate_type . $company_name;
            } elseif ($corporate_prefix == '後') {
                $full_company_name = $company_name . $corporate_type;
            } else {
                $full_company_name = $company_name;
            }
            $postal_code1 = htmlspecialchars($_POST['postal_code1'] ?? '', ENT_QUOTES, 'UTF-8');
            $postal_code2 = htmlspecialchars($_POST['postal_code2'] ?? '', ENT_QUOTES, 'UTF-8');
            $postal_code = "{$postal_code1}-{$postal_code2}";
            $prefecture = htmlspecialchars($_POST['prefecture'] ?? '', ENT_QUOTES, 'UTF-8');
            $city = htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES, 'UTF-8');
            $town = htmlspecialchars($_POST['town'] ?? '', ENT_QUOTES, 'UTF-8');
            $street = htmlspecialchars($_POST['street'] ?? '', ENT_QUOTES, 'UTF-8');
            $building = htmlspecialchars($_POST['building'] ?? '', ENT_QUOTES, 'UTF-8');
            $address = "{$postal_code} {$prefecture}{$city}{$town}{$street}{$building}";
            $address_hyouji = "{$postal_code}<br>{$prefecture}{$city}{$town}{$street}<br>{$building}";

            $phone1 = htmlspecialchars($_POST['phone1'] ?? '', ENT_QUOTES, 'UTF-8');
            $phone2 = htmlspecialchars($_POST['phone2'] ?? '', ENT_QUOTES, 'UTF-8');
            $phone3 = htmlspecialchars($_POST['phone3'] ?? '', ENT_QUOTES, 'UTF-8');
            $phone = "{$phone1}-{$phone2}-{$phone3}";

            $work_email = htmlspecialchars($_POST['work_email'] ?? '', ENT_QUOTES, 'UTF-8');
            $work_email_confirm = htmlspecialchars($_POST['work_email_confirm'] ?? '', ENT_QUOTES, 'UTF-8');
            $password = htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8');
            $password_confirm = htmlspecialchars($_POST['password_confirm'] ?? '', ENT_QUOTES, 'UTF-8');
            $department_name = htmlspecialchars($_POST['department_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $position_name = htmlspecialchars($_POST['position_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $industry_category = htmlspecialchars($_POST['industry_category'] ?? '', ENT_QUOTES, 'UTF-8');
            $department_category = htmlspecialchars($_POST['department_category'] ?? '', ENT_QUOTES, 'UTF-8');
            $position_category = htmlspecialchars($_POST['position_category'] ?? '', ENT_QUOTES, 'UTF-8');
            $employees_count = htmlspecialchars($_POST['employees_count'] ?? '', ENT_QUOTES, 'UTF-8');
            $annual_revenue = htmlspecialchars($_POST['annual_revenue'] ?? '', ENT_QUOTES, 'UTF-8');
            $event_involvement = htmlspecialchars($_POST['event_involvement'] ?? '', ENT_QUOTES, 'UTF-8');
            $event_involvement_other = htmlspecialchars($_POST['event_involvement_other'] ?? '', ENT_QUOTES, 'UTF-8');
        
            echo "<tr><th>名前</th><td><span>{$full_name}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"full_name\" value=\"{$full_name}\">";
            echo "<input type=\"hidden\" name=\"surname\" value=\"{$surname}\">";
            echo "<input type=\"hidden\" name=\"given_name\" value=\"{$given_name}\">";
            echo "<tr><th>お名前フリガナ</th><td><span>{$full_name_kana}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"full_name_kana\" value=\"{$full_name_kana}\">";
            echo "<input type=\"hidden\" name=\"surname_kana\" value=\"{$surname_kana}\">";
            echo "<input type=\"hidden\" name=\"given_name_kana\" value=\"{$given_name_kana}\">";
            echo "<tr><th>法人名</th><td><span>{$full_company_name}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"full_company_name\" value=\"{$full_company_name}\">";
            echo "<input type=\"hidden\" name=\"company_name\" value=\"{$company_name}\">";
            echo "<input type=\"hidden\" name=\"corporate_type\" value=\"{$corporate_type}\">";
            echo "<input type=\"hidden\" name=\"corporate_prefix\" value=\"{$corporate_prefix}\">";
            echo "<tr><th>法人名フリガナ</th><td><span>{$company_name_kana}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"company_name_kana\" value=\"{$company_name_kana}\">";
            echo "<tr><th>部署名</th><td><span>{$department_name}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"department_name\" value=\"{$department_name}\">";
            echo "<tr><th>役職名</th><td><span>{$position_name}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"position_name\" value=\"{$position_name}\">";
            echo "<tr><th>勤務先住所</th><td><span>{$address_hyouji}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"address\" value=\"{$address}\">";
            echo "<input type=\"hidden\" name=\"postal_code1\" value=\"{$postal_code1}\">";
            echo "<input type=\"hidden\" name=\"postal_code2\" value=\"{$postal_code2}\">";
            echo "<input type=\"hidden\" name=\"prefecture\" value=\"{$prefecture}\">";
            echo "<input type=\"hidden\" name=\"city\" value=\"{$city}\">";
            echo "<input type=\"hidden\" name=\"town\" value=\"{$town}\">";
            echo "<input type=\"hidden\" name=\"street\" value=\"{$street}\">";
            echo "<input type=\"hidden\" name=\"building\" value=\"{$building}\">";
            echo "<tr><th>電話番号</th><td><span>{$phone}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"phone\" value=\"{$phone}\">";
            echo "<input type=\"hidden\" name=\"phone1\" value=\"{$phone1}\">";
            echo "<input type=\"hidden\" name=\"phone2\" value=\"{$phone2}\">";
            echo "<input type=\"hidden\" name=\"phone3\" value=\"{$phone3}\">";
            echo "<tr><th>勤務先メールアドレス</th><td><span>{$work_email}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"work_email\" value=\"{$work_email}\">";
            echo "<input type=\"hidden\" name=\"work_email_confirm\" value=\"{$work_email_confirm}\">";
  
            // Display password with masking
            $masked_password = '';
            if (strlen($password) > 3) {
                $masked_password = substr($password, 0, 2) . str_repeat('*', strlen($password) - 3) . substr($password, -1);
            } else {
                $masked_password = $password; // パスワードが3文字以下の場合はそのまま表示
            }
            
            echo "<tr><th>パスワード</th><td><span>{$masked_password}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"password\" value=\"{$password}\">";
            echo "<input type=\"hidden\" name=\"password_confirm\" value=\"{$password_confirm}\">";
            ?>
                </table>

                <table>
                    <table class="single-column-table">
                        <?php
            echo "<tr><th>あなたの業種区分を選択してください。</th><td><span>{$industry_category}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"industry_category\" value=\"{$industry_category}\">";

            echo "<tr><th>あなたの役職区分を選択してください。</th><td><span>{$department_category}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"department_category\" value=\"{$department_category}\">";

            echo "<tr><th>あなたの役職区分を選択してください。</th><td><span>{$position_category}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"position_category\" value=\"{$position_category}\">";

            echo "<tr><th>あなたのお勤め先の従業員数は、次のうちどのくらいですか？</th><td><span>{$employees_count}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"employees_count\" value=\"{$employees_count}\">";

            echo "<tr><th>あなたのお勤め先の年商規模は、次のうちどのくらいですか？</th><td><span>{$annual_revenue}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"annual_revenue\" value=\"{$annual_revenue}\">";

            echo "<tr><th>あなたは本イベントに関連する製品/サービス導入に、主にどのように関与されていますか？</th><td><span>{$event_involvement}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"event_involvement\" value=\"{$event_involvement}\">";

            echo "<tr><th>本イベントに関連する製品/サービス導入関与に「その他」を選択された方はご記入ください。</th><td><span>{$event_involvement_other}</span></td></tr>";
            echo "<input type=\"hidden\" name=\"event_involvement_other\" value=\"{$event_involvement_other}\">";
            ?>
                    </table>
                </table>
                <table>
                    <div class="title">ご登録セッション</div>
                    <table class="time-column-table">
                        <?php
            // Display and pass lecture data
            $lecture_times = $_POST['lecture_time'] ?? [];
            $lecture_titles = $_POST['lecture_title'] ?? [];
            $halls = $_POST['hall'] ?? [];
        
            foreach ($lecture_times as $index => $lecture_time) {
                $lecture_title = htmlspecialchars($lecture_titles[$index], ENT_QUOTES, 'UTF-8');
                $hall = htmlspecialchars($halls[$index], ENT_QUOTES, 'UTF-8');
            
            // 修正後の表示形式に変更
    echo "<tr class='lecture-time'>";
    echo "<th>{$lecture_time}<span class='index'>({$index})</span></th>";
    echo "<td><span>{$lecture_title}</span></td>";
    echo "</tr>";

    echo "<input type=\"hidden\" name=\"lecture_time[]\" value=\"{$lecture_time}\">";
    echo "<input type=\"hidden\" name=\"lecture_title[]\" value=\"{$lecture_title}\">";
    echo "<input type=\"hidden\" name=\"hall[]\" value=\"{$hall}\">";

        }

            // Add loginID as a hidden field
            $loginID = htmlspecialchars($_POST['loginID'] ?? '', ENT_QUOTES, 'UTF-8');
            echo "<input type=\"hidden\" name=\"loginID\" value=\"{$loginID}\">";
            ?>
        </div>
        </table>
        <p><b>
                <center>上記内容をご確認の上、『登録』 ボタンをクリックしてください。<br>
                    登録完了メールをお送りいたします。</center>
            </b></p><br>
        <div class="button-container">
            <button type="button" onclick="goToProfile()" class="select-btn-2">編集</button>
            <button type="submit" class="select-btn">登録</button>
        </div>
        </form>
    </div>

    <script>
    function goToProfile() {
        const form = document.getElementById('confirmForm');
        form.action =
            'profile.php?utm_source=<?php echo urlencode($utm_source); ?>&utm_medium=<?php echo urlencode($utm_medium); ?>&utm_campaign=<?php echo urlencode($utm_campaign); ?>';
        form.method = 'post';
        form.submit();
    }
    </script>




</body>

</html>