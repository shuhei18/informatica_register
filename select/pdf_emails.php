<?php
require_once 'config.php';
require_once 'phpqrcode/qrlib.php';
require_once 'tcpdf/tcpdf.php';
require 'vendor/autoload.php'; // AWS SDKをロード

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class CustomPDF extends TCPDF {
    // ヘッダーを無効化
    public function Header() {}

    // フッターを無効化 
    public function Footer() {}
}

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

            // corporate_type を company_name に結合
            $company = trim($user['corporate_prefix']) === '前' 
                ? trim($user['corporate_type']) . trim($user['company_name']) 
                : (trim($user['corporate_prefix']) === '後' 
                    ? trim($user['company_name']) . trim($user['corporate_type']) 
                    : (trim($user['corporate_prefix']) === 'なし' 
                        ? trim($user['company_name']) 
                        : trim($user['company_name'])));

            // 講演情報取得
            $stmt = $pdo->prepare("SELECT lecture_time, lecture_title FROM user_lectures WHERE loginID = :loginID");
            $stmt->execute(['loginID' => $loginID]);
            $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $currentWidth = $pdf->GetStringWidth($company);

            if ($currentWidth > $maxWidth) {
                $newFontSize = ($maxWidth / $currentWidth) * $maxFontSize;
                $pdf->SetFont("meiryob01", '', $newFontSize);
            }

            $xPosition = (105 - $maxWidth) / 2;
            $pdf->SetXY($xPosition, 90);
            $pdf->Cell($maxWidth, 0, $company, 0, 0, 'C');

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
            sendEmailWithPDF($user['work_email'], $fullName, $loginID, $company, $pdfContent, $pdfFilename);
            
            // リソースを解放
            $pdf->Close();  // TCPDFオブジェクトを閉じる
            unset($pdf);  // オブジェクトを明示的に破棄
        }
        // バッチごとに1秒の遅延を入れる
        sleep(1);
    }
    echo "ユーザーにメールが送信されました。";
    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
}

// PDFをメールに添付して送信する関数
function sendEmailWithPDF($recipientEmail, $recipientName, $loginID, $company, $pdfContent, $pdfFilename) {
    $client = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 送信メールのリージョン（東京）
        'credentials' => [
            'key'    => 'AKIAXF53I65F66G6TK6R',
            'secret' => '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',
        ],
    ]);

      $subject = "【Informatica World Tour 2024】 いよいよ 明日開催です！";
    $bodyText = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【Informatica World Tour 2024】 いよいよ 明日開催です！
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
このたびは、「Informatica World Tour 2024」にお申し込みいただきまして、
誠にありがとうございます。
いよいよ、明日9/13(金) 開催となりました。
当日は混雑が予想されますので、受付は早めにお済ませいただけますようお願いいたします。
皆様のご来場をお待ちしております。

＜当日は受講票が必要です＞
ご来場の際には、添付の受講票をプリントアウトの上、忘れずにお持ちください。
お忘れの場合は、受講票発行までにお時間が掛かりますので、あらかじめご了承ください。
また、ご本人以外の代理でご出席のお客様は、お名刺をお預かりいたしますので、お名刺を忘れずにお持ちください。

＜マイページ＞
受講票は、マイページからもダウンロードが可能です。
下記URLよりアクセスいただき、お客様情報に記載されている[ID]番号とご登録いただいた[パスワード]をご入力の上、マイページへログインしてください。
URL：https://s-bev.jp/login.php

ご登録いただいた[パスワード]が不明の場合は、マイページのログイン画面にあります
「パスワードを忘れた方」よりお問い合わせください。

＜お客様情報＞
---------------------------------------------------------------------- 
[ID] $loginID
[会社名]  $company
[氏名]  $recipientName
---------------------------------------------------------------------- 
 
＜Informatica World Tour 2024 開催概要＞
会期：2024年9月13日(金) 13:30-18:00（13:00- 受付/展示オープン）
会場：大手町プレイス ホール&カンファレンス（2F 受付）
　　　　 東京都千代田区大手町2-3-1大手町プレイス(イーストタワー) 1F/2F
アクセス：東京メトロ・都営地下鉄 「大手町」駅 A5出口直結
https://otemachi-place-hc.jp/access.html

━━━━━━━━━━━━━━━
【ご来場特典】
━━━━━━━━━━━━━━━
ご来場先着200名のお客様に、オリジナルボールペンとトートバッグのセットをプレゼント！
また、当日オンラインアンケートにご回答いただいたお客様には、ワイヤレス充電器を。
さらに、展示会場のスタンプラリーに参加いただき、スタンプを集めたお客様には
オリジナル Tシャツをプレゼントいたします！
皆様のご来場を心よりお待ちしております。

＜お問い合わせ＞
Informatica World Tour 2024 事務局
◇システムやアカウントに関するお問い合わせ（株式会社シードブレイン 内）
　Email：iwt2024_registration@s-bev.jp

◇イベント内容に関するお問い合わせ（株式会社George P. Johnsnon 内）
　Email：iwt2024@jevent.jp


利用規約
会場でオンラインアンケートにご回答いただくと、インフォマティカのワイヤレス充電器をプレゼントいたします。利用規約が適用されますので、詳しくはこちらをご覧ください。
https://iwt2024.jp/assets/pdf/IWT_Tokyo_2024_Promotion-Rules.pdf

";

    $mail = $client->sendRawEmail([
        'RawMessage' => [
           'Data' => buildRawEmail($recipientEmail, $recipientName, $subject, $bodyText, $pdfContent, $pdfFilename),
        ],
    ]);
}

// 生のメールを構築する関数
function buildRawEmail($toEmail, $fullName, $subject, $bodyText, $pdfContent, $pdfFilename) {
    $boundary = uniqid(rand(), true);
    $sender_name = "Informatica World Tour 2024 事務局";
    $sender_email = "iwt2024_registration@s-bev.jp";
    $bcc_email = "greenjackal32@www342b.sakura.ne.jp"; // BCCに追加するメールアドレス
    $encodedFromName = "=?UTF-8?B?" . base64_encode($sender_name) . "?=";

    $rawEmail = "From: $encodedFromName <$sender_email>\r\n";
    $rawEmail .= "To: {$fullName} <{$toEmail}>\r\n";
    $rawEmail .= "BCC: $bcc_email\r\n"; // BCCヘッダーを追加
    $rawEmail .= "Subject: {$subject}\r\n";
    $rawEmail .= "MIME-Version: 1.0\r\n";
    $rawEmail .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

    // プレーンテキストのボディ
    $rawEmail .= "--{$boundary}\r\n";
    $rawEmail .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $rawEmail .= $bodyText . "\r\n\r\n";

    // PDF添付ファイル
    $rawEmail .= "--{$boundary}\r\n";
    $rawEmail .= "Content-Type: application/pdf; name=\"{$pdfFilename}\"\r\n";
    $rawEmail .= "Content-Transfer-Encoding: base64\r\n";
    $rawEmail .= "Content-Disposition: attachment; filename=\"{$pdfFilename}\"\r\n\r\n";
    $rawEmail .= chunk_split(base64_encode($pdfContent)) . "\r\n\r\n";

    $rawEmail .= "--{$boundary}--";

    return $rawEmail;
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