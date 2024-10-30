<?php
// エラーメッセージを表示（デバッグ用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// config.php をインクルードして定義済みの定数を使用
include 'config.php';

try {
    // PDOオブジェクトの作成
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 接続エラー時にメッセージを表示して終了
    echo 'データベース接続に失敗しました: ' . $e->getMessage();
    exit();
}

// SQLクエリの実行（mainカラムがNULLではないレコードのみを取得、作成日で昇順にソート）
$sql = "SELECT 
            users.loginID, 
            users.surname,
            users.given_name, 
            users.corporate_type,
            users.corporate_prefix,
            users.company_name,
            users.phone,
            users.work_email, 
            attendance.main, 
            attendance.main_comment, 
            attendance.mini_a_1, 
            attendance.comment_a1,
            attendance.mini_a_2, 
            attendance.comment_a2,
            attendance.mini_a_3, 
            attendance.comment_a3,
            attendance.mini_b_1, 
            attendance.comment_b1,
            attendance.mini_b_2, 
            attendance.comment_b2,
            attendance.create_date
        FROM users
        LEFT JOIN attendance ON users.loginID = attendance.loginID
        WHERE attendance.main IS NOT NULL
        ORDER BY attendance.create_date ASC";  // 古い順にソート

$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 合計人数を取得
$total_users = count($results);


// 各列の空白でない値のカウントを初期化
$mini_a_1_count = 0;
$mini_a_2_count = 0;
$mini_a_3_count = 0;
$mini_b_1_count = 0;
$mini_b_2_count = 0;

// 各カラムに空白でない値がある場合にカウントを増やす
foreach ($results as $row) {
    if (!empty($row['mini_a_1'])) {
        $mini_a_1_count++;
    }
    if (!empty($row['mini_a_2'])) {
        $mini_a_2_count++;
    }
    if (!empty($row['mini_a_3'])) {
        $mini_a_3_count++;
    }
    if (!empty($row['mini_b_1'])) {
        $mini_b_1_count++;
    }
    if (!empty($row['mini_b_2'])) {
        $mini_b_2_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー出席情報</title>
    <style>
 body {
            font-family: Arial, sans-serif;
        }
p{
        text-align: center;
        font-weight: bold;
        font: caption;
        font-size: 18px;
        color: red;
}
        table {
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            white-space: nowrap;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .container {
            margin: 0 auto;
            padding: 20px;

        }
        h1 {
            text-align: center;
            padding-top: 30px;
        }

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

    tr:hover {
        background-color: #f1f1f1;
    }

    /* スマートフォン向けデザイン */
    @media (max-width: 768px) {
        table, th, td {
            font-size: 12px;
            padding: 8px;
        }

        .container {
            width: 100%;
            padding: 10px;
        }

        h1 {
            font-size: 20px;
        }

        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

.count-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 20px; /* 各要素の間隔を設定 */
}

.count-list li {
    font-size: 16px; /* フォントサイズを調整 */
    color: red;
}


    /* iPhoneなどの小さな画面向け */
    @media (max-width: 480px) {
        table, th, td {
            font-size: 10px;
            padding: 5px;
        }

        h1 {
            font-size: 18px;
        }

        td {
            padding: 6px;
        }

        .container {
            overflow-x: auto;
        }
    }

    </style>
</head>

<body>
<div class="header">
        <form method="post" action="adminMenu.php">
            <button type="submit">メニュー</button>
        </form>
        <form method="post" action="adminLogout.php">
            <button type="submit" name="logout">ログアウト</button>
        </form>
    </div>
    <div class="container">
        <h1>ユーザー出席情報</h1>
        <p>現在の受付人数は <?php echo htmlspecialchars($total_users, ENT_QUOTES, 'UTF-8'); ?> 人です。

<ul class="count-list">
    <li>ミニA1: <?php echo $mini_a_1_count; ?> 人</li>
    <li>ミニA2: <?php echo $mini_a_2_count; ?> 人</li>
    <li>ミニA3: <?php echo $mini_a_3_count; ?> 人</li>
    <li>ミニB1: <?php echo $mini_b_1_count; ?> 人</li>
    <li>ミニB2: <?php echo $mini_b_2_count; ?> 人</li>
</ul>


</p> <!-- 合計人数を表示 -->
        <table>
            <thead>
                <tr>
                    <th>ログインID</th>
                    <th>名前</th>
                    <th>会社名</th>
                    <th>電話番号</th>
                    <th>メールアドレス</th>
                    <th>参加</th>
                    <th>備考</th>
                    <th>ミニA1</th>
                    <th>A1備考</th>
                    <th>ミニA2</th>
                    <th>A2備考</th>
                    <th>ミニA3</th>
                    <th>A3備考</th>
                    <th>ミニB1</th>
                    <th>B1備考</th>
                    <th>ミニB2</th>
                    <th>B2備考</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                   <td><?php echo htmlspecialchars($row['loginID'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php 
                        $fullName = (isset($row['surname']) ? $row['surname'] : '') . ' ' . (isset($row['given_name']) ? $row['given_name'] : '');
                        echo htmlspecialchars(trim($fullName), ENT_QUOTES, 'UTF-8'); 
                    ?></td>

                     <!-- ログインIDを表示 -->

                    <td class="wrap-text">
                        <?php
                            if (isset($row['corporate_prefix']) && isset($row['company_name']) && isset($row['corporate_type'])) {
                                if ($row['corporate_prefix'] === '前') {
                                    echo htmlspecialchars($row['corporate_type'] . '' . $row['company_name'], ENT_QUOTES, 'UTF-8');
                                } elseif ($row['corporate_prefix'] === '後') {
                                    echo htmlspecialchars($row['company_name'] . '' . $row['corporate_type'], ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars($row['company_name'], ENT_QUOTES, 'UTF-8');
                                }
                            } else {
                                echo '';
                            }
                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['work_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['main_comment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['mini_a_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['comment_a1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['mini_a_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['comment_a2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['mini_a_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['comment_a3'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['mini_b_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['comment_b1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['mini_b_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['comment_b2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['create_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>
