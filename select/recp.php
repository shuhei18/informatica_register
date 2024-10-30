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

// SQLクエリの実行
$sql = "SELECT 
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
        LEFT JOIN attendance ON users.loginID = attendance.loginID";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ユーザー出席情報</h1>
        <table>
            <thead>
                <tr>
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
                        <!-- 姓と名をくっつけて表示 -->
<td>
    <?php 
    // 姓と名をくっつけて表示。nullの場合は空文字を使用
    $fullName = (isset($row['surname']) ? $row['surname'] : '') . ' ' . (isset($row['given_name']) ? $row['given_name'] : '');
    echo htmlspecialchars(trim($fullName), ENT_QUOTES, 'UTF-8'); 
    ?>
</td>


                        <!-- 会社名の条件付き表示 -->
                        <td class="wrap-text">
                            <?php
                            if (isset($row['corporate_prefix']) && isset($row['company_name']) && isset($row['corporate_type'])) {
                                if ($row['corporate_prefix'] === '前') {
                                    // corporate_type + company_name
                                    echo htmlspecialchars($row['corporate_type'] . ' ' . $row['company_name'], ENT_QUOTES, 'UTF-8');
                                } elseif ($row['corporate_prefix'] === '後') {
                                    // company_name + corporate_type
                                    echo htmlspecialchars($row['company_name'] . ' ' . $row['corporate_type'], ENT_QUOTES, 'UTF-8');
                                } else {
                                    // corporate_prefix が "なし" の場合は company_name のみ表示
                                    echo htmlspecialchars($row['company_name'], ENT_QUOTES, 'UTF-8');
                                }
                            } else {
                                echo '';
                            }
                            ?>
                        </td>

                        <!-- 電話番号 -->
                        <td><?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- メールアドレス -->
                        <td><?php echo htmlspecialchars($row['work_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- メイン出席 -->
                        <td><?php echo htmlspecialchars($row['main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['main_comment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- ミニA1 -->
                        <td><?php echo htmlspecialchars($row['mini_a_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['comment_a1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- ミニA2 -->
                        <td><?php echo htmlspecialchars($row['mini_a_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['comment_a2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- ミニA3 -->
                        <td><?php echo htmlspecialchars($row['mini_a_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['comment_a3'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- ミニB1 -->
                        <td><?php echo htmlspecialchars($row['mini_b_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['comment_b1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- ミニB2 -->
                        <td><?php echo htmlspecialchars($row['mini_b_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['comment_b2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- 作成日 -->
                        <td><?php echo htmlspecialchars($row['create_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cells = document.querySelectorAll('.wrap-text');
        cells.forEach(cell => {
            const text = cell.textContent.trim(); // セルのテキストを取得
            if (text.length > 20) {
                const newText = text.match(/.{1,20}/g).join('<br>'); // 20文字ごとに改行
                cell.innerHTML = newText; // 改行されたテキストを挿入
            }
        });
    });
</script>

</body>


</html>
