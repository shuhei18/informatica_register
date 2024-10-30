<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// config.php を読み込む
require 'config.php';

try {
    // データベース接続
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQLクエリの実行
    $sql = "
    SELECT 
        u.id AS `ID`,
        u.loginID AS `ログインID`,
        CASE 
            WHEN u.corporate_prefix = '前' THEN CONCAT(u.corporate_type, u.company_name)
            WHEN u.corporate_prefix = '後' THEN CONCAT(u.company_name, u.corporate_type)
            WHEN u.corporate_prefix = '無し' THEN u.company_name
            ELSE u.company_name
        END AS `法人名`,
        u.company_name_kana AS `法人名フリガナ`,
        u.department_name AS `部署名`,
        u.position_name AS `役職名`,
        u.surname AS `お名前(姓)`,
        u.given_name AS `お名前(名)`,
        u.surname_kana AS `お名前フリガナ(セイ)`,
        u.given_name_kana AS `お名前フリガナ(メイ)`,
        u.postal_code AS `郵便番号`,
        u.prefecture AS `都道府県`,
        u.city AS `市区郡名`,
        u.town AS `町村名`,
        u.street AS `丁目番地`,
        u.building AS `ビル・建物名`,
        u.phone AS `電話番号`,
        u.work_email AS `勤務先メールアドレス`,
        u.utm_campaign AS `スポンサーパラメータ`,
        COALESCE(t.source, '不明') AS `集客ソース`,
        u.industry_category AS `業種区分`,
        u.department_category AS `部署カテゴリー`,
        u.position_category AS `役職区分`,
        u.employees_count AS `従業員数`,
        u.annual_revenue AS `年商規模`,
        u.event_involvement AS `関与`,
        u.event_involvement_other AS `製品/サービス導入関与(その他)`,
        u.create_date AS `登録日`,
        u.update_date AS `更新日`,
        COUNT(CASE WHEN ul.lecture_title LIKE '%【データが切り拓く生成 AIの未来～Everybody’s ready for AI except your data～】%' THEN 1 END) AS `基調講演`,
        COUNT(CASE WHEN ul.lecture_title = '【AI時代の勝者へ：IDMCで実現するデータマネジメント】' THEN 1 END) AS `ALSI_1`,
        COUNT(CASE WHEN ul.lecture_title = '【ビジネスに革新をもたらす生成AIとモダン・データマネジメント】' THEN 1 END) AS `インフォマティカ①`,
        COUNT(CASE WHEN ul.lecture_title = '【データ活用の課題と最新動向｜現場で広げるデータの利活用とは】' THEN 1 END) AS `NSW`,
        COUNT(CASE WHEN ul.lecture_title LIKE '【データ戦略を支えるインフォマティカ・プラットフォームの全体像%' THEN 1 END) AS `インフォマティカ②`,
        COUNT(CASE WHEN ul.lecture_title = '【Snowflake, Databricks, AWS, Microsoft, GCP...マルチクラウドで創る最強データプラットフォーム】' THEN 1 END) AS `インフォマティカ③`,
        COUNT(CASE WHEN ul.lecture_title = '【SUBARU流 全社データ活用で笑顔を作る「モノづくり革新」と「価値づくり」】' THEN 1 END) AS `SUBARU`,
        COUNT(CASE WHEN ul.lecture_title LIKE '%NTTデータ%' THEN 1 END) AS  `NTTデータ（調整中）`,
        COUNT(CASE WHEN ul.lecture_title = '【DX成功のカギ！　データをスピーディーな意思決定に活かすには】' THEN 1 END) AS `SCSK`,
        COUNT(CASE WHEN ul.lecture_title = '【「データの在処」と「人」をつなぐICT基盤の現在地】' THEN 1 END) AS `NTTコミュニケーションズ`,
        COUNT(CASE WHEN ul.lecture_title = '【データ活用に悩む方必見！スモールスタートで始めるデータ活用】' THEN 1 END) AS `CTC`,
        COUNT(CASE WHEN ul.lecture_title = '【未来を開拓するデータ流通・活用基盤「Xzilla」】' THEN 1 END) AS `ALSI_2`
    FROM 
        users u
    LEFT JOIN 
        user_lectures ul ON u.loginID = ul.loginID
    LEFT JOIN
        utm_campaign_source t
    ON (
        CASE
            WHEN u.utm_campaign LIKE CONCAT('%', t.utm_campaign, '%') THEN t.utm_campaign
            ELSE NULL
        END = t.utm_campaign
    )
    GROUP BY 
        u.id, u.loginID, u.company_name, u.company_name_kana, u.department_name, u.position_name,
        u.surname, u.given_name, u.surname_kana, u.given_name_kana, u.postal_code,
        u.prefecture, u.city, u.town, u.street, u.building, u.phone, u.work_email,
        u.utm_campaign, t.source, u.industry_category, u.department_category, 
        u.position_category, u.employees_count, u.annual_revenue, u.event_involvement,
        u.event_involvement_other, u.create_date, u.update_date
    ORDER BY u.id
    ";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // データが取得できているか確認
    if (empty($data)) {
        throw new Exception('データが取得できませんでした。');
    }

    // CSVファイルの作成
    if (isset($_GET['download'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="data_export.csv"');
        $output = fopen('php://output', 'w');

        // ヘッダー行の出力
        fputcsv($output, array_keys($data[0]));

        // データ行の出力
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データ表示</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    h1{
       padding-top: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        overflow-x: auto;
        display: block;
        white-space: nowrap;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #f4f4f4;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    thead th {
        background-color: #007BFF;
        color: white;
    }

    tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tbody tr:hover {
        background-color: #ddd;
    }

    .container {
        margin: 20px;
        overflow-x: auto;
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
        <h1>データ表示</h1>
        <a href="?download=true" class="download-button">CSVでダウンロード</a>
        <table border="1">
            <thead>
                <tr>
                    <?php
                    // テーブルのヘッダーを表示
                    if (!empty($data)) {
                        foreach (array_keys($data[0]) as $index => $header) {
                            // 30列目以降にfixed-widthクラスを追加
                            $class = $index >= 29 ? 'fixed-width' : '';
                            echo "<th class='{$class}'>" . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . "</th>";
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // テーブルのデータ行を表示
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $index => $cell) {
                        // 30列目以降にfixed-widthクラスを追加
                        $class = $index >= 29 ? 'fixed-width' : '';
                        echo "<td class='{$class}'>" . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . "</td>";
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>