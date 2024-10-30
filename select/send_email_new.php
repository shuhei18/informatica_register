<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'vendor/autoload.php';
require 'config.php'; 
require 'phpqrcode/qrlib.php'; 

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;


session_start();

header('Content-Type: application/json');
$response = ['success' => false];
ob_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_SESSION['user'] ?? [];

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // メールアドレスの重複チェック
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE work_email = :email");
        $stmt->execute(['email' => $user['work_email']]);
        $emailCount = $stmt->fetchColumn();

        if ($emailCount > 0) {
            // 重複があれば、セッション変数を設定し、thank_you.phpにリダイレクト
            $_SESSION['email_exists'] = true;
            header("Location: thank_you.php");
            exit;
        } else {
            // 新規登録の場合のみ以下の処理を実行
            $_SESSION['email_exists'] = false;
            // 新規ユーザーの挿入処理をここで実行
            $surname = $user['surname'] ?? null;
            $given_name = $user['given_name'] ?? null;
            $surname_kana = $user['surname_kana'] ?? null;
            $given_name_kana = $user['given_name_kana'] ?? null;
            $corporate_type = $user['corporate_type'] ?? null;
            $corporate_prefix = $user['corporate_prefix'] ?? null;
            $company_name = $user['company_name'] ?? null;
            $company_name_kana = $user['company_name_kana'] ?? null;
            $department_name = $user['department_name'] ?? null;
            $position_name = $user['position_name'] ?? null;
            $postal_code1 = $user['postal_code1'] ?? null;
            $postal_code2 = $user['postal_code2'] ?? null;
            $prefecture = $user['prefecture'] ?? null;
            $city = $user['city'] ?? null;
            $town = $user['town'] ?? null;
            $street = $user['street'] ?? null;
            $building = $user['building'] ?? null;
            $phone1 = $user['phone1'] ?? null;
            $phone2 = $user['phone2'] ?? null;
            $phone3 = $user['phone3'] ?? null;
            $work_email = $user['work_email'] ?? null;
            $password = password_hash($user['password'], PASSWORD_DEFAULT);
            $industry_category = $user['industry_category'] ?? null;
            $department_category = $user['department_category'] ?? null;
            $position_category = $user['position_category'] ?? null;
            $employees_count = $user['employees_count'] ?? null;
            $annual_revenue = $user['annual_revenue'] ?? null;
            $event_involvement = $user['event_involvement'] ?? null;
            $event_involvement_other = $user['event_involvement_other'] ?? null;

            $postal_code = $postal_code1 . '-' . $postal_code2;
            $phone = $phone1 . '-' . $phone2 . '-' . $phone3;
            $loginID = generateLoginID($phone);

            // データベースへの挿入処理
            $utm_source = $_SESSION['utm_source'] ?? '';
            $utm_medium = $_SESSION['utm_medium'] ?? '';
            $utm_campaign = $_SESSION['utm_campaign'] ?? '';
            $utm_campaign = $utm_source . '_' . $utm_medium . '_' . $utm_campaign;
            $current_datetime = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("INSERT INTO users (
                utm_campaign,
                surname, 
                given_name, 
                surname_kana, 
                given_name_kana, 
                corporate_type, 
                corporate_prefix, 
                company_name, 
                company_name_kana, 
                department_name, 
                position_name, 
                postal_code, 
                prefecture, 
                city, 
                town, 
                street, 
                building, 
                phone, 
                work_email, 
                password, 
                loginID, 
                create_date, 
                industry_category, 
                department_category, 
                position_category, 
                employees_count, 
                annual_revenue, 
                event_involvement, 
                event_involvement_other)
                
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $utm_campaign,
                    $surname, 
                    $given_name, 
                    $surname_kana, 
                    $given_name_kana, 
                    $corporate_type, 
                    $corporate_prefix,
                    $company_name, 
                    $company_name_kana, 
                    $department_name, 
                    $position_name, 
                    $postal_code, 
                    $prefecture,
                    $city, 
                    $town, 
                    $street, 
                    $building, 
                    $phone, 
                    $work_email, 
                    $password, 
                    $loginID, 
                    $current_datetime,
                    $industry_category, 
                    $department_category, 
                    $position_category, 
                    $employees_count, 
                    $annual_revenue,
                    $event_involvement, 
                    $event_involvement_other
                ]);

        }

        $fullName = trim($user['surname']) . ' ' . trim($user['given_name']);
        $corporateDisplayName = $corporate_prefix === '前' ? $corporate_type . '' . $company_name : ($corporate_prefix === '後' ? $company_name . '' . $corporate_type : $company_name);

        // メール送信
        sendEmailWith($user['work_email'], $loginID, $fullName, $corporateDisplayName);

        $response['success'] = true;
        ob_end_clean();

        // 結果に基づいてリダイレクト
        header("Location: thank_you.php");
        exit;

    } catch (PDOException $e) {
        $response['error'] = "Failed to save to database. Error: {$e->getMessage()}";
    } catch (AwsException $e) {
        $response['error'] = "Failed to send email. AWS SES Error: {$e->getMessage()}";
    }
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
        

