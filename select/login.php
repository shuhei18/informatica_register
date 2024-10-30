<?php
require 'config.php'; // データベース設定を含むファイルを読み込む
require 'vendor/autoload.php'; // AWS SDKの読み込み

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

session_start(); // セッションの開始

// ログイン処理を行う部分
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false];

    if (isset($_POST['loginID']) && isset($_POST['password'])) {
        $loginIDOrEmail = $_POST['loginID'];  // loginID または work_email
        $password = $_POST['password'];

        if (empty($loginIDOrEmail) || empty($password)) {
            $response['error'] = "ログインID（またはメールアドレス）とパスワードを入力してください。";
            echo json_encode($response);
            exit;
        }

        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
            // loginID または work_email で検索できるように SQL を修正
            $stmt = $pdo->prepare("SELECT * FROM users WHERE loginID = ? OR work_email = ?");
            $stmt->execute([$loginIDOrEmail, $loginIDOrEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['loginID'] = $user['loginID'];
                $response['success'] = true;
            } else {
                $response['error'] = "ログインID（またはメールアドレス）またはパスワードが間違っています。";
            }
        } catch (PDOException $e) {
            $response['error'] = "データベースクエリに失敗しました。エラー: {$e->getMessage()}";
        }

        echo json_encode($response);
        exit;
    }

    if (isset($_POST['reset_loginID']) && isset($_POST['reset_email'])) {
        $loginID = $_POST['reset_loginID'];
        $email = $_POST['reset_email'];

        if (empty($loginID) || empty($email)) {
            $response['error'] = "ログインIDとメールアドレスを入力してください。";
            echo json_encode($response);
            exit;
        }

        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
            $stmt = $pdo->prepare("SELECT * FROM users WHERE loginID = ? AND work_email = ?");
            $stmt->execute([$loginID, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // トークン生成と保存
                $resetToken = bin2hex(random_bytes(16)); // 32文字のトークンを生成
                $expires_at = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s'); // 1時間後

                $stmt = $pdo->prepare("INSERT INTO password_resets (token, loginID, expires_at, created_at) VALUES (:token, :loginID, :expires_at, NOW())");
                $stmt->execute([
                    'token' => $resetToken,
                    'loginID' => $loginID,
                    'expires_at' => $expires_at
                ]);

                // パスワードリセットリンク生成
                $resetUrl = "https://s-bev.jp/reset_password.php?token={$resetToken}";

                $sender_name = "Informatica World Tour 2024 事務局";
                $sender_email = "iwt2024_registration@s-bev.jp";
                $subject = "【Informatica World Tour 2024】パスワードのご案内";
                $message = "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【Informatica World Tour 2024】パスワード再設定URL発行のご案内
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

パスワード再設定URL：
{$resetUrl}

上記にアクセスしてパスワードの再設定を行ってください。
アクセス有効期限は1時間以内です。

＜お問い合わせ＞
Informatica World Tour 2024 事務局
◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内）
　Email：iwt2024_registration@s-bev.jp

◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内）
　Email：iwt2024@jevent.jp
";

                // SESクライアントの初期化
                $sesClient = new SesClient([
                    'version' => 'latest',
                    'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
                    'credentials' => [
                        'key'    => 'AKIAXF53I65F66G6TK6R',
                        'secret' => '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',
                    ],
                ]);

                try {
                    $result = $sesClient->sendEmail([
                        'Source' => "Informatica World Tour 2024 事務局 <iwt2024_registration@s-bev.jp>",
                        'Destination' => [
                            'ToAddresses' => [$user['work_email']],
                        ],
                        'Message' => [
                            'Subject' => [
                                'Data' => $subject,
                                'Charset' => 'UTF-8',
                            ],
                            'Body' => [
                                'Text' => [
                                    'Data' => $message,
                                    'Charset' => 'UTF-8',
                                ],
                            ],
                        ],
                    ]);
                    $response['success'] = true;
                    $response['message'] = "パスワードリセットの案内をメールで送信しました。";
                } catch (AwsException $e) {
                    $response['error'] = "メール送信中にエラーが発生しました: " . $e->getMessage();
                }
            } else {
                $response['error'] = "該当するユーザーが見つかりません。";
            }
        } catch (PDOException $e) {
            $response['error'] = "データベースエラー: " . $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }
}
?>


<?php
// header部分
$header = file_get_contents('header.html');
if ($header === FALSE) {
    die("header.htmlを読み込めませんでした。");
}
// 読み込んだHTMLを結合して表示
echo $header;
?>
<title>ログイン</title>
<style>
/* 新規登録リンクのスタイル */
.register-link {
    margin-bottom: 20px;
    text-align: center;
}

.register-link a {
    text-decoration: none;
    font-weight: bold;
}

.register-link a:hover {
    text-decoration: underline;
}
</style>

<div class="profileform-container">
    <h1 class="profileform-text">ログイン</h1>
    <!-- 新規登録リンクを追加 -->
    <div class="register-link">
        <a href="privacy_new.php">新規登録はこちら</a>
    </div>
    <form id="loginForm" onsubmit="return login(event)">
        <div>
            <label for="loginID">ログインIDまたはメールアドレス:</label>
            <input type="text" id="loginID" name="loginID" required>
        </div>
        <div>
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="select-btn-box">
            <button type="submit" class="select-btn">ログイン</button>
        </div>
    </form>
    <div class="select-btn-box">
        <button onclick="showResetForm()" class="select-btn-2">パスワードを忘れた方</button>
    </div>
    <div id="resetForm" style="display:none;">
        <h1 class="profileform-text" style="margin-top:15px">パスワードリセット</h1>
        <p>パスワードを忘れた方は、以下の情報を入力してください。登録したメールアドレスにパスワードリセットの指示を送ります。</p>
        <form onsubmit="return resetPassword(event)">
            <input type="text" id="reset_loginID" name="reset_loginID" placeholder="ログインID" required>
            <input type="email" id="reset_email" name="reset_email" placeholder="メールアドレス" required>
            <div class="select-btn-box">
                <button type="submit" class="select-btn">送信</button>
            </div>
        </form>
    </div>
</div>

<script>
function login(event) {
    event.preventDefault();
    const loginID = document.getElementById('loginID').value;
    const password = document.getElementById('password').value;

    const formData = new URLSearchParams();
    formData.append('loginID', loginID);
    formData.append('password', password);

    fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'toppage.php';
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function showResetForm() {
    var form = document.getElementById('resetForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function resetPassword(event) {
    event.preventDefault();
    const loginID = document.getElementById('reset_loginID').value;
    const email = document.getElementById('reset_email').value;

    const formData = new URLSearchParams();
    formData.append('reset_loginID', loginID);
    formData.append('reset_email', email);

    fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
</script>
</body>

</html>