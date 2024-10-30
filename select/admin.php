<?php
session_start();
require 'config.php';

// セッションタイムアウトの時間（秒）
$timeout_duration = 144000; // 分

// セッションが開始されていて、最後のアクティビティが設定されている場合
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout_duration) {
        session_unset();
        session_destroy();
        header('Location: adminsLogin_iwt2024.php');
        exit;
    }
}

// 最後のアクティビティを更新
$_SESSION['last_activity'] = time();

// セッションが存在しない場合はログインページにリダイレクト
if (!isset($_SESSION['admin_staff_in'])) {
    header('Location: adminsLogin_iwt2024.php');
    exit;
}

// ログアウト処理
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: adminsLogin_iwt2024.php');
    exit;
}

// メール送信後のアラート表示
if (isset($_SESSION['total_sent'])) {
    $total_sent = $_SESSION['total_sent'];
    echo "<script>alert('合計 " . htmlspecialchars($total_sent, ENT_QUOTES, 'UTF-8') . " 人にメールが送信されました。');</script>";
    unset($_SESSION['total_sent']);
}

// 来場者フィルタのチェック状態を取得
$attended_only = isset($_POST['attended_only']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    if (!empty($_POST['selected_users']) && isset($_POST['email_type'])) {
        $selected_users = $_POST['selected_users'];
        $email_type = $_POST['email_type'];
        $total_sent = count($selected_users);

        if ($email_type === 'pdf') {
            require_once 'pdf_emails.php';
        } elseif ($email_type === 'attention') {
            require_once 'attention_emails.php';
        } elseif ($email_type === 'visitors') {
            require_once 'visitors_email.php'; // 来場者限定メール
        }

        // セッションに送信数を保存
        $_SESSION['total_sent'] = $total_sent;

        // 処理が完了したらリダイレクト
        header('Location: admin.php?sent=true');
        exit;
    }
}