// PDFをメールに添付して送信する関数
function sendEmailWith($recipientEmail, $loginID, $fullName, $corporateDisplayName) {
    $client = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
        'credentials' => [
            'key'    => 'AKIAXF53I65F66G6TK6R',
            'secret' => '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',
        ],
    ]);

    $subject = "【Informatica World Tour 2024】登録完了のご連絡";
    $message = "以下の内容で登録が完了しました。\n\n";
    $message .= "登録ID: $loginID\n";
    $message .= "企業名: $corporateDisplayName\n";

    $message = <<<EOT
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ 
    【Informatica World Tour 2024】 登録完了のご連絡 
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ 
    このたびは、「Informatica World Tour 2024」にお申し込みいただきまして、 
    誠にありがとうございます。 
    お客様のご登録が完了いたしましたのでご連絡申し上げます。 

    ＜マイページ＞ 
    下記URLよりアクセスいただき、お客様情報に記載されている[ID]番号とご登録いただいた[パスワード]をご入力の上 
    マイページへログインしてください。 
    URL：https://s-bev.jp/login.php 

    ご登録いただいた[パスワード]が不明の場合は、マイページのログイン画面にあります 
    「パスワードを忘れた方」よりお問い合わせください。 

    ＜お客様情報＞ 
    ---------------------------------------------------------------------- 
    [ID] {$loginID}
    [会社名] {$corporateDisplayName}
    [氏名] {$fullName}
    ---------------------------------------------------------------------- 

    ＜Informatica World Tour 2024 開催概要＞ 
    会期：2024年9月13日(金) 13:30-18:00（13:00- 受付/展示オープン） 
    会場：大手町プレイス ホール&カンファレンス（2F 受付） 
        　　　東京都千代田区大手町2-3-1大手町プレイス(イーストタワー) 1F/2F 
    アクセス：東京メトロ・都営地下鉄 「大手町」駅 A5出口直結 
    https://otemachi-place-hc.jp/access.html 

    ＜お問い合わせ＞ 
    Informatica World Tour 2024 事務局 
    ◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内） 
        Email：iwt2024_registration@s-bev.jp 
        
    ◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内） 
        Email：iwt2024@jevent.jp 
    EOT;

    $mail = $client->sendRawEmail([
        'RawMessage' => [
           'Data' => buildRawEmail($recipientEmail, $recipientName, $subject, $message),
        ],
    ]);
}

function buildRawEmail($toEmail, $fullName, $subject, $bodyText) {
    $boundary = uniqid(rand(), true);
    $sender_name = "Informatica World Tour 2024 事務局";
    $sender_email = "iwt2024_registration@s-bev.jp";
    $bcc_email = "bcc@example.com"; // BCCに追加するメールアドレス
    $encodedFromName = "=?UTF-8?B?" . base64_encode($sender_name) . "?=";

    $rawEmail = "From: $encodedFromName <$sender_email>\r\n";
    $rawEmail .= "To: {$fullName} <{$toEmail}>\r\n";
    $rawEmail .= "BCC: $bcc_email\r\n";
    $rawEmail .= "Subject: {$subject}\r\n";
    $rawEmail .= "MIME-Version: 1.0\r\n";
    $rawEmail .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

    $rawEmail .= "--{$boundary}\r\n";
    $rawEmail .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $rawEmail .= $bodyText . "\r\n\r\n";

    $rawEmail .= "--{$boundary}--";

    return $rawEmail;
}

function generateLoginID($phone) {
    $randomStr = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    $phoneSuffix = substr($phone, -4);
    return $randomStr . $phoneSuffix;
}
?>