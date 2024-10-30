<?php
require_once 'config.php';
require 'vendor/autoload.php'; // AWS SDKをロード

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$sent_count = 0; // 送信カウントの初期化

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
                // ユーザー情報取得
                $stmt = $pdo->prepare("SELECT * FROM users WHERE loginID = :loginID");
                $stmt->execute(['loginID' => $loginID]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    echo "ログインIDが見つかりませんでした: " . htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8') . "<br>";
                    continue;
                }

                // surname と given_name を結合して $recipientName を作成
                $recipientName = trim($user['surname']) . ' ' . trim($user['given_name']);

                // corporate_type を company_name に結合
                $company = trim($user['corporate_prefix']) === '前' 
                    ? trim($user['corporate_type']) . trim($user['company_name']) 
                    : (trim($user['corporate_prefix']) === '後' 
                        ? trim($user['company_name']) . trim($user['corporate_type']) 
                        : (trim($user['corporate_prefix']) === 'なし' 
                            ? trim($user['company_name']) 
                            : trim($user['company_name'])));

                // メール送信
                sendEmail($user['work_email'], $recipientName, $loginID, $company);
                $sent_count++; // 送信カウントをインクリメント
            }

            // バッチごとに1秒の遅延を入れる
            sleep(1);
        }

        // 送信したユーザー数をセッションに保存
        $_SESSION['total_sent'] = $sent_count;
        
        echo "ユーザーにメールが送信されました。";
    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
}

            

function sendEmail($recipientEmail, $recipientName, $loginID, $company) {
    $client = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
        'credentials' => [
            'key'    => 'AKIAXF53I65F66G6TK6R',
            'secret' => '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',
        ],
    ]);

    $subject = "【Informatica World Tour 2024】 来週 9/13(金) 開催です！";
    $bodyText = "
━━━━━━━━━━━━━━━━━━━━━ 
【Informatica World Tour 2024】 来週 9/13(金) 開催です！ 
━━━━━━━━━━━━━━━━━━━━━
このたびは、「Informatica World Tour 2024」にお申し込みいただきまして、誠にありがとうございます。
いよいよ、来週9/13(金) 開催となりました。皆様のご来場をお待ちしております！ 

＜Informatica World Tour 2024 開催概要＞
会期：2024年9月13日(金) 13:30-18:00（13:00- 受付/展示オープン）
会場：大手町プレイス ホール&カンファレンス（2F 受付）
東京都千代田区大手町2-3-1大手町プレイス(イーストタワー) 1F/2F
アクセス：東京メトロ・都営地下鉄 「大手町」駅 A5出口直結
https://otemachi-place-hc.jp/access.html

＜お客様情報＞ 
[ID] $loginID
[会社名]  $company
[氏名]  $recipientName

＜マイページ＞
受講票は、マイページからダウンロードが可能です。下記URLよりアクセスいただき、お客様情報に記載されている[ID]番号とご登録いただいた[パスワード]をご入力の上、マイページへログインしてください。
URL：https://s-bev.jp/login.php 
ご登録いただいた[パスワード]が不明の場合は、マイページのログイン画面にあります「パスワードを忘れた方」よりお問い合わせください。 

━━━━━━━━━━━━━━━━━
【注目ユーザー事例講演】
━━━━━━━━━━━━━━━━━
◆データ連携基盤の統合/拡大、データマネジメントヘ  
中部電力株式会社 / 株式会社中電シーティーアイ 
￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣
◆SUBARU流 全社データ活用で笑顔を作る 「モノづくり革新」 と 「価値づくり」 
株式会社SUBARU 
￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣
━━━━━━━━━━━━━━━━━
【注目ユーザー事例講演】
━━━━━━━━━━━━━━━━━
◆データが切り拓く生成AIの未来 〜Everybody’s ready for AI except your data〜 
インフォマティカ・ジャパン株式会社 
代表取締役社長
小澤 泰斗 

━━━━━━━━━━━━━━━━━
【展示会場】 13:00-18:00 
━━━━━━━━━━━━━━━━━
展示コーナーでは、インフォマティカによる生成AI対応の最新データマネジメントソリューションを 
デモを交えてご紹介します。さらに、スポンサー各社による、DXを推進するデータ連携・利活用の 
導入事例やサービス等、お客様の課題解決となる多彩なソリューションをご覧いただけます。 

＜お問い合わせ＞
Informatica World Tour 2024 事務局
◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内）
　Email：iwt2024_registration@s-bev.jp
◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内）
　Email：iwt2024@jevent.jp

";

    $mail = $client->sendRawEmail([
        'RawMessage' => [
           'Data' => buildRawEmail($recipientEmail, $recipientName, $subject, $bodyText),
        ],
    ]);
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