if (isset($_GET['sent']) && $_GET['sent'] === 'true') {
    if (isset($_SESSION['total_sent'])) {
        $total_sent = $_SESSION['total_sent'];
        echo "<script>showSentCount($total_sent);</script>";
        unset($_SESSION['total_sent']);
    }
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 基本クエリ
    $query = "
        SELECT u.id, u.loginID, u.surname, u.given_name, u.work_email, u.create_date, a.main 
        FROM users u
        LEFT JOIN attendance a ON u.loginID = a.loginID
        WHERE 1=1
    ";

    // 日付範囲の指定がある場合
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '00:00';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '23:59';

    if ($start_date && $end_date && $start_time && $end_time) {
        $start_datetime = $start_date . ' ' . $start_time;
        $end_datetime = $end_date . ' ' . $end_time;
        $query .= " AND u.create_date BETWEEN :start_datetime AND :end_datetime";
    }

    // 来場者のみを検索する場合
    if ($attended_only) {
        $query .= " AND a.main IS NOT NULL";
    }

    $query .= " ORDER BY u.id ASC";
    $stmt = $pdo->prepare($query);

    // パラメータバインディング
    if ($start_date && $end_date && $start_time && $end_time) {
        $stmt->bindParam(':start_datetime', $start_datetime);
        $stmt->bindParam(':end_datetime', $end_datetime);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ユーザーの総数を取得
    $userCount = count($users);

} catch (PDOException $e) {
    echo "エラーが発生しました。";
    error_log("Error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>管理者ページ - メール一斉送信</title>
    <style>
    /* 既存のスタイルはそのまま維持 */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    /* コンテナスタイル */
    .container {
        max-width: 1200px;
        margin: 50px auto;
        padding: 50px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .date-filter-form {
        padding-bottom: 20px;
        text-align: center;
    }

    form {
        text-align: -webkit-center;
    }

    h1 {
        color: #333;
        margin-bottom: 30px;
        text-align: center;
    }

    /* テーブルのスタイル */
    table {
        width: auto;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        color: #333;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .info {
        font-size: 18px;
        margin-bottom: 20px;
        padding-top: 20px;
        font-weight: bold;
        color: #333;
        text-align: center;
    }

    /* ボタンのスタイル */
    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .button-container button {
        padding: 10px 40px;
        background-color: #f07844;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .button-container button:hover {
        background-color: #0056b3;
    }

    /* フレックスボックスを使用したヘッダー */
    .header {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 20px;
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
    </style>
    <script>
    // 全選択・全解除機能
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    }

    function confirmSendEmail() {
        // 選択したユーザー数を取得
        const selectedUsers = document.querySelectorAll('input[name="selected_users[]"]:checked').length;
        // メールの種類を取得
        const emailType = document.querySelector('input[name="email_type"]:checked').value;

        // メールタイプに応じた表示名
        let emailTypeName = '';
        if (emailType === 'pdf') {
            emailTypeName = '受講票メール';
        } else if (emailType === 'attention') {
            emailTypeName = '注目セッションメール';
        } else if (emailType === 'visitors') {
            emailTypeName = '来場者限定メール';
        }

        // 確認メッセージを表示
        return confirm('合計 ' + selectedUsers + ' 人に ' + emailTypeName + ' をお送りしてもよろしいでしょうか？');
    }

    function resetForm() {
        // 全ての入力フィールドをクリアする
        document.getElementById('start_date').value = '';
        document.getElementById('start_time').value = '00:00';
        document.getElementById('end_date').value = '';
        document.getElementById('end_time').value = '23:59';
        document.getElementById('attended_only').checked = false;
        document.getElementById('email_type_pdf').checked = true; // デフォルト選択をリセット
        document.getElementById('email_type_attention').checked = false;
        document.getElementById('email_type_visitors').checked = false;

        // フォームを送信して初期状態にリセットする
        document.querySelector('.date-filter-form').submit();
    }
    </script>
</head>

<body>
    <!-- ヘッダー -->
    <div class="header">
        <form method="post" action="adminMenu.php">
            <button type="submit">メニュー</button>
        </form>
        <form method="post">
            <button type="submit" name="logout">ログアウト</button>
        </form>
    </div>

    <div class="container">
        <h1>メール送信一覧</h1>

        <!-- 日付範囲フィルタ -->
        <form method="post" class="date-filter-form">
            <label for="start_date">開始日:</label>
            <input type="date" id="start_date" name="start_date"
                value="<?php echo htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="start_time">開始時間:</label>
            <input type="time" id="start_time" name="start_time"
                value="<?php echo htmlspecialchars($start_time, ENT_QUOTES, 'UTF-8'); ?>">

            <label for="end_date">終了日:</label>
            <input type="date" id="end_date" name="end_date"
                value="<?php echo htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="end_time">終了時間:</label>
            <input type="time" id="end_time" name="end_time"
                value="<?php echo htmlspecialchars($end_time, ENT_QUOTES, 'UTF-8'); ?>">

            <!-- 来場者フィルタ -->
            <label for="attended_only">来場者のみ表示:</label>
            <input type="checkbox" id="attended_only" name="attended_only" value="1"
                <?php echo $attended_only ? 'checked' : ''; ?>>

            <button type="submit">検索</button>
            <button type="button" class="reset-button" onclick="resetForm()">リセット</button>
        </form>

        <form action="" method="post" onsubmit="return confirmSendEmail();">
            <table>
                <tr>
                    <th><input type="checkbox" onclick="toggleSelectAll(this);"> 全選択</th>
                    <th>名前 (LoginID)</th>
                    <th>メールアドレス</th>
                    <th>登録日</th>
                    <th>出席時間</th>
                </tr>
                <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="selected_users[]"
                            value="<?php echo htmlspecialchars($user['loginID'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['surname'] . ' ' . $user['given_name'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($user['loginID'], ENT_QUOTES, 'UTF-8') . ')'; ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['work_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($user['create_date'])), ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['main'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5">指定された期間にユーザーが見つかりませんでした。</td>
                </tr>
                <?php endif; ?>
            </table>

            <div class="email-type-container">
                <label>メール選択:</label>
                <input type="radio" id="email_type_pdf" name="email_type" value="pdf" checked>
                <label for="email_type_pdf">受講票メール</label>
                <input type="radio" id="email_type_attention" name="email_type" value="attention">
                <label for="email_type_attention">注目セッションメール</label>
                <input type="radio" id="email_type_visitors" name="email_type" value="visitors">
                <label for="email_type_visitors">来場者限定メール</label>
            </div>

            <div class="info">
                メール送信人数は合計 <?php echo htmlspecialchars($userCount, ENT_QUOTES, 'UTF-8'); ?> 人となります。
            </div>

            <div class="button-container">
                <button type="submit" name="send_email">一斉送信</button>
            </div>
        </form>
    </div>
</body>

</html>