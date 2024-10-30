<?php
require 'vendor/autoload.php';
require 'tcpdf/tcpdf.php'; 
require 'config.php'; 
require 'phpqrcode/qrlib.php'; 

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class CustomPDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

session_start();
// セッションの有効期限を10分に設定
$inactive = 1800; // 30分（秒単位）
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("Location: index.php"); // セッションが切れた場合はindex.phpにリダイレクト
        exit(); // 必ずexitで処理を終了させる
    }
}
$_SESSION['timeout'] = time();

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
            $lecture_times = $user['lecture_time'] ?? [];
            $lecture_titles = $user['lecture_title'] ?? [];
            $halls = $user['hall'] ?? [];
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

            foreach ($lecture_times as $key => $lecture_time) {
                $lecture_title = $lecture_titles[$key];
                $hall = $halls[$key];
                $stmt = $pdo->prepare("INSERT INTO user_lectures (lecture_time, lecture_title, hall, loginID) VALUES (?, ?, ?, ?)");
                $stmt->execute([$lecture_time, $lecture_title, $hall, $loginID]);
            }
        }

        // 講演情報を取得する
        $stmt = $pdo->prepare("SELECT lecture_time, lecture_title, hall FROM user_lectures WHERE loginID = ?");
        $stmt->execute([$loginID]);
        $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $corporateDisplayName = $corporate_prefix === '前' ? $corporate_type . '' . $company_name : ($corporate_prefix === '後' ? $company_name . '' . $corporate_type : $company_name);

        // PDF生成
        $pdf = new CustomPDF();
        $pdf->AddPage();
        $pdf->SetFont('meiryo01', '', 10);

        // 四分割の線を引く
        $pdf->Line(105, 0, 105, 297);
        $pdf->Line(0, 148.5, 210, 148.5);

        // QRコード生成
        $qrCodeFile = '/tmp/qrcode.png';
        QRcode::png($loginID, $qrCodeFile);

        // 左上のブロック
        $pdf->SetXY(0, 0);
        $pdf->Image($qrCodeFile, 32.5, 25, 40);
        $pdf->MultiCell(85, 0, htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8'), 0, 'C', false, 0,  10, 61);

        // 最大文字サイズを30に設定
        $maxFontSize = 30;

        // PDFの幅を取得
        $maxWidth = 89;
        $pdf->SetFont("meiryob01", '', $maxFontSize);
        $currentWidth = $pdf->GetStringWidth($corporateDisplayName);

        if ($currentWidth > $maxWidth) {
            $newFontSize = ($maxWidth / $currentWidth) * $maxFontSize;
            $pdf->SetFont("meiryob01", '', $newFontSize);
        }

        $xPosition = (105 - $maxWidth) / 2;
        $pdf->SetXY($xPosition, 90);
        $pdf->Cell($maxWidth, 0, $corporateDisplayName, 0, 0, 'C');

        // 氏名を結合して作成
        $fullName = htmlspecialchars($user['surname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user['given_name'], ENT_QUOTES, 'UTF-8');

        $pdf->SetFont("meiryob01", '', $maxFontSize);
        $textWidth = $pdf->GetStringWidth($fullName);

        if ($textWidth > 85) {
            $adjustedFontSize = ($maxFontSize * 85) / $textWidth;
            $pdf->SetFont("meiryob01", '', $adjustedFontSize);
        } else {
            $pdf->SetFont("meiryob01", '', $maxFontSize);
        }

        $pdf->SetXY(10, 110);
        $pdf->Cell(85, 0, $fullName, 0, 0, "C");

        // 右上のブロック
        $pdf->SetFont('meiryo01');
        $pdf->SetXY(110, 5);

        $pdf->SetFont('meiryob01', '', 14);
        $pdf->MultiCell(110, 0, '【ご登録セッション】', 0, 'L', false);
        $pdf->Ln(4);

        $pdf->SetFont('meiryo01', '', 9);
        foreach ($lectures as $lecture) {
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetX(110);
            $pdf->MultiCell(95, 6, $lecture['lecture_time'], 1, 'L', true, 0, '', '', true, 0, false, true, 6, 'M', false);
            $pdf->Ln();

            $pdf->SetX(110);
            $pdf->SetFont('meiryo01', '', 9);

            $lectureTitle = getFormattedLectureTitle($lecture['lecture_title']);
            $formattedTitle = trim(preg_replace('/\s+/', ' ', $lectureTitle));
            $lineCount = $pdf->getNumLines($formattedTitle, 95);

            $cellHeight = ($lineCount == 1) ? 7 : (($lineCount == 2) ? 11 : 15);
            $pdf->MultiCell(95, $cellHeight, $lectureTitle, 1, 'L', false, 0, '', '', true, 0, false, true, $cellHeight, 'M', false);
            $pdf->Ln();
        }

        // 左下のブロック
        $pdf->SetXY(5, 155);
        $pdf->SetFont('meiryob01', '', 14);
        $pdf->MultiCell(0, 0, '【開催日程】', '0', 'L');
        $pdf->Ln();
        $pdf->SetFont('meiryo01', '', 12);
        $pdf->MultiCell(0, 0, '2024年9月13日（金）13:30 – 18:00', '0', 'L');
        $pdf->MultiCell(0, 0, '受付開始：13:00～', '0', 'L');
        $pdf->MultiCell(0, 0, '展示会場：13:00～18:00', '0', 'L');

        $pdf->Ln(12);
        $pdf->SetFont('meiryob01', '', 14);
        $pdf->SetX(5);
        $pdf->MultiCell(0, 0, '【会場】', '0', 'L');
        $pdf->Ln();
        $pdf->SetFont('meiryo01', '', 12);
        $pdf->MultiCell(0, 0, '大手町プレイス ホール＆カンファレンス', '0', 'L');
        $pdf->MultiCell(0, 0, '東京都千代田区大手町2-3-1', '0', 'L');
        $pdf->MultiCell(0, 0, '大手町プレイス(イーストタワー) 1F/2F', '0', 'L');

        $pdf->Ln(12);
        $pdf->SetFont('meiryob01', '', 14);
        $pdf->SetX(5);
        $pdf->MultiCell(0, 0, '【ご来場に際して】', '0', 'L');
        $pdf->Ln();
        $pdf->SetFont('meiryo01', '', 12);
        $pdf->MultiCell(0, 0, "受講票をA4サイズ・100%で印刷し\n四つ折りにして会場へお持ちください。\n受付にてホルダーをお渡しいたしますので\n受講票をホルダーに入れてご入場ください。", '0', 'L');

        // 右下のブロック
        $pdf->SetXY(110, 155);
        $pdf->SetFont('meiryob01', '', 14);
        $pdf->MultiCell(0, 0, '【会場アクセス】', '0', 'L');
        $pdf->Image('img-cnct/map00.png', 120.5, 166, 75);
        $pdf->Ln();
        $pdf->SetXY(115, 248);
        $pdf->SetFont('meiryo01', '', 12);
        $pdf->MultiCell(0, 0, "東京メトロ\n丸の内線・東西線・千代田線・半蔵門線\n「大手町」駅A5出口直結\n", '0', 'L');
        $pdf->Ln(2);
        $pdf->SetX(115);
        $pdf->MultiCell(0, 0, "都営三田線\n「大手町」駅A5出口直結", '0', 'L');

        // PDFをバッファに保存
        $pdfContent = $pdf->Output('', 'S');
        $pdfFilename = '受講票.pdf';

        // メール送信
        sendEmailWithPDF($user['work_email'], $fullName, $loginID, $corporateDisplayName, $pdfContent, $pdfFilename, $fullName, $lecture_times, $lecture_titles, $halls);

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
function sendEmailWithPDF($recipientEmail, $recipientName, $loginID, $corporateDisplayName, $pdfContent, $pdfFilename, $fullName, $lecture_times, $lecture_titles, $halls) {
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
    $message .= "講演情報:\n";

    foreach ($lecture_times as $key => $lecture_time) {
        $lecture_title = $lecture_titles[$key];
        $hall = $halls[$key];
        $message .= "講演時間: $lecture_time, 講演タイトル: $lecture_title, 会場: $hall\n";
    }        

    $message = <<<EOT
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ 
    【Informatica World Tour 2024】 登録完了のご連絡 
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ 
    このたびは、「Informatica World Tour 2024」にお申し込みいただきまして、 
    誠にありがとうございます。 
    お客様のご登録が完了いたしましたのでご連絡申し上げます。 

    ＜当日は受講票が必要です＞ 
    ご来場の際には、添付の受講票をプリントアウトの上、忘れずにお持ちください。 
    お忘れの場合は、受講票発行までにお時間を頂戴いたします。 

    ＜マイページ＞ 
    受講票は、マイページからもダウンロードが可能です。 
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
           'Data' => buildRawEmail($recipientEmail, $recipientName, $subject, $message, $pdfContent, $pdfFilename),
        ],
    ]);
}

