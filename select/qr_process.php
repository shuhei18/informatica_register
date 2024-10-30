<?php
session_start();  // セッションを開始

require 'config.php'; // データベース接続情報をインクルード

// セッション情報をチェック
if (isset($_GET['session'])) {
    $_SESSION['session'] = $_GET['session']; // セッション情報を保存
}

$sessionColumn = $_SESSION['session'] ?? null;  // セッション情報をセッションから取得

// 初期設定の変数
$backgroundColor = '#f4f4f9'; // デフォルトの背景色
$reservationStatus = ''; // デフォルトの予約ステータス


// QRコードのデータとセッション情報を取得
if (isset($_GET['data']) && $sessionColumn) {
    $qrData = $_GET['data'];

    // データベース接続
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // QRコードのデータに基づいてユーザー情報とセッション情報を取得するクエリを作成
        $stmt = $pdo->prepare("
            SELECT 
                u.loginID, 
                u.corporate_type,
                u.corporate_prefix,
                u.company_name, 
                u.surname, 
                u.given_name, 
                u.surname_kana, 
                u.given_name_kana,
                ul.lecture_time,
                ul.lecture_title,
                ul.hall
            FROM 
                users u
            JOIN 
                user_lectures ul ON u.loginID = ul.loginID
            WHERE 
                u.loginID = :loginID
        ");
        $stmt->bindParam(':loginID', $qrData);
        $stmt->execute();

 // ユーザー情報とセッション情報を取得
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);  // 複数のセッション情報を取得

        if ($user) {
            // corporate_type と company_name の結合を行う
            $company = trim($user[0]['corporate_prefix']) === '前' 
                ? trim($user[0]['corporate_type']) . trim($user[0]['company_name']) 
                : (trim($user[0]['corporate_prefix']) === '後' 
                    ? trim($user[0]['company_name']) . trim($user[0]['corporate_type']) 
                    : (trim($user[0]['corporate_prefix']) === 'なし' 
                        ? trim($user[0]['company_name']) 
                        : trim($user[0]['company_name'])));

            // 検索したユーザー情報を表示するために変数にデータを格納
            $loginID = htmlspecialchars($user[0]['loginID'], ENT_QUOTES, 'UTF-8');
            $recipientName = htmlspecialchars($user[0]['surname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user[0]['given_name'], ENT_QUOTES, 'UTF-8');
            $recipientNameKana = htmlspecialchars($user[0]['surname_kana'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user[0]['given_name_kana'], ENT_QUOTES, 'UTF-8');
            $lectureTime = htmlspecialchars($user[0]['lecture_time'], ENT_QUOTES, 'UTF-8');
            $hall = htmlspecialchars($user[0]['hall'], ENT_QUOTES, 'UTF-8');

            // ここに追加
          // mainセッションの場合の処理を追加
            if ($sessionColumn === 'main') {
                // user_lecturesにloginIDが存在するか確認
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM user_lectures WHERE loginID = :loginID
                ");
                $stmt->bindParam(':loginID', $loginID);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $backgroundColor = 'lightgreen';  // 背景色を緑に変更
                    $reservationStatus = '事前登録者';   // 予約済みと表示
                } else {
                    $backgroundColor = 'red';  // 背景色を赤に変更
                    $reservationStatus = '未登録';  // 未予約と表示
                }
            } else {
                // それ以外のセッションの処理（mini_a_1 〜 mini_b_2 の場合）
                $isReserved = false; // 初期状態では予約なしとする

                // 複数のセッション情報（講演タイトル）がある場合、それをループで確認する
                foreach ($user as $session) {
                    $lectureTitle = htmlspecialchars($session['lecture_title'], ENT_QUOTES, 'UTF-8');

                    // セッションごとの条件をチェック
                    if (
                        ($sessionColumn === 'mini_a_1' && strpos($lectureTitle, 'NTTデータ') !== false) ||
                        ($sessionColumn === 'mini_a_2' && strpos($lectureTitle, 'データの在処') !== false) ||
                        ($sessionColumn === 'mini_a_3' && strpos($lectureTitle, '未来を開拓するデータ') !== false) ||
                        ($sessionColumn === 'mini_b_1' && strpos($lectureTitle, 'DX成功のカギ！') !== false) ||
                        ($sessionColumn === 'mini_b_2' && strpos($lectureTitle, 'データ活用に悩む方必見！') !== false)
                    ) {
                        $backgroundColor = 'lightgreen';  // 背景色を黄緑に変更
                        $reservationStatus = '予約済み';   // 予約済みと表示
                        $isReserved = true; // 予約ありに設定
                        break;  // 条件が満たされたらループを終了
                    }
                }

                // 予約がない場合、背景色をオレンジにし、未予約と表示
                if (!$isReserved) {
                    $backgroundColor = 'orange';  // 背景色をオレンジに変更
                    $reservationStatus = '未登録';  // 未予約と表示
                }
            }
        } else {
            // ユーザーが見つからなかった場合のメッセージ
            $errorMessage = "ユーザー情報が見つかりませんでした。";
$backgroundColor = 'red';  // 背景色を赤に変更
$reservationStatus = 'エラー';  
        }
    } catch (PDOException $e) {
        $errorMessage = "データベース接続エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
$backgroundColor = 'red';  // 背景色を赤に変更
$reservationStatus = 'エラー';
    }
} else {
    $errorMessage = "QRコードのデータまたはセッション情報が送信されていません。";
    $backgroundColor = 'red';  // 背景色を赤に変更
    $reservationStatus = 'エラー';
}

// 登録ボタンが押された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $loginID = $_POST['loginID'];  // QRから取得されたユーザーのloginID
    $comment = $_POST['memo'];  // ユーザーが入力したメモ

    // セッションカラム名と対応するコメントカラムのマッピング
    $sessionCommentMap = [
        'main' => 'main_comment',
        'mini_a_1' => 'comment_a1',
        'mini_a_2' => 'comment_a2',
        'mini_a_3' => 'comment_a3',
        'mini_b_1' => 'comment_b1',
        'mini_b_2' => 'comment_b2'
    ];

    // セッションカラム名と対応するコメントカラム名を取得
    $commentColumn = $sessionCommentMap[$sessionColumn] ?? null;

    if (!$commentColumn) {
        die("無効なセッション情報です。");
    }

    // 出欠情報を挿入または更新するための処理
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // attendanceテーブルに出欠情報を挿入または更新
        $stmt = $pdo->prepare("
            INSERT INTO attendance (loginID, $sessionColumn, $commentColumn) 
            VALUES (:loginID, NOW(), :comment)
            ON DUPLICATE KEY UPDATE $sessionColumn = NOW(), $commentColumn = :comment
        ");
        $stmt->bindParam(':loginID', $loginID);
        $stmt->bindParam(':comment', $comment);
        $stmt->execute();

        $successMessage = "登録完了しました！引き続きQRを読み取りしてください。";
    } catch (PDOException $e) {
        $errorMessage = "出欠確認エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// 取り消しボタンが押された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmCancel'])) {
    $loginID = $_POST['loginID'];  // QRから取得されたユーザーのloginID
    $sessionColumn = $_POST['session'];  // URLから渡されたセッションカラム名（main, mini_a_1 など）
    $comment = $_POST['memo'];  // 取り消し時のコメントを取得

    // セッションカラム名と対応するコメントカラムのマッピング
    $sessionCommentMap = [
        'main' => 'main_comment',
        'mini_a_1' => 'comment_a1',
        'mini_a_2' => 'comment_a2',
        'mini_a_3' => 'comment_a3',
        'mini_b_1' => 'comment_b1',
        'mini_b_2' => 'comment_b2'
    ];

    // セッションカラム名と対応するコメントカラム名を取得
    $commentColumn = $sessionCommentMap[$sessionColumn] ?? null;

    if (!$commentColumn) {
        die("無効なセッション情報です。");
    }

    // 出欠情報を削除（日時のみ削除、コメントは保持）
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // attendanceテーブルからセッションの日時を削除し、コメントは更新
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET $sessionColumn = NULL, $commentColumn = :comment 
            WHERE loginID = :loginID
        ");
        $stmt->bindParam(':loginID', $loginID);
        $stmt->bindParam(':comment', $comment);
        $stmt->execute();

        $cancelMessage = "出欠情報が取り消され、コメントが更新されました。";
    } catch (PDOException $e) {
        $errorMessage = "出欠取消エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// セッション名に対応する表示用の名前を定義
$sessions = [
    'main' => 'メインセッション',
   'mini_a_1' => 'ミニセッションA-1<br>NTTデータ<br>【現場技術者が語る！<br>NTTデータのインフォマティカプロジェクトでのチャレンジ！】',
    'mini_a_2' => 'ミニセッションA-2<br>NTT Com<br>【「データの在処」と「人」をつなぐ<br>ICT基盤の現在地】',
    'mini_a_3' => 'ミニセッションA-3<br>ALSI_2<br>【未来を開拓する<br>データ流通・活用基盤「Xzilla」】',
    'mini_b_1' => 'ミニセッションB-1<br>SCSK<br>【DX成功のカギ！<br>データをスピーディーな意思決定に活かすには】',
    'mini_b_2' => 'ミニセッションB-2<br>CTC<br>【データ活用に悩む方必見！<br>スモールスタートで始めるデータ活用】'
];



// 表示用のセッション名を取得
$selectedSessionName = $sessions[$_SESSION['session']] ?? 'セッション未選択';

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコードスキャン結果</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: <?php echo $backgroundColor;
        ?>;
        /* 背景色をPHPで設定 */
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .container {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 20px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    h1 {
        text-align: center;
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
    }

    .info-box {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        font-size: 16px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .info-box p {
        margin: 5px 0;
        font-size: 14px;
        color: #555;
    }

    .status {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-top: 20px;
    }

    .button-container {
        display: flex;

        align-items: center;
        margin-top: 20px;
        flex-wrap: no-wrap;
        justify-content: center;
        /*中央揃え*/
        justify-content: space-between;
        /*均等に間隔をあける*/
    }

    button,
    .back-button {
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        width: 200px;
        transition: background-color 0.3s ease;
        text-align: center;
        margin-bottom: 15px;
    }

    button {
        background-color: #007bff;
        color: white;
    }

    button:hover {
        background-color: #0056b3;
    }

    .back-button {
        background-color: #6c757d;
        color: white;
        display: block;
    }

    .back-button:hover {
        background-color: #565e64;
    }

    input[type="text"] {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
        box-sizing: border-box;
        background-color: #f9f9f9;
    }

    input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
        background-color: #fff;
    }

    .accordion {
        background-color: #e49943;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        cursor: pointer;
        font-size: 16px;
        text-align: center;
        margin-bottom: 10px;
        width: 100%;
    }

    .panel {
        padding: 0 15px;
        background-color: white;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.2s ease-out;
        border: 1px solid #ddd;
        border-top: none;
        margin-bottom: 20px;
    }

    .panel p {
        color: #333;
        margin: 5px 0;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        max-width: 400px;
        text-align: center;
    }

    .modal-content p {
        color: red;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .modal-content button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .modal-content button:hover {
        background-color: #0056b3;
    }

    @media (max-width: 480px) {
        .container {
            padding: 15px;
        }

        h1 {
            font-size: 16px;
        }

        .info-box p {
            font-size: 12px;
        }

        input[type="text"] {
            font-size: 14px;
        }

        .button-container button,
        .back-button {
            font-size: 14px;
            width: 150px;

        }

        .accordion {
            font-size: 14px;
        }

        .panel {
            font-size: 12px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <h1><?php echo $sessions[$_SESSION['session']]; ?></h1>




        <!-- 予約ステータス表示 -->
        <div class="status">
            <?php echo $reservationStatus; ?>
        </div>

        <?php if (isset($errorMessage)): ?>
        <div class="info-box">
            <p><?php echo $errorMessage; ?></p>
        </div>
        <!-- QRボタンをエラー発生時に表示 -->
        <div class="button-container">
            <button id="closeModal">QRコード読み取り</button>
        </div>
        <div class="button-container">
            <a href="main_reception.php" class="back-button">セッション選択に戻る</a>
        </div>
        <?php else: ?>
        <div class="info-box">
            <p>ログインID: <?php echo $loginID; ?></p>
            <p>会社名: <?php echo $company; ?></p>
            <p>お名前: <?php echo $recipientName; ?></p>
            <p>カナ: <?php echo $recipientNameKana; ?></p>
        </div>

        <form action="" method="post">
            <input type="hidden" name="loginID" value="<?php echo $loginID; ?>">
            <input type="hidden" name="session"
                value="<?php echo htmlspecialchars($_GET['session'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="memo">メモ入力欄</label>
            <input type="text" id="memo" name="memo" placeholder="メモを入力">
            <div class="button-container">
                <button type="submit" name="register">登録</button>
                <button type="button" id="cancelBtn" style="background-color:red;">取消</button>
            </div>
        </form>

        <!-- セッション情報を1つのアコーディオンで表示 -->
        <button class="accordion">登録セッション情報を表示</button>
        <div class="panel">
            <?php foreach ($user as $session): ?>
            <p><strong>講演タイトル:</strong> <?php echo htmlspecialchars($session['lecture_title'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <p><strong>講演時間:</strong> <?php echo htmlspecialchars($session['lecture_time'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>会場:</strong>
                <?php
            // ホール名を対応するセッション名に変換
            switch ($session['hall']) {
                case '1st-hall':
                    echo 'メインセッション';
                    break;
                case '2nd-hall':
                    echo 'ミニセッションA';
                    break;
                case '3rd-hall':
                    echo 'ミニセッションB';
                    break;
                default:
                    echo htmlspecialchars($session['hall'], ENT_QUOTES, 'UTF-8'); // デフォルトはそのまま表示
                    break;
            }
            ?>
            </p>
            <hr> <!-- セッションごとの区切り線（必要に応じて追加） -->
            <?php endforeach; ?>
        </div>
        <div class="button-container">
            <a href="main_reception.php" class="back-button">セッション選択に戻る</a>
        </div>

        <!-- 成功メッセージのモーダル -->
        <div id="successModal" class="modal">
            <div class="modal-content">
                <p>登録完了しました！引き続きQRを読み取りしてください。</p>
                <button id="closeModal">QRボタン</button>
            </div>
        </div>
        <!-- 取り消し確認のモーダル -->
        <div id="cancelConfirmModal" class="modal">
            <div class="modal-content">
                <p>登録された出欠情報を削除しますよろしいですか？</p>
                <form action="" method="post">
                    <input type="hidden" name="loginID" value="<?php echo $loginID; ?>">
                    <input type="hidden" name="session"
                        value="<?php echo htmlspecialchars($_GET['session'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="memo" id="cancelMemo">
                    <button type="submit" name="confirmCancel" style="background-color:red;">取消</button>
                </form>
                <!-- キャンセルボタンを追加 -->
                <button id="cancelModalBtn">キャンセル</button>
            </div>
        </div>

        <!-- 取り消し成功メッセージのモーダル -->
        <div id="cancelSuccessModal" class="modal">
            <div class="modal-content">
                <p>出欠情報が取り消されました！</p>
                <button id="closeCancelModal">戻る</button>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script>
    // アコーディオン機能のためのJavaScript
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    }

    // 登録成功モーダル表示処理
    <?php if (isset($successMessage)): ?>
    document.getElementById('successModal').style.display = 'flex';
    <?php endif; ?>

    // 取り消し成功モーダル表示処理
    <?php if (isset($cancelMessage)): ?>
    document.getElementById('cancelSuccessModal').style.display = 'flex';
    <?php endif; ?>

    // モーダルの閉じる処理
    document.getElementById('closeModal').addEventListener('click', function() {
        window.location.href = 'qr_scan.php';
    });

    document.getElementById('closeCancelModal').addEventListener('click', function() {
        window.location.href = 'qr_scan.php';
    });

    // 取り消し確認モーダルの表示
    document.getElementById('cancelBtn').addEventListener('click', function() {
        document.getElementById('cancelMemo').value = document.getElementById('memo').value; // コメントをモーダルに渡す
        document.getElementById('cancelConfirmModal').style.display = 'flex';
    });


    // キャンセルボタンが押されたらモーダルを閉じる処理
    document.getElementById('cancelModalBtn').addEventListener('click', function() {
        document.getElementById('cancelConfirmModal').style.display = 'none';
    });
    </script>
</body>

</html>