<?php
require_once 'config.php';
require 'vendor/autoload.php'; // AWS SDKをロード

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$sent_count = 0; // 送信カウントの初期化
$error_occurred = false; // エラーの有無を追跡

// フォームが送信されたかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_users'])) {
    $selectedUsers = $_POST['selected_users'];
    $batchSize = 20; // 一度に送信するユーザー数
    $totalUsers = count($selectedUsers);
    $batches = ceil($totalUsers / $batchSize); // バッチの数を計算

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        for ($i = 0; $i < $batches; $i++) {
            $batchUsers = array_slice($selectedUsers, $i * $batchSize, $batchSize);

            foreach ($batchUsers as $loginID) {
                // ユーザー情報とattendanceテーブルのmainカラムをチェック
                $stmt = $pdo->prepare("
                    SELECT u.*, a.main 
                    FROM users u 
                    JOIN attendance a ON u.loginID = a.loginID 
                    WHERE u.loginID = :loginID AND a.main IS NOT NULL
                ");
                $stmt->execute(['loginID' => $loginID]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    echo "参加記録が見つかりませんでした、または出席している人がいません: " . htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8') . "<br>";
                    continue;
                }

                // surname と given_name を結合して $recipientName を作成
                $recipientName = trim($user['surname']) . ' ' . trim($user['given_name']);

                // メール送信
                try {
                    sendEmail($user['work_email'], $recipientName);
                    $sent_count++; // 送信カウントをインクリメント
                } catch (Exception $e) {
                    $error_occurred = true;
                    error_log("メール送信エラー: " . $e->getMessage());
                }
            }

            // バッチごとに1秒の遅延を入れる
            sleep(1);
        }

    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage();
        error_log("Error: " . $e->getMessage());
        $error_occurred = true;
    }

    // メール送信結果に応じたアラート表示
    if ($error_occurred) {
        echo "<script>alert('エラーが発生しました。送付人数は " . $sent_count . " 人です。');</script>";
    } else {
        echo "<script>alert('" . $sent_count . " 人に送信がされました。');</script>";
    }
}

function sendEmail($recipientEmail, $recipientName) {
    $client = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
        'credentials' => [
            'key'    => 'AKIAXF53I65F66G6TK6R',
            'secret' => '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',
        ],
    ]);

    $subject = "【Informatica World Tour 2024】 ご来場ありがとうございました。";
    $bodyText = "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【Informatica World Tour 2024】 ご来場ありがとうございました。
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

このたびは、「Informatica World Tour 2024」にご来場いただきまして、誠に有難うございました。
おかげさまで多くの皆様にご参加いただき、盛況のうちに終了することができました。
ご来場いただいた皆様に心より感謝申し上げます。
本イベントでご紹介いたしました、インフォマティカ及び、スポンサー各社の様々なデータマネジメントソリューションが、今後のお客様の課題解決にお役立ていただけましたら幸いです。

＜オンデマンド配信のご案内＞
当日のセッション収録動画を、オンデマンドにて配信いたします。
当日ご視聴いただけなかったセッション等がございましたら、ぜひこの機会にご覧ください。
配信の準備が整い次第、皆様へご案内いたします。

このたびは、ご来場いただきまして、誠に有難うございました。

＜お問い合わせ＞
Informatica World Tour 2024 事務局
◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内）
　Email：iwt2024_registration@s-bev.jp

◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内）
　Email：iwt2024@jevent.jp
";

try {
        $result = $client->sendRawEmail([
            'RawMessage' => [
                'Data' => buildRawEmail($recipientEmail, $recipientName, $subject, $bodyText),
            ],
        ]);
    } catch (AwsException $e) {
        throw new Exception("メール送信失敗: " . $e->getAwsErrorMessage());
    }
}



// 生のメールを構築する関数
function buildRawEmail($toEmail, $recipientName, $subject, $bodyText) {
    $boundary = uniqid(rand(), true);
    $sender_name = "Informatica World Tour 2024 事務局";
    $sender_email = "iwt2024_registration@s-bev.jp";
    $bcc_email = "greenjackal32@www342b.sakura.ne.jp"; // BCCに追加するメールアドレス
    $encodedFromName = "=?UTF-8?B?" . base64_encode($sender_name) . "?=";

    $rawEmail = "From: $encodedFromName <$sender_email>\r\n";
    $rawEmail .= "To: {$recipientName} <{$toEmail}>\r\n";
    $rawEmail .= "BCC: $bcc_email\r\n"; // BCCヘッダーを追加
    $rawEmail .= "Subject: {$subject}\r\n";
    $rawEmail .= "MIME-Version: 1.0\r\n";
    $rawEmail .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

    // プレーンテキストのボディ
    $rawEmail .= "--{$boundary}\r\n";
    $rawEmail .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $rawEmail .= $bodyText . "\r\n\r\n";

    
    $rawEmail .= "--{$boundary}--";

    return $rawEmail;
}

?>