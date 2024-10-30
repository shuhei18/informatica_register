<?php
session_start();
require 'config.php';

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 過去の日付
header("Pragma: no-cache"); // HTTP/1.0

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

// URLからクエリパラメータを取得
if (isset($_GET['utm_source'])) {
    $_SESSION['utm_source'] = $_GET['utm_source'];
}
if (isset($_GET['utm_medium'])) {
    $_SESSION['utm_medium'] = $_GET['utm_medium'];
}
if (isset($_GET['utm_campaign'])) {
    $_SESSION['utm_campaign'] = $_GET['utm_campaign'];
}

// 保持されたユーザー情報の取得
$user = isset($_SESSION['user']) ? $_SESSION['user'] : [];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // usersテーブルに存在するユーザーと紐づいている lecture_title のセッションを抽出
    $stmt = $pdo->prepare("
        SELECT LEFT(ul.lecture_title, 8) AS short_title, ul.hall, COUNT(*) as count 
        FROM user_lectures ul
        JOIN users u ON ul.loginID = u.loginID
        GROUP BY short_title, ul.hall
    ");
    $stmt->execute();
    $lectureCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sessionStatus = [];
    foreach ($lectureCounts as $lecture) {
        $limit = ($lecture['hall'] === '1st-hall') ?  1000: 80; // メインセッションなら250、ミニセッションなら60
        $sessionStatus[$lecture['short_title']] = ($lecture['count'] >= $limit) ? 'full' : 'available';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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
                        alert('セッションが切れました。セッションを再選択してください。');
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




    <title>Informatica World Tour 2024</title>


    <link rel="icon" type="image/png" href="../assets/images/favicon.png" />
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../assets/js/iscroll.js"></script>
    <script type="text/javascript" src="../assets/js/drawer.min.js"></script>
    <script type="text/javascript" src="../assets/js/script.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/custom.css">

    <style>
    .profileform-text {
        font-size: 2em;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        font-weight: bold;
        color: #333;
        text-align: center;

    }

    .modal-description {
        margin-bottom: 20px;
    }


    .modal-title {
        margin-bottom: 0px;
    }


    .scchedule-agenda span {
        line-height: 1.2rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .kityoukouen {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    h3,
    .h3 {
        font-size: clamp(10px, 1.2vw, 18px);

    }

    .select-btn-box {
        /* display: flex; */
        display: flex;
        flex-direction: row-reverse;
        flex-wrap: wrap;
        justify-content: space-evenly;
        margin-bottom: 20px;
        gap: 10px;
    }

    .select-btn {
        margin: 0 10px;
        width: 200px;
        padding: 12px 20px;
    }

    .select-btn-2 {
        margin: 0 10px;
        width: 200px;
        padding: 12px 20px;
        color: #001AFF !important;
        border: 2px solid #001AFF;
        outline: 2px solid transparent;
        background: #FFF;
    }

    tbody {
        border: 1px solid;
    }

    td {
        vertical-align: top;
        text-align: left;
        height: 25px;
    }

    td.scchedule-time-30 {
        vertical-align: middle;
    }


    th {
        font-size: clamp(11px, 1vw, 18px);

    }

    table td {
        text-alli
    }

    td,
    th {
        /* border: 1px solid #000;*/
    }

    .modal-open {
        padding-right: 0px;
    }


    .scchedule-agenda p {

        font-size: clamp(11px, 1.1vw, 16px);
        line-height: 1.2;
    }

    .scchedule-agenda span {
        font-size: clamp(11px, 1.1vw, 16px);
        line-height: 1.2;
    }

    label {
        font-size: clamp(11px, 1.1vw, 16px);
    }

    @media (max-width: 767.98px) {
        .scchedule-agenda span {

            -webkit-line-clamp: 3;
        }

        .kityoukouen {

            -webkit-line-clamp: 3;
        }

        .scchedule-time-30 {
            font-size: clamp(9px, 1.1vw, 16px);

        }

        .scchedule-time {
            font-size: clamp(9px, 1.1vw, 16px);

        }

        table td {
            padding-right: 10px;
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
        width: auto;
        padding: 12px 20px;
        outline: 2px solid transparent;
        color: #FFF;
        background: linear-gradient(92.34deg, #FF7D00 -1.28%, #E23400 104.91%) !important;
        text-align: center;
    }

    @media (max-width: 767.98px) {

        .select-btn-box {
            margin-top: 15px;
            gap: 10px;
        }
    }

    .logout-container {
        display: flex;
        justify-content: flex-end;

    }

    .logout-button {
        background-color: #ff4c4c;
        color: #fff;
        border: none;
        padding: 8px 16px;
        cursor: pointer;
        font-size: 14px;
        display: inline-block;
        width: auto;
        text-align: right;
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

    <nav class="drawer-nav" style="touch-action: none;" style="display:none">
        <ul class="ul-menu"
            style="transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); transition-duration: 0ms; transform: translate(0px, 0px) translateZ(0px);">

        </ul>
    </nav>

    <!-- End Header-->
    <div class="infa-world-headline-img"></div>

    <div class="site-main">


        <div class="site-section section-mainvisual">




            <section class="hero">
                <div id="home" class="area_top_view">
                    <div class="container">
                        <div class="container-top">
                            <div class="container-top-box">
                                <div class="text-box">
                                    <img class="iw23-logo" src="assets/images/event-logo.png" height="120">
                                    <h1>2024年9月13日(金)｜大手町プレイス ホール＆カンファレンス</h1>
                                    <div class="infa-world-headline-a">

                                    </div>
                                </div>
                            </div>

                            <div class="container-top-box">
                                <img class="container-top-box-img" src="assets/images/iw24-main-visual.png"
                                    height="120">
                            </div>
                        </div>


                    </div>
                </div>
            </section>


            <!-- live start -->







            <!-- highlights start -->
            <div class="container">
                <div class="select">
                    <h1 class="profileform-text">セッション登録</h1>
                    <font color="red">
                        <center></center>
                    </font>

                    <p style="color: red; text-align: center;">※ブラウザの戻るボタンは使用しないでください。<br>※時間が重複しているセッションは登録することはできません。
                    </p>
                    <div class="logout-container">

                    </div>

                    <div class="select-schedule">
                        <table>
                            <!-- 30分ごと→ -->
                            <tr>

                                <th></th>
                                <th>
                                    <h3>メインセッション</h3>
                                </th>
                                <th>
                                    <h3>ミニセッション</h3>
                                </th>
                                <th>
                                    <h3>ミニセッション</h3>
                                </th>
                                <th></th>
                            </tr>
                            <tr>

                                <td class="scchedule-time-30" rowspan="2">13:30</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:30</td>
                            </tr>
                            <tr>
                                <td class="scchedule-agenda" id="select-1" rowspan="11">
                                    <label>
                                        <input type="checkbox" name="sessions[]" id="id_check_agree_1"
                                            data-time="13:30-14:25"
                                            data-title="【データが切り拓く生成 AIの未来～Everybody’s ready for AI except your data～】&#10;【データ連係基盤の統合／拡大、データマネジメントへ】"
                                            data-hall="1st-hall" onclick="check_agree_func_1()"
                                            <?php $shortTitle = mb_substr('【データが切り拓く生成 AIの未来～Everybody’s ready for AI except your data～】&#10;【データ連係基盤の統合／拡大、データマネジメントへ】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                        <label for="1"
                                            style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                            <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                        </label>
                                    </label>
                                    <div class="modal-open">
                                        <p class="schedule-time-small">13:30 - 14:25</p>


                                        <span class="schedule-section-title" id="title-1" style="color: #ff6a13;">
                                            <p class="kityoukouen" style="margin-top:0">【基調講演】</p>
                                            <p class="kityoukouen">データが切り拓く生成 AIの未来～Everybody’s ready for AI except your
                                                data～</p>
                                            <p class="kityoukouen">データ連係基盤の統合／拡大、データマネジメントへ</p>
                                        </span>
                                    </div>
                                    <div class="modal__content js-modal"
                                        style="left: 420px; top: 0px; display: none; height: auto;">
                                        <div class="modal__inner">
                                            <div class="modal__box">
                                                <div class="modal-close"></div>
                                                <span class="modal-subtitle">【基調講演】</span>
                                                <h2 class="modal-title">データが切り拓く生成 AIの未来<br>
                                                    ～Everybody’s ready for AI except your data～</h2>
                                                <p class="modal-description">
                                                    今、あらゆる企業がAIの準備に注力していますが、自社データの準備はできているのでしょうか。ビジネスへの生成AIの適用が加速する世界で、AIを活かすデータを全社規模で適切に管理し、データと共にAIに命を吹き込むことで、データから価値を生み出しビジネスに変革をもたらすことが可能になります。これを実現するインフォマティカの戦略と、データの力により進化する世界をご紹介します。
                                                </p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/i_kozawa.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>インフォマティカ・ジャパン株式会社</h3>
                                                            <p>代表取締役社長<span>小澤 泰斗</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h2 class="modal-title" style="margin-top: 60px">
                                                    データ連係基盤の統合／拡大、データマネジメントへ</h2>
                                                <p class="modal-description">
                                                    当社では、クラウドシフトや運用効率化を目的に、データ連係基盤の最適化／インフォマティカへの『統合』を進めると共に、利用者の『拡大』を狙った多角的な取組み（教育／周知／性能改善）を行っております。本講演では、『統合』『拡大』に、中長期的なDX施策として『データマネジメント』へのチャレンジをキーワードに加え、取組み事例／苦労等を発表致します。
                                                </p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/u_yamada.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>中部電力株式会社</h3>
                                                            <p>ＤＸ推進室<br>ＩＴアーキテクトグループ<br>副長<span>山田 祐揮 氏</span></p>
                                                        </div>
                                                    </div>
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/u_imai.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>株式会社中電シーティーアイ</h3>
                                                            <p>技術本部<br>プラットフォームＲ ハイブリッドクラウドセンター<br>共通インフラＧ<br>主査<span>今井
                                                                    優一 氏</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--modal__inner-->
                                        <!--modal__content-->
                                    </div>


                                </td>
                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:35</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">13:40</td>

                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:40</td>
                            </tr>
                            <tr>


                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:45</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">13:50</td>

                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:50</td>
                            </tr>
                            <tr>


                                <td></td>
                                <td></td>
                                <td class="schedule-time">13:55</td>
                            </tr>
                            <!-- ←30分ごと -->
                            <!-- 30分ごと→ -->
                            <tr>

                                <td class="scchedule-time-30" rowspan="2">14:00</td>

                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:00</td>
                            </tr>
                            <tr>


                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:05</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">14:10</td>

                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:10</td>
                            </tr>
                            <tr>


                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:15</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">14:20</td>

                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:20</td>
                            </tr>
                            <tr>


                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:25</td>
                            </tr>
                            <!-- ←30分ごと -->
                            <!-- 30分ごと→ -->
                            <tr>

                                <td class="scchedule-time-30" rowspan="2">14:30</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:30</td>
                            </tr>
                            <tr>

                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:35</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">14:40</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="schedule-time">14:40</td>
                            </tr>
                            <tr>
                                <td class="scchedule-agenda" id="select-2" rowspan="5">

                                    <label>
                                        <input type="checkbox" name="sessions[]" id="id_check_agree_2"
                                            data-time="14:40-15:05" data-title="【AI時代の勝者へ：IDMCで実現するデータマネジメント】"
                                            data-hall="1st-hall" onclick="check_agree_func_2()"
                                            <?php $shortTitle = mb_substr('【AI時代の勝者へ：IDMCで実現するデータマネジメント】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                        <label for="2"
                                            style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                            <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                        </label>

                                    </label>
                                    <div class="modal-open">
                                        <p class="schedule-time-small">14:40 - 15:05</p>


                                        <span class="schedule-section-title" id="title-2"
                                            style="color: #ff6a13;">AI時代の勝者へ：IDMCで実現するデータマネジメント</span>
                                    </div>
                                    <div class="modal__content js-modal" style="left: 420px; top: 0px; display: none;">
                                        <div class="modal__inner">
                                            <div class="modal__box">
                                                <div class="modal-close"></div>
                                                <span class="modal-subtitle"></span>
                                                <h2 class="modal-title">AI時代の勝者へ：IDMCで実現するデータマネジメント</h2>
                                                <p class="modal-description">AI時代において、データマネジメントは重要な役割を果たし、Garbage In,
                                                    Garbage Outの原則に従い正確で品質の高いデータを収集・整備することが求められます。
                                                    本セミナーでは、IDMCの機能やその活用方法を事例を交えながら説明します。更にデータの収集、整備、分析の過程を解説し、データの価値を最大限に引き出すためのポイントをお伝えします。
                                                    AI時代の企業競争力を高めるチャンスをお見逃しなく。ぜひご参加ください。</p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/01_douman.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>アルプス システム インテグレーション株式会社</h3>
                                                            <p>セールス＆マーケティング統括部<br>営業部<br>エンタープライズ営業課<span>道満 純子 氏</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/01_takei.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>アルプス システム インテグレーション株式会社</h3>
                                                            <p>プロダクト&ソリューション事業部<br>ソリューションビジネス統括部<br>ソリューション推進1部<br>部長<span>武井
                                                                    順也 氏</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--modal__inner-->
                                        <!--modal__content-->
                                    </div>
                                </td>

                                <td class="scchedule-agenda" id="select-3" rowspan="6">

                                    <label>
                                        <input type="checkbox" name="sessions[]" id="id_check_agree_3"
                                            data-time="14:40-15:10"
                                            data-title="【現場技術者が語る！NTTデータのインフォマティカプロジェクトでのチャレンジ！】" data-hall="2nd-hall"
                                            onclick="check_agree_func_3()"
                                            <?php $shortTitle = mb_substr('【現場技術者が語る！NTTデータのインフォマティカプロジェクトでのチャレンジ！】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                        <label for="3"
                                            style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                            <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                        </label>

                                    </label>
                                    <div class="modal-open">
                                        <p class="schedule-time-small">14:40 – 15:10</p>


                                        <span class="schedule-section-title" id="title-3"
                                            style="color: #ff6a13;">現場技術者が語る！NTTデータのインフォマティカプロジェクトでのチャレンジ！</span>
                                    </div>
                                    <div class="modal__content js-modal"
                                        style="left: 128.705px; top: 98px; display: none;">
                                        <div class="modal__inner">
                                            <div class="modal__box">
                                                <div class="modal-close"></div>
                                                <span class="modal-subtitle"></span>
                                                <h2 class="modal-title">現場技術者が語る！<br>NTTデータのインフォマティカプロジェクトでのチャレンジ！</h2>
                                                <p class="modal-description">
                                                    NTTデータ内の様々なプロジェクトで活躍している技術者によるライトニングトーク。<br>
                                                    業務改革プロジェクトにおける連携基盤構築の難しさやそれを支えるインフォマティカがどう貢献したか、またどのような点に苦労したか、などインフォマティカの導入・運用において「今まさに挑戦している事」を様々な角度から赤裸々に語ります！<br>
                                                    現場の生の声が溢れる、時間を皆様にお届けします。</p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="../assets/images/live/kishimoto.png"
                                                                class="member-image"
                                                                onerror="this.src='../assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>株式会社NTTデータ</h3>
                                                            <p>ソリューション事業本部<br>
                                                                デジタルサクセスソリューション事業部<br>
                                                                データマネジメントプラットフォーム統括部<br>
                                                                課長代理
                                                                <span>岸本 康秀 氏</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="../assets/images/live/higuchi.png"
                                                                class="member-image"
                                                                onerror="this.src='../assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>株式会社NTTデータ</h3>
                                                            <p>ソリューション事業本部<br>
                                                                デジタルサクセスソリューション事業部<br>
                                                                データマネジメントプラットフォーム統括部
                                                                <br>主任
                                                                <span>樋口 舞子 氏</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="../assets/images/live/kitamura.png"
                                                                class="member-image"
                                                                onerror="this.src='../assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>株式会社NTTデータ</h3>
                                                            <p>ソリューション事業本部<br>
                                                                デジタルサクセスソリューション事業部<br>
                                                                データマネジメントプラットフォーム統括部<br>
                                                                主任
                                                                <span>北村 清也 氏</span>
                                                            </p>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                        <!--modal__content-->
                                    </div>
                                </td>

                                <td class="scchedule-agenda" id="select-4" rowspan="6">

                                    <label>
                                        <input type="checkbox" name="sessions[]" id="id_check_agree_4"
                                            data-time="14:40-15:10" data-title="【DX成功のカギ！　データをスピーディーな意思決定に活かすには】"
                                            data-hall="3rd-hall" onclick="check_agree_func_4()"
                                            <?php $shortTitle = mb_substr('【DX成功のカギ！　データをスピーディーな意思決定に活かすには】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                        <label for="4"
                                            style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                            <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                        </label>
                                    </label>
                                    <div class="modal-open">
                                        <p class="schedule-time-small">14:40 – 15:10</p>


                                        <span class="schedule-section-title" id="title-4"
                                            style="color: #ff6a13;">DX成功のカギ！　データをスピーディーな意思決定に活かすには</span>
                                    </div>
                                    <div class="modal__content js-modal"
                                        style="left: 130.205px; top: 198px; display: none;">
                                        <div class="modal__inner">
                                            <div class="modal__box">
                                                <div class="modal-close"></div>
                                                <span class="modal-subtitle"></span>
                                                <h2 class="modal-title">DX成功のカギ！<br>
                                                    データをスピーディーな意思決定に活かすには</h2>
                                                <p class="modal-description">
                                                    データをスピーディーな意思決定に活かせていますか？データの効果的な活用は迅速な意思決定に繋がり、そのためにはデータマネジメントが不可欠です。本セッションでは、データマネジメントの重要性とその課題、そしてそれに対する解決策をご紹介します。「データ活用を始めたけれど、いまいち成果が出ない」「データマネジメントで何をすべきか分からない」といったお悩みをお持ちの方、ぜひご参加ください。
                                                </p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/04_mikami.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>SCSK株式会社</h3>
                                                            <p>産業事業グループ 産業ソリューション第二事業本部<br>
                                                                エンタープライズソリューション第二部 第二課<span>三上 晶子 氏</span></p>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                        <!--modal__inner-->
                                        <!--modal__content-->
                                    </div>
                                </td>
                                <td class="schedule-time">14:45</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">14:50</td>

                                <td class="schedule-time">14:50</td>
                            </tr>
                            <tr>


                                <td class="schedule-time">14:55</td>
                            </tr>
                            <!-- ←30分ごと -->
                            <!-- 30分ごと→ -->
                            <tr>

                                <td class="scchedule-time-30" rowspan="2">15:00</td>

                                <td class="schedule-time">15:00</td>
                            </tr>
                            <tr>


                                <td class="schedule-time">15:05</td>
                            </tr>
                            <tr>

                                <td class="schedule-time-etc" rowspan="2">15:10</td>

                                <td class="scchedule-agenda" id="select-5" rowspan="5">
                                    <label>
                                        <input type="checkbox" name="sessions[]" id="id_check_agree_5"
                                            data-time="15:05-15:30" data-title="【ビジネスに革新をもたらす生成AIとモダン・データマネジメント】"
                                            data-hall="1st-hall" onclick="check_agree_func_5()"
                                            <?php $shortTitle = mb_substr('【ビジネスに革新をもたらす生成AIとモダン・データマネジメント】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                        <label for="5"
                                            style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                            <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                        </label>
                                    </label>
                                    <div class="modal-open">
                                        <p class="schedule-time-small">15:05 – 15:30</p>


                                        <span class="schedule-section-title" id="title-5"
                                            style="color: #ff6a13;">ビジネスに革新をもたらす生成AIとモダン・データマネジメント</span>
                                    </div>
                                    <div class="modal__content js-modal" style="left: 420px; top: 0px; display: none;">
                                        <div class="modal__inner">
                                            <div class="modal__box">
                                                <div class="modal-close"></div>
                                                <span class="modal-subtitle"></span>
                                                <h2 class="modal-title">ビジネスに革新をもたらす生成AIとモダン・データマネジメント</h2>
                                                <p class="modal-description">
                                                    「どうすればデータから価値を生み出し、ビジネスに革新をもたらす企業になれるのか？」　生成AIとデータマネジメントこそがその答えであり、データの力をあらゆるビジネスへ解放します。本講演では、「生成AIのためのデータマネジメント」と「データマネジメントのための生成AI」、この2大テーマにフォーカスしつつ、最新のデータマネジメントの世界をご紹介します。
                                                </p>
                                                <div class="modal-member">
                                                    <div class="member-table">
                                                        <div class="member-cell">
                                                            <img src="assets/images/live/i_morimoto.png"
                                                                class="member-image"
                                                                onerror="this.src='assets/images/live/img.png'">
                                                        </div>
                                                        <div class="member-cell">
                                                            <h3>インフォマティカ・ジャパン株式会社</h3>
                                                            <p>グローバル・パートナーテクニカルセールス<br>ソリューションアーキテクト＆エバンジェリスト<span>森本
                                                                    卓也</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--modal__inner-->
                                        <!--modal__content-->
                                    </div>
                    </div>
                    </td>
                    <td class="schedule-time">15:10</td>
                    </tr>
                    <tr>

                        <td></td>
                        <td></td>
                        <td class="schedule-time">15:15</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">15:20</td>

                        <td></td>
                        <td></td>
                        <td class="schedule-time">15:20</td>
                    </tr>
                    <tr>


                        <td></td>
                        <td></td>
                        <td class="schedule-time">15:25</td>
                    </tr>
                    <!-- ←30分ごと -->
                    <!-- 30分ごと→ -->
                    <tr>

                        <td class="scchedule-time-30" rowspan="2">15:30</td>


                        <td class="scchedule-agenda" id="select-6" rowspan="6">

                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_6" data-time="15:25-15:55"
                                    data-title="【「データの在処」と「人」をつなぐICT基盤の現在地】" data-hall="2nd-hall"
                                    onclick="check_agree_func_6()"
                                    <?php $shortTitle = mb_substr('【「データの在処」と「人」をつなぐICT基盤の現在地】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="6"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">15:25 – 15:55</p>


                                <span class="schedule-section-title" id="title-6"
                                    style="color: #ff6a13;">「データの在処」と「人」をつなぐICT基盤の現在地</span>
                            </div>
                            <div class="modal__content js-modal" style="left: 128.705px; top: 98px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">「データの在処」と「人」をつなぐICT基盤の現在地</h2>
                                        <p class="modal-description">
                                            ビジネスへの生成AI適用が加速する一方で、業務へのより高度な活用のためには、機密データも含めていかにデータを適切に収集し、安全に管理・活用していくかが大きな課題となっています。本講演では、AIの活用に向けて求められる最適なICT基盤について、データマネジメントの観点から、NTT
                                            Comがトータルでご支援できるソリューションや機能、ユースケースを交えてご紹介します。</p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/05_nakamura.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>NTTコミュニケーションズ株式会社</h3>
                                                    <p>プラットフォームサービス本部<br>
                                                        クラウド＆ネットワークサービス部<br>
                                                        データプラットフォームビジネス推進部門<br>
                                                        担当部長<span>中村 匡孝 氏</span></p>
                                                </div>
                                            </div>


                                        </div>

                                    </div>
                                </div>
                                <!--modal__inner-->
                            </div>
                        </td>

                        <td class="scchedule-agenda" id="select-7" rowspan="6">

                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_7" data-time="15:25-15:55"
                                    data-title="【データ活用に悩む方必見！スモールスタートで始めるデータ活用】" data-hall="3rd-hall"
                                    onclick="check_agree_func_7()"
                                    <?php $shortTitle = mb_substr('【データ活用に悩む方必見！スモールスタートで始めるデータ活用】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="7"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">15:25 – 15:55</p>
                                <span class="schedule-section-title" id="title-7"
                                    style="color: #ff6a13;">データ活用に悩む方必見！スモールスタートで始めるデータ活用</span>
                            </div>
                            <div class="modal__content js-modal" style="left: 130.205px; top: 198px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">データ活用に悩む方必見！<br>
                                            スモールスタートで始めるデータ活用</h2>
                                        <p class="modal-description">
                                            企業の価値創出においてデータ活用は不可欠ですが、具体的にどこから始めればよいか悩む企業も少なくありません。本セッションでは、データ活用の「きっかけ」を提供するため、CTCの経験・知見を基にニーズの高い分析事例をテンプレート化した「データ活用
                                            スモールスタートパック」についてご紹介いたします。提供コンテンツや事例を通し、データ活用の確実な第一歩を踏み出すための具体的なポイントをお伝えします。
                                        </p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/03_kobayashi.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>伊藤忠テクノソリューションズ株式会社</h3>
                                                    <p>データビジネス企画・推進本部<br>
                                                        データビジネス営業推進部 <br>
                                                        データビジネスデザイン課<span>小林 直人 氏</span></p>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>
                        </td>
                        <td class="schedule-time">15:30</td>
                    </tr>
                    <tr>

                        <td class="scchedule-agenda" id="select-8" rowspan="5">


                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_8" data-time="15:30-15:55"
                                    data-title="【データ活用の課題と最新動向｜現場で広げるデータの利活用とは】" data-hall="1st-hall"
                                    onclick="check_agree_func_8()"
                                    <?php $shortTitle = mb_substr('【データ活用の課題と最新動向｜現場で広げるデータの利活用とは】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="8"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">15:30 – 15:55</p>


                                <span class="schedule-section-title" id="title-8"
                                    style="color: #ff6a13;">データ活用の課題と最新動向<br>現場で広げるデータの利活用とは</span>
                            </div>
                            <div class="modal__content js-modal" style="left: 420px; top: 0px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">データ活用の課題と最新動向<br>
                                            現場で広げるデータの利活用とは</h2>
                                        <p class="modal-description">
                                            データ活用とは、企業に蓄積されたデータや社外の情報ソースを、経営テーマや業務課題に沿って継続的に活用する営みです。日本企業においてはその重要性を認識しても活用が浸透しない、またはプロジェクトが進まないといった声を多くお聞きします。<br>
                                            本講演では、データに裏付けされたデータドリブン経営を実現するための重要なポイントや、クラウドデータファブリックで実装されるソリューション活用の未来像をお伝えします。
                                        </p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/02_suzuki.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>NSW株式会社</h3>
                                                    <p>サービスソリューション事業本部 クラウドプラットフォーム事業部<br>副事業部長<span>鈴木 輝亮 氏</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>

                        </td>
                        <td class="schedule-time">15:35</td>

                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">15:40</td>


                        <td class="schedule-time">15:40</td>
                    </tr>
                    <tr>


                        <td class="schedule-time">15:45</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">15:50</td>
                        <td class="schedule-time">15:50</td>
                    </tr>
                    <tr>


                        <td class="schedule-time">15:55</td>
                    </tr>
                    <!-- ←30分ごと -->
                    <!-- 30分ごと→ -->
                    <tr>

                        <td class="scchedule-time-30" rowspan="2">16:00</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">16:00</td>
                    </tr>
                    <tr>

                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">16:05</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">16:10</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">16:10</td>
                    </tr>
                    <tr>
                        <td class="scchedule-agenda" id="select-9" rowspan="5">

                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_9" data-time="16:10-16:35"
                                    data-title="【データ戦略を支えるインフォマティカ・プラットフォームの全体像〜ETL/ELTからマスタデータ管理、データガバナンスまで〜】"
                                    data-hall="1st-hall" onclick="check_agree_func_9()"
                                    <?php $shortTitle = mb_substr('【データ戦略を支えるインフォマティカ・プラットフォームの全体像〜ETL/ELTからマスタデータ管理、データガバナンスまで〜】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="9"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">16:10 – 16:35</p>


                                <span class="schedule-section-title" id="title-9"
                                    style="color: #ff6a13;">データ戦略を支えるインフォマティカ・プラットフォームの全体像
                                    〜ETL/ELTからマスタデータ管理、データガバナンスまで〜</span>
                            </div>
                            <div class="modal__content js-modal" style="left: 420px; top: 0px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">データ戦略を支えるインフォマティカ・プラットフォームの全体像<br>
                                            〜ETL/ELTからマスタデータ管理、データガバナンスまで〜</h2>
                                        <p class="modal-description">
                                            インフォマティカ・プラットフォームは、データ統合、マスタデータ管理、データ/AIガバナンスを統合的に活用し、企業のデータ戦略を強化します。本講演では、AI活用、DX推進、クラウド化などお客様のビジネス、ITを取り巻く様々な活動、課題に対して、プラットフォーム全体としてどのようにデータ活用を支援できるかを探り、ビジネス価値を最大化するためのベストプラクティスおよびアプローチをご紹介します。
                                        </p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/i_suzuki.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>インフォマティカ・ジャパン株式会社</h3>
                                                    <p>テクニカルセールス本部<br>プリンシパルソリューションアーキテクト<span>鈴木 直人</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>
                        </td>

                        <td class="scchedule-agenda" id="select-10" rowspan="9">

                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_10" data-time="16:10-16:55"
                                    data-title="【未来を開拓するデータ流通・活用基盤「Xzilla」】" data-hall="2nd-hall"
                                    onclick="check_agree_func_10()"
                                    <?php $shortTitle = mb_substr('【未来を開拓するデータ流通・活用基盤「Xzilla」】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="10"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">16:10 – 16:55</p>


                                <span class="schedule-section-title" id="title-10"
                                    style="color: #ff6a13;">未来を開拓するデータ流通・活用基盤「Xzilla」</span>
                            </div>
                            <div class="modal__content js-modal" style="left: 128.705px; top: 98px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">未来を開拓するデータ流通・活用基盤「Xzilla」</h2>
                                        <p class="modal-description">
                                            「2050年カーボンニュートラル」実現のため様々な挑戦をし続ける北海道ガス。「エネルギーと環境の最適化による快適な社会の創造」をテーマにエネルギーの地産地消など地域課題と向き合いながら付加価値の高いサービス提供と業務改革を進め、脱炭素社会の実現を目指しています。これらの取り組みの中核を担うのがデータ流通・活用基盤「Xzilla（くじら）」。DXを機動的に実現する「Xzilla」とは何かを紹介致します。
                                        </p>
                                        <div class="modal-member">

                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/01_tsumita.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>アルプス システム インテグレーション株式会社</h3>
                                                    <p>プロダクト&ソリューション事業部<br>ソリューションビジネス統括部<br>ソリューション推進1部<br>課長<span>積田
                                                            雄人氏</span></p>
                                                </div>
                                            </div>

                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/01_saitou.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>北海道ガス株式会社</h3>
                                                    <p>デジタルトランスフォーメーション・構造改革推進部<br>情報プラットフォーム基盤管理グループ<span>齊藤 圭司
                                                            氏</span>
                                                    </p>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>
                        </td>
                        <td></td>
                        <td class="schedule-time">16:15</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">16:20</td>


                        <td></td>
                        <td class="schedule-time">16:20</td>
                    </tr>
                    <tr>



                        <td></td>
                        <td class="schedule-time">16:25</td>
                    </tr>
                    <!-- ←30分ごと -->
                    <!-- 30分ごと→ -->
                    <tr>

                        <td class="scchedule-time-30" rowspan="2">16:30</td>

                        <td></td>
                        <td class="schedule-time">16:30</td>
                    </tr>
                    <tr>


                        <td></td>
                        <td class="schedule-time">16:35</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">16:40</td>
                        <td class="scchedule-agenda" id="select-11" rowspan="5">

                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_11" data-time="16:35-17:00"
                                    data-title="【Snowflake, Databricks, AWS, Microsoft, GCP...マルチクラウドで創る最強データプラットフォーム】"
                                    data-hall="1st-hall" onclick="check_agree_func_11()"
                                    <?php $shortTitle = mb_substr('【Snowflake, Databricks, AWS, Microsoft, GCP...マルチクラウドで創る最強データプラットフォーム】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="11"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">16:35 - 17:00</p>


                                <span class="schedule-section-title" id="title-11" style="color: #ff6a13;">Snowflake,
                                    Databricks, AWS, Microsoft, GCP...
                                    マルチクラウドで創る最強データプラットフォーム</span>
                            </div>
                            <div class="modal__content js-modal"
                                style="left: 420px; top: 0px; display: none; height: auto;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle"></span>
                                        <h2 class="modal-title">Snowflake, Databricks, AWS, Microsoft, GCP...<br>
                                            マルチクラウドで創る最強データプラットフォーム</h2>
                                        <p class="modal-description">
                                            熱量溢れるコミュニティとユーザー体験に優れたSnowflake。AIエンジニアに評価されるDatabricks。世界で最も使われているAWS。高い期待を寄せられるMicrosoft
                                            Fabric。根強い人気を維持するGCP。自社にとって最適なデータプラットフォームを目指す場合、どれか一つを選択する必要はありません。本講演では、あらゆるデータプラットフォームを高度化しながら融合するマルチクラウド・データマネジメントとその最新ソリューションについてご紹介します。
                                        </p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/i_arata.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>インフォマティカ・ジャパン株式会社</h3>
                                                    <p>テクニカルセールス本部<br>執行役員<br>テクニカルセールス本部本部長<span>荒田 圭哉</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>
                        </td>

                        <td></td>
                        <td class="schedule-time">16:40</td>
                    </tr>
                    <tr>



                        <td></td>
                        <td class="schedule-time">16:45</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">16:50</td>


                        <td></td>
                        <td class="schedule-time">16:50</td>
                    </tr>
                    <tr>



                        <td></td>
                        <td class="schedule-time">16:55</td>
                    </tr>
                    <!-- ←30分ごと -->
                    <!-- 30分ごと→ -->
                    <tr>

                        <td class="scchedule-time-30" rowspan="2">17:00</td>

                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:00</td>
                    </tr>
                    <tr>


                        <td class="scchedule-agenda" id="select-12" rowspan="4">
                            <label>
                                <input type="checkbox" name="sessions[]" id="id_check_agree_12" data-time="17:00-17:20"
                                    data-title="【SUBARU流 全社データ活用で笑顔を作る「モノづくり革新」と「価値づくり」】" data-hall="1st-hall"
                                    onclick="check_agree_func_12()"
                                    <?php $shortTitle = mb_substr('【SUBARU流 全社データ活用で笑顔を作る「モノづくり革新」と「価値づくり」】', 0, 8); echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'disabled' : '';?>>
                                <label for="12"
                                    style="color: <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? 'red' : '#212529'; ?>;">
                                    <?php echo (isset($sessionStatus[$shortTitle]) && $sessionStatus[$shortTitle] === 'full') ? '満席' : '選択'; ?>
                                </label>
                            </label>
                            <div class="modal-open">
                                <p class="schedule-time-small">17:00 - 17:20</p>


                                <span class="schedule-section-title" id="title-12" style="color: #ff6a13;">
                                    <p class="kityoukouen" style="margin-top:0">【特別講演】
                                    <p class="kityoukouen" style="margin:5px 0 0 0">【SUBARU流
                                        全社データ活用で笑顔を作る「モノづくり革新」と「価値づくり」
                                    </p>
                                </span>
                            </div>
                            <div class="modal__content js-modal" style="left: 420px; top: 0px; display: none;">
                                <div class="modal__inner">
                                    <div class="modal__box">
                                        <div class="modal-close"></div>
                                        <span class="modal-subtitle">【特別講演】</span>
                                        <h2 class="modal-title">SUBARU流 全社データ活用で笑顔を作る「モノづくり革新」と「価値づくり」</h2>
                                        <p class="modal-description">
                                            SUBARUは世界最先端の「モノづくり革新」と「価値づくり」を目指しており、モノづくりのプロセスを可視化するPLMの領域だけでなく(＝モノづくり革新)、SUBARU車両のトレーサビリティデータを利用してお客様に対して新しい価値(=価値づくり)を提供するための全社データ統合基盤をインフォマティカ製品を活用して実現しています。部門横断で活用できるデータ統合基盤(G-PLM)を構築し、データを以って組織の壁を壊し、データを以って新しい価値を生み出す、その取り組みをご紹介します。
                                        </p>
                                        <div class="modal-member">
                                            <div class="member-table">
                                                <div class="member-cell">
                                                    <img src="assets/images/live/ichikawa.png" class="member-image"
                                                        onerror="this.src='assets/images/live/img.png'">
                                                </div>
                                                <div class="member-cell">
                                                    <h3>株式会社SUBARU</h3>
                                                    <p>データ統括活用推進部<br>主査<span>市川 健太郎 氏</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--modal__inner-->
                                <!--modal__content-->
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:05</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">17:10</td>

                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:10</td>
                    </tr>
                    <tr>


                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:15</td>
                    </tr>
                    <tr>

                        <td class="schedule-time-etc" rowspan="2">17:20</td>

                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:20</td>
                    </tr>
                    <tr>

                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:25</td>
                    </tr>
                    <!-- ←30分ごと -->
                    <!-- 30分ごと→ -->
                    <tr>

                        <td class="scchedule-time-30" rowspan="2">17:30</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:30</td>
                    </tr>
                    <tr>

                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="schedule-time">17:35</td>
                    </tr>


                    </table>




                </div>




            </div>


            <p id="error-message" style="color: red; text-align: center; display: none; padding-bottom:20px;">
                ※セッションを選択してください
            </p>

            <form id="sessionForm"
                action="privacy.php?utm_source=<?php echo htmlspecialchars($utm_source); ?>&utm_medium=<?php echo htmlspecialchars($utm_medium); ?>&utm_campaign=<?php echo htmlspecialchars($utm_campaign); ?>"
                method="post">
                <div class="select-btn-box">
                    <button type="button" onclick="radioDeselection()" class="select-btn-2">選択リセット</button>
                    <button type="button" class="select-btn" onclick="submitForm()">登録確認へ進む</button>
                </div>
                <input type="hidden" name="from_index" value="true">
                <input type="hidden" name="sessions" id="sessions"> <!-- ここを追加 -->
            </form>

        </div>


        <script language="JavaScript" type="text/JavaScript">
            function submitForm() {
    let selectedLectures = [];
    document.querySelectorAll('input[name="sessions[]"]:checked').forEach(function (checkbox) {
        selectedLectures.push(checkbox.dataset.time + ";" + checkbox.dataset.title + ";" + checkbox.dataset.hall);
    });
    if (selectedLectures.length === 0) {
        document.getElementById('error-message').style.display = 'block';
        return;
    } else {
        document.getElementById('error-message').style.display = 'none';
    }
    document.getElementById('sessions').value = JSON.stringify(selectedLectures);
    document.getElementById('sessionForm').submit();
}

function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('input[name="sessions[]"]');
    checkboxes.forEach(checkbox => {
        const label = checkbox.nextElementSibling;
        if (label && label.textContent.trim() === '満席') {
            checkbox.disabled = true;
            label.style.color = 'red';
            const selectId = checkbox.id.replace('id_check_agree_', 'select-');
            // 背景色を強制的に設定
            const selectElement = document.getElementById(selectId);
            selectElement.style.setProperty('background-color', '#d3d3d3', 'important');
            checkbox.setAttribute('data-full', 'true'); // 満席フラグを追加
        } else {
            checkbox.setAttribute('data-full', 'false'); // 満席ではないフラグを追加
        }
    });
    reevaluateCheckboxes();
}

window.onload = function() {
    initializeCheckboxes();
};

function isTimeOverlap(time1, time2) {
    const [start1, end1] = time1.split('-').map(t => parseInt(t.replace(':', ''), 10));
    const [start2, end2] = time2.split('-').map(t => parseInt(t.replace(':', ''), 10));
    return !(end1 <= start2 || end2 <= start1);
}

function reevaluateCheckboxes() {
    const checkboxes = document.querySelectorAll('input[name="sessions[]"]');
    checkboxes.forEach(checkbox => {
        const selectId = checkbox.id.replace('id_check_agree_', 'select-');
        const selectElement = document.getElementById(selectId);

        // 満席のチェックボックスは再評価しない
        if (checkbox.dataset.full === 'true') {
            return;
        }

        const currentTime = checkbox.dataset.time;
        let overlap = false;

        checkboxes.forEach(otherCheckbox => {
            if (otherCheckbox !== checkbox && otherCheckbox.checked && isTimeOverlap(otherCheckbox.dataset.time, currentTime)) {
                overlap = true;
            }
        });

        if (overlap) {
            checkbox.disabled = true;
            selectElement.style.setProperty('background-color', '#d3d3d3', 'important');
            selectElement.style.setProperty('color', '#000', 'important');
        } else {
            if (!checkbox.checked) { // チェックされていない場合のみスタイルをリセット
                checkbox.disabled = false;
                selectElement.style.setProperty('background-color', '#fff', 'important');
                selectElement.style.setProperty('color', '#000', 'important');
            }
        }
    });
}

function toggleSelect(checkId, selectId, titleId) {
    const checkbox = document.getElementById(checkId);
    const selectElement = document.getElementById(selectId);
    const titleElement = document.getElementById(titleId);

    if (checkbox.checked) {
        // 背景色と文字色を強制的に設定
        selectElement.style.setProperty('background-color', '#ff6a13', 'important');
        
        titleElement.style.setProperty('color', 'white', 'important');
        console.log(`Selected: ${selectId} - Background: #ff6a13, Color: white`);
    } else {
        // 背景色と文字色をリセット
        selectElement.style.setProperty('background-color', '#fff', 'important');
        selectElement.style.setProperty('color', '#000', 'important');
        titleElement.style.setProperty('color', '#ff6a13', 'important');
        console.log(`Deselected: ${selectId} - Background: #fff, Color: #000`);
    }

    reevaluateCheckboxes();
}

// イベントリスナーの設定
for (let i = 1; i <= 12; i++) {
    window[`check_agree_func_${i}`] = function() {
        toggleSelect(`id_check_agree_${i}`, `select-${i}`, `title-${i}`);
    };
}

function radioDeselection() {
    document.querySelectorAll('input[name="sessions[]"]').forEach(function (element) {
        element.checked = false;
        const selectId = element.id.replace('id_check_agree_', 'select-');
        const titleId = 'title-' + selectId.split('-')[1];
        const titleElement = document.getElementById(titleId);

        const label = element.nextElementSibling;
        if (label && label.textContent.trim() !== '満席') {
            element.disabled = false;
            document.getElementById(selectId).style.background = '#fff';
            titleElement.style.color = '#ff6a13';
        }
    });
    reevaluateCheckboxes();
}

document.addEventListener('DOMContentLoaded', function () {
    initializeCheckboxes();
});


    </script>



        <!-- footer start -->
        <div class="footer-wave-image" style="background-image: url(assets/images/footer-wave.jpg);"></div>

        <!-- footer end -->


        <div class="drawer-overlay drawer-toggle"></div>
        <script src="assets/js/countdown.js"></script>
        <script src="assets/js/scroll_color.js"></script>






</body>

</html>