function buildRawEmail($toEmail, $fullName, $subject, $bodyText, $pdfContent, $pdfFilename) {
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

    $rawEmail .= "--{$boundary}\r\n";
    $rawEmail .= "Content-Type: application/pdf; name=\"{$pdfFilename}\"\r\n";
    $rawEmail .= "Content-Transfer-Encoding: base64\r\n";
    $rawEmail .= "Content-Disposition: attachment; filename=\"{$pdfFilename}\"\r\n\r\n";
    $rawEmail .= chunk_split(base64_encode($pdfContent)) . "\r\n\r\n";

    $rawEmail .= "--{$boundary}--";

    return $rawEmail;
}

function generateLoginID($phone) {
    $randomStr = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    $phoneSuffix = substr($phone, -4);
    return $randomStr . $phoneSuffix;
}

function getFormattedLectureTitle($lectureTitle) {
    if (strpos($lectureTitle, 'データが切り拓く生成') !== false) {
        return "【データが切り拓く生成 AIの未来\n　～Everybody’s ready for AI except your data～】\n【データ連係基盤の統合／拡大、データマネジメントへ】";
    } elseif (strpos($lectureTitle, 'ビジネスに革新をもたらす') !== false) {
        return "【ビジネスに革新をもたらす生成AIと\n　モダン・データマネジメント】";
    } elseif (strpos($lectureTitle, 'データ活用の課題') !== false) {
        return "【データ活用の課題と最新動向｜\n　現場で広げるデータの利活用とは】";
    } elseif (strpos($lectureTitle, 'データ戦略を支える') !== false) {
        return "【データ戦略を支える\n　インフォマティカ・プラットフォームの全体像\n　〜ETL/ELTからマスタデータ管理、データガバナンスまで〜】";
    } elseif (strpos($lectureTitle, 'Snowflake, Databricks,') !== false) {
        return "【Snowflake, Databricks, AWS, Microsoft, GCP…\n　マルチクラウドで創る最強データプラットフォーム】";
    } elseif (strpos($lectureTitle, 'SUBARU流') !== false) {
        return "【SUBARU流 全社データ活用で笑顔を作る\n　「モノづくり革新」と「価値づくり」】";
    } elseif (strpos($lectureTitle, '現場技術者が語る！') !== false) {
        return "【現場技術者が語る！NTTデータの\n　インフォマティカプロジェクトでのチャレンジ！】";
    } elseif (strpos($lectureTitle, 'DX成功のカギ！') !== false) {
        return "【DX成功のカギ！\n　データをスピーディーな意思決定に活かすには】";
    } elseif (strpos($lectureTitle, 'データ活用に悩む方') !== false) {
        return "【データ活用に悩む方必見！\n　スモールスタートで始めるデータ活用】";
    } else {
        return $lectureTitle;
    }
}
?>